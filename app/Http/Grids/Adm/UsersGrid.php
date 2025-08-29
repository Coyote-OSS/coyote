<?php
namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Decorators\FormatDateRelative;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Filters\Text;
use Boduch\Grid\GridHelper;
use Boduch\Grid\Order;
use Coyote\Domain\Administrator\UserContent\UserContent;
use Coyote\Domain\Icon\Icons;
use Coyote\Domain\TempEmail\TempEmailCategory;
use Coyote\Domain\TempEmail\TempEmailRepository;
use Coyote\Services\Adm\UserContent\UserContentFactory;
use Coyote\Services\Grid\Grid;
use Coyote\User;

class UsersGrid extends Grid {
    public function __construct(
        GridHelper                           $gridHelper,
        private readonly Icons               $icons,
        private readonly TempEmailRepository $tempEmails,
    ) {
        parent::__construct($gridHelper);
        $this->perPage = 50;
    }

    public function buildGrid(): void {
        $this
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('name', [
                'title'     => 'Użytkownik',
                'clickable' => $this->username(...),
                'filter'    => new Text(['operator' => FilterOperator::OPERATOR_ILIKE]),
            ])
            ->addColumn('email', [
                'title'     => 'E-mail',
                'clickable' => $this->userEmail(...),
                'filter'    => new Text(['operator' => FilterOperator::OPERATOR_ILIKE]),
            ])
            ->addColumn('created_at', [
                'title'      => 'Rejestracja',
                'decorators' => [new FormatDateRelative('nigdy', shortDate:true)],
            ])
            ->addColumn('visited_at', [
                'title'      => 'Ostatnio',
                'sortable'   => true,
                'decorators' => [new FormatDateRelative('nigdy', shortDate:true)],
            ])
            ->addColumn('Treści', [
                'clickable' => $this->userContent(...),
            ])
            ->addColumn('Status', [
                'clickable' => $this->userStatusHtml(...),
            ]);
    }

    private function username(User $user): string {
        return
            $this->icons->icon('adminUsersUser') . ' ' .
            link_to_route('adm.users.show', $user->name, [$user->id]);
    }

    private function userEmail(User $user): string {
        $mail = $this->userEmailHtml($user->email);
        if ($user->is_confirm) {
            return $mail;
        }
        $icon = $this->icons->icon('adminUsersEmailUnverified');
        return "<b title='Adres email nie został zweryfikowany.'>$icon $mail</b>";
    }

    private function userEmailHtml(string $email): string {
        [$username, $domain] = \explode('@', $email);
        $color = $this->emailCssColor($email);
        $title = $this->emailCategoryTitle($email);
        return "$username@<span title='$title' style='color:$color;'>$domain</span>";
    }

    private function emailCssColor(string $email): string {
        return match ($this->tempEmails->findCategory($email)) {
            TempEmailCategory::TEMPORARY => 'red',
            TempEmailCategory::TRUSTED   => 'inherit',
            TempEmailCategory::UNKNOWN   => 'orange',
        };
    }

    private function emailCategoryTitle(string $email): string {
        return match ($this->tempEmails->findCategory($email)) {
            TempEmailCategory::TEMPORARY => 'Tymczasowy adres email.',
            TempEmailCategory::TRUSTED   => 'Adres email od zaufanego dostawcy.',
            TempEmailCategory::UNKNOWN   => 'Nieznana domena adresu email.',
        };
    }

    private function userStatusHtml(User $user): string {
        if ($user->is_blocked) {
            return 'Zbanowany';
        }
        if ($user->deleted_at !== null) {
            return 'Usunięty';
        }
        if ($user->is_incognito) {
            return 'ShadowBan';
        }
        return '';
    }

    private function userContent(User $user): string {
        /** @var UserContentFactory $factory */
        $factory = app(UserContentFactory::class);
        return $this->userContentCell(
            $factory->create($user),
            $user->reputation);
    }

    private function userContentCell(UserContent $content, $reputation): string {
        return \sPrintF('<span title="%s">%s</span>',
            htmlSpecialChars($this->userContentSummary($content, $reputation)),
            $this->userContentSum($content));
    }

    private function userContentSummary(UserContent $content, int $reputation): string {
        return <<<summary
            Wątki: $content->topics
            Posty: $content->posts (+$content->postsDeleted usuniętych)
            Komentarze: $content->postComments (+$content->postCommentsDeleted usuniętych)
            Głosy: $content->postVotes
            Blogi: $content->blogs (+$content->blogsDeleted usuniętych)
            Komentarze: $content->blogComments (+$content->blogCommentsDeleted usuniętych)
            Głosy: $content->blogVotes
            Oferty pracy: $content->jobOffers
            Raporty: $content->flagsTotal
            Wiadomości: $content->privateMessages
            Reputacja: $reputation
            summary;
    }

    private function userContentSum(UserContent $content): int {
        return $content->posts +
            $content->postsDeleted +
            $content->postComments +
            $content->postCommentsDeleted +
            $content->blogs +
            $content->blogsDeleted +
            $content->blogComments +
            $content->blogCommentsDeleted +
            $content->postVotes +
            $content->blogVotes +
            $content->topics +
            $content->jobOffers +
            $content->flagsTotal +
            $content->privateMessages;
    }
}
