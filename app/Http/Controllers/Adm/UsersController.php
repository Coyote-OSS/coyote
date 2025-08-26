<?php
namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Carbon\Carbon;
use Coyote\Domain\Administrator\User\Store\UserStore;
use Coyote\Domain\Administrator\User\View\Activity;
use Coyote\Domain\Administrator\User\View\Navigation;
use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Administrator\View\Mention;
use Coyote\Events\UserDeleted;
use Coyote\Events\UserSaved;
use Coyote\Http\Forms\User\AdminForm;
use Coyote\Http\Grids\Adm\UsersGrid;
use Coyote\Models\Multiacc;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\Adm\UserContent\UserContentFactory;
use Coyote\Services\Adm\UserContent\UserContentItemFactory;
use Coyote\Services\Adm\UserContent\UserContentItemType;
use Coyote\Services\Adm\UserInspection\UserInspectionService;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\Stream\Activities;
use Coyote\Services\Stream\Activities\Update;
use Coyote\Services\Stream\Objects\MassDelete;
use Coyote\Services\Stream\Objects\Person;
use Coyote\User;
use Coyote\View\FingerprintUserMentionTableItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsersController extends BaseController {
    public function __construct(private UserRepository $user) {
        parent::__construct();
        $this->breadcrumb->push('Użytkownicy', route('adm.users'));
    }

    public function index(): View {
        $this->user->pushCriteria(new WithTrashed());
        $this->user->applyCriteria();
        $grid = $this->gridBuilder()->createGrid(UsersGrid::class);
        $grid->setSource(new EloquentSource($this->user->newQuery()));
        return $this->view('adm.users.home', ['grid' => $grid]);
    }

    public function show(User $user, UserContentFactory $service): View {
        $this->breadcrumb->push("@$user->name", route('adm.users.show', [$user->id]));
        $daysAgo = $this->daysAgo($this->request);
        $store = new UserStore($user, Carbon::now()->subDays($daysAgo));
        return $this->view('adm.users.show', [
            'userDetails'       => [
                'email'          => $user->email,
                'accountCreated' => new Date($user->created_at, Carbon::now()),
                'lastVisit'      => $user->visited_at
                    ? new Date($user->visited_at, Carbon::now())
                    : null,
                'isBanned'       => $user->is_blocked,
                'isIncognito'    => $user->is_incognito,
                'isDeleted'      => !$user->is_active,
                'mailVerified'   => $user->is_confirm,
                'multiacc'       => $this->userDetailMultiacc($user),
            ],
            'accountCreated'    => new Date($user->created_at, Carbon::now()),
            'navigation'        => new Navigation($user->id, $user->name),
            'activity'          => new Activity(
                $store->postsCategoriesStatistic(),
                $store->postsCategoriesStatisticLikes(),
                $store->deleteReasons(),
                $store->reportReasons(),
                $store->postStats()),
            'userContent'       => $service->create($user),
            'userContentAction' => [
                'url'       => route('adm.users.contentDelete', [$user]),
                'canDelete' => $this->canDeleteContent($user),
            ],
        ]);
    }

    public function contentDelete(
        User                   $user,
        Request                $request,
        UserContentItemFactory $factory,
    ) {
        $canDeleteContent = $this->canDeleteContent($user);
        if (!$canDeleteContent) {
            return response()->json(['error' => 'Account too old to remove content.']);
        }
        $type = UserContentItemType::from($request->get('contentDelete'));
        $deletedItems = $factory->create($type)->massDelete($user);
        stream(Activities\MassDelete::class,
            new MassDelete($user, $type, $deletedItems));
        return response()->redirectTo(
            route('adm.users.show', [$user]));
    }

    private function daysAgo(Request $request): int {
        $key = $request->query('last') ?? 'default';
        $map = [
            'day'     => 1,
            'week'    => 7,
            'month'   => 31,
            'year'    => 365,
            'default' => 40 * 365,
        ];
        return $map[$key] ?? $map['default'];
    }

    public function edit(User $user): View {
        $this->breadcrumb->push("@$user->name", route('adm.users.show', [$user->id]));
        $this->breadcrumb->push('Ustawienia konta', route('adm.users.save', [$user->id]));
        return $this->view('adm.users.save', [
            'user'         => $user,
            'form'         => $this->getForm($user),
            'userSettings' => \json_encode($user->guest?->settings, \JSON_PRETTY_PRINT),
        ]);
    }

    protected function getForm(User $user): Form {
        return $this->createForm(AdminForm::class, $user, [
            'url' => route('adm.users.save', [$user->id]),
        ]);
    }

    public function save(User $user): RedirectResponse {
        $form = $this->getForm($user);
        $form->validate();

        $this->transaction(function () use ($user, $form) {
            $data = $form->all();
            if ($form->get('delete_photo')->isChecked()) {
                $data['photo'] = null;
            }

            // we use forceFill() to fill fields that are NOT in $fillable model's array.
            // we can do that because $form->all() returns only fields in form. $request->all() returns
            // all fields in HTTP POST so it's not secure.
            $user->forceFill(array_except($data, ['submit', 'skills', 'groups', 'delete_photo']))->save();

            $user->groups()->sync((array)$data['groups']);
            stream(Update::class, new Person($user));
            event($user->deleted_at ? new UserDeleted($user) : new UserSaved($user));
        });

        return back()->with('success', 'Zmiany zostały zapisane.');
    }

    public function inspect(User $user, UserInspectionService $service): View {
        $recognizeOtherUsers = request()->query->has('recognizeOtherUsers');
        [$fingerprints, $usersIdByFingerprints] = $service->loadFingerprints($user->id, $recognizeOtherUsers);
        return $this->view('adm.users.inspect', [
            'navigation' => [
                'mention'             => new Mention($user->id, $user->name),
                'usersByFingerprints' => route('adm.users.inspect', [$user->id]) . '?recognizeOtherUsers',
                'back'                => route('adm.users.show', [$user->id]),
            ],
            'inspection' => [
                'hasUsersByFingerprints' => $recognizeOtherUsers,
                'fingerprints'           => $fingerprints,
                'usersByFingerprints'    => \array_map(
                    FingerprintUserMentionTableItem::from(...),
                    $usersIdByFingerprints),
            ],
        ]);
    }

    private function canDeleteContent(User $user): bool {
        return $user->created_at->diffInDays(now()) < 31;
    }

    private function userDetailMultiacc(User $user): ?array {
        /** @var Multiacc $multiacc */
        $multiacc = $user->multiaccs->first();
        if ($multiacc) {
            return [
                'href' => route('adm.multiacc.show', [$multiacc]),
                'id'   => $multiacc->id,
            ];
        }
        return null;
    }
}
