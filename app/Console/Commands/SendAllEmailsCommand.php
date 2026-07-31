<?php
namespace Coyote\Console\Commands;

use Closure;
use Coyote\Comment;
use Coyote\Events\SuccessfulLogin as SuccessfulLoginEvent;
use Coyote\Job;
use Coyote\Job\Application as JobApplication;
use Coyote\Mail\EmailConfirmation;
use Coyote\Mail\SuccessfulLogin;
use Coyote\Mail\UserRegistered;
use Coyote\Microblog;
use Coyote\Microblog\Vote as MicroblogVote;
use Coyote\Notifications\Job\ApplicationConfirmationNotification;
use Coyote\Notifications\Job\ApplicationSentNotification;
use Coyote\Notifications\Job\CommentedNotification;
use Coyote\Notifications\Job\ExpiredNotification;
use Coyote\Notifications\Job\RepliedNotification;
use Coyote\Notifications\Microblog\DeletedNotification;
use Coyote\Notifications\Microblog\SubmittedNotification;
use Coyote\Notifications\Microblog\VotedNotification;
use Coyote\Notifications\Wiki\ContentChangedNotification;
use Coyote\Payment;
use Coyote\Pm;
use Coyote\Pm\Text as PmText;
use Coyote\Post;
use Coyote\Post\Comment as PostComment;
use Coyote\Topic;
use Coyote\User;
use Coyote\Wiki;
use Coyote\Wiki\Comment as WikiComment;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

/**
 * Sends one example of every transactional email the app can produce, for local visual QA
 * (e.g. via MailHog). Calls each Mailable/Notification's mail-building logic directly rather
 * than going through the real ->notify()/via() pipeline, so it doesn't depend on the target
 * user's notification settings, unread-notification state, or email-verification status.
 */
