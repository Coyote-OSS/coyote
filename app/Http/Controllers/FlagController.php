<?php
namespace Coyote\Http\Controllers;

use Carbon\Carbon;
use Coyote;
use Coyote\Flag;
use Coyote\Flag\Type;
use Coyote\Forum;
use Coyote\Job;
use Coyote\Microblog;
use Coyote\Notifications\FlagCreatedNotification;
use Coyote\Post;
use Coyote\Repositories\Criteria\HasPermission;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\Stream;
use Coyote\Services\Stream\Activities;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FlagController extends Controller {
    public function __construct(private UserRepository $user) {
        parent::__construct();
    }

    public function index(): Eloquent\Collection {
        return Type::query()->orderBy('order')->get();
    }

    public function save(Request $request): void {
        $this->validate($request, [
            'url'      => 'required|string',
            'metadata' => 'required',
            'type_id'  => 'integer|exists:flag_types,id',
            'text'     => 'nullable|string',
            '_token'   => 'throttle',
        ]);

        /** @var array $metadata */
        $metadata = decrypt($request->input('metadata'));

        $flag = $this->transaction(function () use ($request, $metadata) {
            $data = $request->all() + ['user_id' => $this->userId];
            /** @var Flag $flag */
            $flag = Flag::query()->create($data);
            $this->attachFlagResources($flag, $metadata);
            stream(Activities\Create::class, (new Stream\Objects\Flag())->map($flag));
            return $flag;
        });

        $this->user->pushCriteria(new HasPermission($this->resourcePermissions($metadata)));
        $users = $this
            ->user
            ->all()
            ->sortByDesc(function (User $user): int {
                if ($user->id === $this->userId) {
                    return -2;
                }
                if ($user->is_online) {
                    return Carbon::now()->timestamp;
                }
                if ($user->visited_at) {
                    return $user->visited_at->timestamp;
                }
                return -1;
            })
            ->splice(0, 5);

        /** @var User $user */
        foreach ($users as $user) {
            $user->notify(new FlagCreatedNotification($flag));
        }
    }

    public function delete(Flag $flag): ?Response {
        $object = new Stream\Objects\Flag(['id' => $flag->id]);
        if (!$this->isAuthorized($flag)) {
            return response('Unauthorized.', 401);
        }
        $this->transaction(function () use ($flag, $object) {
            $flag->moderator_id = $this->userId;
            $flag->save();
            $flag->delete();
            stream(Stream\Activities\Delete::class, $object);
        });
        return null;
    }

    private function isAuthorized(Flag $flag): bool {
        /** @var Gate $gate */
        $gate = app(Gate::class);
        if (count($flag->forums)) {
            return $gate->allows('delete', $flag->forums[0]);
        }
        if (count($flag->microblogs)) {
            return $gate->allows('microblog-delete');
        }
        if (count($flag->jobs)) {
            return $gate->allows('job-delete');
        }
        return false;
    }

    private function attachFlagResources(Flag $flag, array $resources): void {
        foreach ($resources as $name => $id) {
            $this->flagResources($flag, $name)->attach($id);
        }
    }

    private function flagResources(Flag $flag, string $model): Eloquent\Relations\MorphToMany {
        if ($model === Post::class) {
            return $flag->posts();
        }
        if ($model === Topic::class) {
            return $flag->topics();
        }
        if ($model === Forum::class) {
            return $flag->forums();
        }
        if ($model === Microblog::class) {
            return $flag->microblogs();
        }
        if ($model === Job::class) {
            return $flag->jobs();
        }
        if ($model === Coyote\Comment::class) {
            return $flag->comments();
        }
        if ($model === Coyote\Post\Comment::class) {
            return $flag->postComments();
        }
        throw new \Exception('Unexpected reported model.');
    }

    private function resourcePermissions(array $resources): array {
        return \array_map($this->resourcePermission(...), \array_keys($resources));
    }

    private function resourcePermission(string $resource): string {
        $permissions = [
            Post::class                => 'forum-delete',
            Topic::class               => 'forum-delete',
            Forum::class               => 'forum-delete',
            Microblog::class           => 'microblog-delete',
            Job::class                 => 'job-delete',
            Coyote\Comment::class      => 'comment-delete', // comment in wiki
            Coyote\Post\Comment::class => 'forum-delete', // comment in post
        ];
        return $permissions[$resource];
    }
}
