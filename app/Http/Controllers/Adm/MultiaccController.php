<?php
namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Domain\Administrator\View\Mention;
use Coyote\Http\Grids\Adm\MultiaccGrid;
use Coyote\Models\Multiacc;
use Coyote\Services\Adm\MultiaccService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MultiaccController extends BaseController {
    public function index(): View {
        $grid = $this->gridBuilder()
            ->createGrid(MultiaccGrid::class)
            ->setSource(new EloquentSource(Multiacc::query()));
        return $this->view('adm.multiacc.home', [
            'grid'               => $grid,
            'joinHref'           => route('adm.multiacc.join.form'),
            'createHref'         => route('adm.multiacc.create'),
            'findByUsernameHref' => route('adm.multiacc.findByUsername'),
        ]);
    }

    public function create(MultiaccService $service): RedirectResponse {
        $multiacc = $service->create();
        return response()
            ->redirectToRoute('adm.multiacc.show', [$multiacc])
            ->with('success', 'Wpis do kartoteki został dodany.');
    }

    public function joinForm(): View {
        return $this->view('adm.multiacc.join', [
            'postUrl'  => route('adm.multiacc.join.save'),
            'backHref' => route('adm.multiacc.home'),
        ]);
    }

    public function joinSave(MultiaccService $service): RedirectResponse {
        $validator = validator(request()->all(), [
            'username'   => ['required', 'array'],
            'username.*' => ['required', 'exists:users,name', 'distinct'],
        ], [
            'username.*.required' => 'Podaj nazwę konta użytkownika.',
            'username.*.exists'   => 'Podane konto użytkownika nie istnieje.',
            'username.*.distinct' => 'Nie można oznaczyć konta jako swoje multikonto.',
        ]);
        if ($validator->fails()) {
            return back()
                ->withInput(request()->except('_token'))
                ->withErrors($validator);
        }
        $service->join(request()->get('username'));
        return response()
            ->redirectToRoute('adm.multiacc.home')
            ->with('success', 'Wpis do kartoteki został dodany.');
    }

    public function show(Multiacc $multiacc): View {
        $multiacc->loadMissing([
            'notes.moderator' => function (BelongsTo $query): void {
                $query->select('id', 'name');
            },
        ]);

        return $this->view('adm.multiacc.show', [
            'multiacc'        => [
                'id'        => $multiacc->id,
                'createdAt' => $multiacc->created_at,
                'users'     => $multiacc->multiaccUsers
                    ->map(fn(Multiacc\User $user): array => [
                        'user'      => Mention::from($user->user),
                        'createdAt' => $user->created_at,
                        'moderator' => Mention::from($user->moderator),
                    ])
                    ->toArray(),
                'notes'     => $multiacc->notes
                    ->map(fn(Multiacc\Note $note): array => [
                        'content'   => $note->content,
                        'moderator' => Mention::from($note->moderator),
                        'createdAt' => $note->created_at,
                    ])
                    ->toArray(),
            ],
            'noteFormHref'    => route('adm.multiacc.noteForm', [$multiacc]),
            'backHref'        => route('adm.multiacc.home'),
            'includeUserHref' => route('adm.multiacc.includeUser.form', [$multiacc]),
        ]);
    }

    public function noteForm(Multiacc $multiacc): View {
        return $this->view('adm.multiacc.note', [
            'postHref' => route('adm.multiacc.noteSave', [$multiacc]),
            'backHref' => route('adm.multiacc.show', [$multiacc]),
            'multiacc' => ['id' => $multiacc->id,],
        ]);
    }

    public function noteSave(Multiacc $multiacc, MultiaccService $service): RedirectResponse {
        $service->addNote($multiacc, request()->get('note'));
        return response()->redirectToRoute('adm.multiacc.show', [$multiacc])
            ->with('success', 'Notatka moderatorska została dodana.');
    }

    public function findByUsername(MultiaccService $service): RedirectResponse {
        $username = request()->get('username') ?? '';
        if ($username === '') {
            return response()->redirectToRoute('adm.multiacc.home');
        }
        $multiacc = $service->findByUsername($username);
        if ($multiacc) {
            return response()
                ->redirectToRoute('adm.multiacc.show', [$multiacc]);
        }
        return back()->with('warning', 'Nie ma kartoteki z kontem użytkownika: "' . $username . '".');
    }

    public function includeUserForm(Multiacc $multiacc): View {
        return $this->view('adm.multiacc.include', [
            'multiacc' => [
                'id'        => $multiacc->id,
                'createdAt' => $multiacc->created_at,
            ],
            'backHref' => route('adm.multiacc.show', [$multiacc]),
            'postUrl'  => route('adm.multiacc.includeUser.save', [$multiacc]),
        ]);
    }

    public function includeUserSave(Multiacc $multiacc, MultiaccService $service): RedirectResponse|JsonResponse {
        $validator = validator(request()->all(),
            ['username' => ['required', 'exists:users,name']],
            [
                'username.required' => 'Podaj nazwę użytkownika.',
                'username.exists'   => 'Podane konto użytkownika nie istnieje, podaj dokładną nazwę użytkownika.',
            ]);
        if ($validator->fails()) {
            return back()
                ->withInput(request()->except('_token'))
                ->withErrors($validator);
        }
        $service->include($multiacc, request()->get('username'));
        return response()->redirectToRoute('adm.multiacc.show', [$multiacc])
            ->with('success', 'Konto zostało dodane do kartoteki jako rozpoznane multikonto.');
    }
}