class SendAllEmailsCommand extends Command {
    protected $signature = 'mail:send-all
        {email : Recipient address for every sample email}
        {--base-url=http://localhost:8880 : Browser-reachable base URL to use for links/images in the emails (running via CLI has no HTTP request to infer it from, and config(app.url) points at the internal docker hostname)}';
    protected $description = 'Sends one example of every transactional email the app can produce (for local visual QA, e.g. via MailHog)';

    /** @var array<int, array{0: string, 1: string, 2: string}> */
    private array $results = [];

    public function handle(): int {
        $to = $this->argument('email');
        \Illuminate\Support\Facades\URL::forceRootUrl($this->option('base-url'));

        $this->attempt('UserRegistered', fn() => $this->sendUserRegistered($to));
        $this->attempt('EmailConfirmation', fn() => $this->sendEmailConfirmation($to));
        $this->attempt('SuccessfulLogin', fn() => $this->sendSuccessfulLogin($to));
        $this->attempt('Lockout', fn() => $this->sendLockout($to));
        $this->attempt('ResetPasswordNotification', fn() => $this->sendResetPassword($to));

        $this->attempt('PmCreatedNotification', fn() => $this->sendPmCreated($to));

        $this->attempt('Topic\MovedNotification', fn() => $this->sendTopicMoved($to));
        $this->attempt('Topic\SubjectChangedNotification', fn() => $this->sendTopicSubjectChanged($to));
        $this->attempt('Topic\DeletedNotification', fn() => $this->sendTopicDeleted($to));

        $this->attempt('Post\AcceptedNotification', fn() => $this->sendPostAccepted($to));
        $this->attempt('Post\VotedNotification', fn() => $this->sendPostVoted($to));
        $this->attempt('Post\SubmittedNotification', fn() => $this->sendPostSubmitted($to));
        $this->attempt('Post\ChangedNotification', fn() => $this->sendPostChanged($to));
        $this->attempt('Post\UserMentionedNotification', fn() => $this->sendPostUserMentioned($to));
        $this->attempt('Post\DeletedNotification', fn() => $this->sendPostDeleted($to));
        $this->attempt('Post\CommentedNotification', fn() => $this->sendPostCommented($to));
        $this->attempt('Post\Comment\VotedNotification', fn() => $this->sendPostCommentVoted($to));
        $this->attempt('Post\Comment\MigratedNotification', fn() => $this->sendPostCommentMigrated($to));
        $this->attempt('Post\Comment\UserMentionedNotification', fn() => $this->sendPostCommentUserMentioned($to));

        $this->attempt('Microblog\SubmittedNotification', fn() => $this->sendMicroblogSubmitted($to));
        $this->attempt('Microblog\CommentedNotification', fn() => $this->sendMicroblogCommented($to));
        $this->attempt('Microblog\UserMentionedNotification', fn() => $this->sendMicroblogUserMentioned($to));
        $this->attempt('Microblog\DeletedNotification', fn() => $this->sendMicroblogDeleted($to));
        $this->attempt('Microblog\VotedNotification', fn() => $this->sendMicroblogVoted($to));

        $this->attempt('Wiki\CommentedNotification', fn() => $this->sendWikiCommented($to));
        $this->attempt('Wiki\ContentChangedNotification', fn() => $this->sendWikiContentChanged($to));

        $this->attempt('Job\CommentedNotification', fn() => $this->sendJobCommented($to));
        $this->attempt('Job\RepliedNotification', fn() => $this->sendJobReplied($to));
        $this->attempt('Job\ApplicationSentNotification', fn() => $this->sendJobApplicationSent($to));
        $this->attempt('Job\ApplicationConfirmationNotification', fn() => $this->sendJobApplicationConfirmation($to));
        $this->attempt('Job\ExpiredNotification', fn() => $this->sendJobExpired($to));
        $this->attempt('Job\CreatedNotification', fn() => $this->sendJobCreated($to));

        $this->attempt('SuccessfulPaymentNotification', fn() => $this->sendSuccessfulPayment($to));

        $this->summarize();

        return self::SUCCESS;
    }

    private function attempt(string $label, Closure $send): void {
        try {
            $send();
            $this->results[] = [$label, 'sent', ''];
        } catch (Throwable $e) {
            $this->results[] = [$label, 'skipped', $e->getMessage()];
        }
    }

    private function summarize(): void {
        $this->table(['Email', 'Status', 'Reason'], $this->results);
        $sent = count(array_filter($this->results, fn($row) => $row[1] === 'sent'));
        $this->info("$sent / " . count($this->results) . ' emails sent to ' . $this->argument('email') . '.');
    }

    // --- Mailables & raw mail (no Notification class involved) ---

    private function sendUserRegistered(string $to): void {
        Mail::to($to)->send(new UserRegistered(url('/')));
    }

    private function sendEmailConfirmation(string $to): void {
        Mail::to($to)->send(new EmailConfirmation(url('/')));
    }

    private function sendSuccessfulLogin(string $to): void {
        $event = new SuccessfulLoginEvent($this->latestUser(), '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        Mail::to($to)->send(new SuccessfulLogin($event));
    }

    private function sendLockout(string $to): void {
        $user = $this->latestUser();
        $data = array_merge($user->toArray(), ['ip' => '127.0.0.1', 'host' => 'localhost']);
        Mail::send('emails.auth.lockout', $data, function ($message) use ($to) {
            $message->to($to);
            $message->subject('Powiadomienie o nieudanym logowaniu na Twoje konto');
        });
    }

    // --- Notification-backed emails ---

    private function sendResetPassword(string $to): void {
        $this->deliver((new \Coyote\Notifications\ResetPasswordNotification('sample-reset-token'))->toMail(), $to);
    }

    private function sendPmCreated(string $to): void {
        $text = new PmText();
        $text->text = 'To jest przykładowa treść prywatnej wiadomości do testowania e-maili.';
        $pm = new Pm();
        $pm->id = 1;
        $pm->setRelation('author', $this->latestUser());
        $pm->setRelation('text', $text);
        $this->deliver((new \Coyote\Notifications\PmCreatedNotification($pm))->toMail(), $to);
    }

    private function sendTopicMoved(string $to): void {
        $topic = $this->latestTopic();
        $this->deliver((new \Coyote\Notifications\Topic\MovedNotification($this->latestUser(), $topic))->toMail(), $to);
    }

    private function sendTopicSubjectChanged(string $to): void {
        $notification = (new \Coyote\Notifications\Topic\SubjectChangedNotification($this->latestUser(), $this->latestTopic()))
            ->setOriginalSubject('Poprzedni tytuł wątku');
        $this->deliver($notification->toMail(), $to);
    }

    private function sendTopicDeleted(string $to): void {
        $notification = (new \Coyote\Notifications\Topic\DeletedNotification($this->latestUser(), $this->latestTopic()))
            ->setReasonName('Spam')
            ->setReasonText('Wątek został uznany za spam.');
        $this->deliver($notification->toMail(), $to);
    }

    private function sendPostAccepted(string $to): void {
        $this->deliver((new \Coyote\Notifications\Post\AcceptedNotification($this->latestUser(), $this->latestPost()))->toMail(), $to);
    }

    private function sendPostVoted(string $to): void {
        $this->deliver((new \Coyote\Notifications\Post\VotedNotification($this->latestUser(), $this->latestPost()))->toMail(), $to);
    }

    private function sendPostSubmitted(string $to): void {
        $this->deliver((new \Coyote\Notifications\Post\SubmittedNotification($this->latestUser(), $this->latestPost()))->toMail(), $to);
    }

    private function sendPostChanged(string $to): void {
        $this->deliver((new \Coyote\Notifications\Post\ChangedNotification($this->latestUser(), $this->latestPost()))->toMail(), $to);
    }

    private function sendPostUserMentioned(string $to): void {
        $this->deliver((new \Coyote\Notifications\Post\UserMentionedNotification($this->latestUser(), $this->latestPost()))->toMail(), $to);
    }

    private function sendPostDeleted(string $to): void {
        $notification = (new \Coyote\Notifications\Post\DeletedNotification($this->latestUser(), $this->latestPost()))
            ->setReasonName('Spam')
            ->setReasonText('Post został uznany za spam.');
        $this->deliver($notification->toMail(), $to);
    }

    private function sendPostCommented(string $to): void {
        $this->deliver((new \Coyote\Notifications\Post\CommentedNotification($this->fakePostComment()))->toMail(), $to);
    }

    private function sendPostCommentVoted(string $to): void {
        $this->deliver((new \Coyote\Notifications\Post\Comment\VotedNotification($this->latestUser(), $this->latestPost()))->toMail(), $to);
    }

    private function sendPostCommentMigrated(string $to): void {
        $this->deliver((new \Coyote\Notifications\Post\Comment\MigratedNotification($this->latestUser(), $this->latestPost()))->toMail(), $to);
    }

    private function sendPostCommentUserMentioned(string $to): void {
        $this->deliver((new \Coyote\Notifications\Post\Comment\UserMentionedNotification($this->fakePostComment()))->toMail(), $to);
    }

    private function sendMicroblogSubmitted(string $to): void {
        $this->deliver(new SubmittedNotification($this->latestMicroblog())->toMail(), $to);
    }

    private function sendMicroblogCommented(string $to): void {
        $this->deliver((new \Coyote\Notifications\Microblog\CommentedNotification($this->fakeMicroblogReply()))->toMail(), $to);
    }

    private function sendMicroblogUserMentioned(string $to): void {
        $this->deliver(new \Coyote\Notifications\Microblog\UserMentionedNotification($this->latestMicroblog())->toMail(), $to);
    }

    private function sendMicroblogDeleted(string $to): void {
        $notification = new DeletedNotification($this->latestMicroblog(), $this->latestUser());
        $this->deliver($notification->toMail(), $to);
    }

    private function sendMicroblogVoted(string $to): void {
        $this->deliver(new VotedNotification($this->fakeMicroblogVote())->toMail(), $to);
    }

    private function sendWikiCommented(string $to): void {
        $this->deliver(new \Coyote\Notifications\Wiki\CommentedNotification($this->fakeWikiComment())->toMail(), $to);
    }

    private function sendWikiContentChanged(string $to): void {
        $this->deliver(new ContentChangedNotification($this->latestWikiWithLog())->toMail(), $to);
    }

    private function sendJobCommented(string $to): void {
        $this->deliver(new CommentedNotification($this->fakeJobComment())->toMail(), $to);
    }

    private function sendJobReplied(string $to): void {
        $this->deliver(new RepliedNotification($this->fakeJobComment())->toMail(), $to);
    }

    private function sendJobApplicationSent(string $to): void {
        $application = $this->fakeJobApplication();
        $this->deliver(new ApplicationSentNotification($application)->toMail($application->job), $to);
    }

    private function sendJobApplicationConfirmation(string $to): void {
        $application = $this->fakeJobApplication();
        $this->deliver(new ApplicationConfirmationNotification()->toMail($application), $to);
    }

    private function sendJobExpired(string $to): void {
        $this->deliver(new ExpiredNotification($this->latestJob())->toMail(), $to);
    }

    private function sendJobCreated(string $to): void {
        $notification = new \Coyote\Notifications\Job\CreatedNotification($this->latestJobWithUnpaidPayment());
        $notification->via($this->latestUser()); // populates the internal price calculator, normally done by the framework before toMail()
        $this->deliver($notification->toMail(), $to);
    }

    private function sendSuccessfulPayment(string $to): void {
        $this->deliver((new \Coyote\Notifications\SuccessfulPaymentNotification($this->latestPayment(), null))->toMail(), $to);
    }

    // --- delivery helper: wraps an already-built MailMessage so it can be sent without a real notifiable/via() ---

    private function deliver(MailMessage $message, string $to): void {
        $notifiable = (new AnonymousNotifiable())->route('mail', $to);
        $notification = new class ($message) extends \Illuminate\Notifications\Notification {
            public function __construct(private readonly MailMessage $message) {}

            public function via($notifiable): array {
                return ['mail'];
            }

            public function toMail($notifiable): MailMessage {
                return $this->message;
            }
        };
        app(MailChannel::class)->send($notifiable, $notification);
    }

    // --- data helpers: real DB records where available, minimal unsaved models otherwise ---

    private function latestUser(): User {
        return $this->latestOrFail(User::query()->whereNotNull('email'), 'użytkownik (users)');
    }

    private function latestTopic(): Topic {
        return $this->latestOrFail(Topic::query()->with('forum'), 'wątek (topics)');
    }

    private function latestPost(): Post {
        return $this->latestOrFail(Post::query()->with('topic'), 'post (posts)');
    }

    private function latestMicroblog(): Microblog {
        return $this->latestOrFail(Microblog::query()->with('user'), 'wpis na mikroblogu (microblogs)');
    }

    private function latestJob(): Job {
        return $this->latestOrFail(Job::query(), 'ogłoszenie o pracę (jobs)');
    }

    private function latestJobWithUnpaidPayment(): Job {
        $job = Job::query()->latest('id')->get()->first(fn(Job $job) => $job->getUnpaidPayment() !== null);
        if ($job === null) {
            throw new RuntimeException('brak ogłoszenia z nieopłaconą płatnością (jobs+payments)');
        }
        return $job;
    }

    private function latestPayment(): Payment {
        return $this->latestOrFail(Payment::query()->with('job'), 'płatność (payments)');
    }

    private function latestWikiWithLog(): Wiki {
        /** @var Wiki|null $wiki */
        $wiki = Wiki::query()->latest('id')->get()->first(fn(Wiki $wiki) => $wiki->logs()->exists());
        if ($wiki === null) {
            throw new RuntimeException('brak strony wiki z historią zmian (wiki+wiki_log)');
        }
        return $wiki;
    }

    private function fakePostComment(): PostComment {
        $user = $this->latestUser();
        $comment = new PostComment();
        $comment->id = 1;
        $comment->text = 'To jest przykładowy komentarz do testowania e-maili.';
        $comment->user_id = $user->id;
        $comment->setRelation('post', $this->latestPost());
        $comment->setRelation('user', $user);
        return $comment;
    }

    private function fakeMicroblogReply(): Microblog {
        $parent = $this->latestMicroblog();
        $reply = new Microblog();
        $reply->id = 1;
        $reply->parent_id = $parent->id;
        $reply->text = 'To jest przykładowy komentarz na mikroblogu.';
        $reply->setRelation('user', $this->latestUser());
        $reply->setRelation('parent', $parent);
        return $reply;
    }

    private function fakeMicroblogVote(): MicroblogVote {
        $vote = new MicroblogVote();
        $vote->setRelation('microblog', $this->latestMicroblog());
        $vote->setRelation('user', $this->latestUser());
        return $vote;
    }

    private function fakeWikiComment(): WikiComment {
        $comment = new WikiComment();
        $comment->id = 1;
        $comment->text = 'To jest przykładowy komentarz do strony wiki.';
        $comment->setRelation('wiki', $this->latestOrFail(Wiki::query(), 'strona wiki (wiki)'));
        $comment->setRelation('user', $this->latestUser());
        return $comment;
    }

    private function fakeJobComment(): Comment {
        $comment = new Comment();
        $comment->id = 1;
        $comment->text = 'To jest przykładowy komentarz do ogłoszenia o pracę.';
        $comment->setRelation('resource', $this->latestJob());
        $comment->setRelation('user', $this->latestUser());
        return $comment;
    }

    private function fakeJobApplication(): JobApplication {
        $application = new JobApplication();
        $application->name = 'Jan Testowy';
        $application->email = 'jan.testowy@example.com';
        $application->phone = '000-000-000';
        $application->github = '';
        $application->text = 'Przykładowa treść zgłoszenia rekrutacyjnego.';
        $application->salary = '10000-15000';
        $application->dismissal_period = '1 miesiąc';
        $application->setRelation('job', $this->latestJob());
        return $application;
    }

    /**
     * @param Builder<Model> $query
     */
    private function latestOrFail(Builder $query, string $label): mixed {
        $record = $query->latest('id')->first();
        if ($record === null) {
            throw new RuntimeException("brak rekordu w bazie: $label");
        }
        return $record;
    }
}
