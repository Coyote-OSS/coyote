<?php
namespace Coyote\Http\Controllers\Auth;

use Carbon\Carbon;
use Coyote\Actkey;
use Coyote\Domain\RouteVisits;
use Coyote\Domain\TempEmail\TempEmailCategory;
use Coyote\Domain\TempEmail\TempEmailRepository;
use Coyote\Events\UserSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Auth\RegisterForm;
use Coyote\Mail\UserRegistered;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Objects\Person as Stream_Person;
use Coyote\User;
use Illuminate\Contracts\Mail\MailQueue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;

class RegisterController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->middleware('guest');
    }

    public function index(RouteVisits $visits, Request $request): View {
        $this->breadcrumb->push('Rejestracja', route('register'));
        $agent = new Agent();
        if (!$agent->isRobot($this->request->userAgent())) {
            $visits->visit($this->request->path(), Carbon::now()->toDateString());
        }
        return $this->view('auth.register', [
            'form'       => $this->createForm(RegisterForm::class, null, [
                'url' => route('register'),
            ]),
            'refererUrl' => $request->headers->get('referer'),
        ]);
    }

    public function signup(RegisterForm $form, TempEmailRepository $repository): RedirectResponse {
        $request = $form->getRequest();
        $this->transaction(function () use ($repository, $request) {
            $user = User::query()->forceCreate([
                'name'                 => $request->input('name'),
                'email'                => $request->input('email'),
                'password'             => bcrypt($request->input('password')),
                'guest_id'             => $request->session()->get('guest_id'),
                'marketing_agreement'  => $request->input('marketing_agreement'),
                'newsletter_agreement' => true,
                'allow_smilies'        => true,
                'is_incognito'         => $this->hasTemporaryMail($request->input('email')),
            ]);
            app(MailQueue::class)
                ->to($request->input('email'))
                ->send(new UserRegistered(Actkey::createLink($user->id)));
            auth()->login($user, true);
            stream(Stream_Create::class, new Stream_Person());
            event(new UserSaved($user));
        });
        return redirect()
            ->to($request->input('refererUrl'))
            ->with('success', 'Konto zostało utworzone. Na podany adres e-mail, przesłany został link aktywacyjny.');
    }

    private function hasTemporaryMail(string $email): bool {
        /** @var TempEmailRepository $repository */
        $repository = app(TempEmailRepository::class);
        $category = $repository->findCategory($email);
        return $category === TempEmailCategory::TEMPORARY;
    }
}
