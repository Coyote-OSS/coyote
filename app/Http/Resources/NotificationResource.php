<?php
namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Domain\Initials;
use Coyote\Notification\Sender;
use Coyote\Services\Declination;
use Coyote\Services\Media\Factory;
use Coyote\User;
use Illuminate\Database\Eloquent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Carbon $read_at
 * @property bool $is_clicked
 * @property User $user
 */
class NotificationResource extends JsonResource {
    public function toArray(Request $request): array {
        $senders = $this->resource->senders->unique('name');
        $user = $senders->first();

        return \array_merge(
            $this->resource->only(['subject', 'excerpt', 'id', 'url']),
            [
                'is_read'    => $this->is_clicked && $this->read_at,
                'is_clicked' => $this->is_clicked,
                'headline'   => $this->getHeadline($user, $senders),
                'created_at' => $this->resource->created_at->toIso8601String(),
                'photo'      => $this->getMediaUrl($user ? $user->photo : null),
                'user_id'    => $user->user_id,
                'user'       => [
                    'id'   => $user->id,
                    'name' => $user->name,
                ],
                'initials'   => $this->initials($user ? $user->name : null),
            ]);
    }

    private function getMediaUrl(?string $filename): string {
        if ($filename) {
            return app(Factory::class)->make('photo', ['file_name' => $filename])->url();
        }
        return '';
    }

    private function getHeadline(Sender $user, Eloquent\Collection $senders): string {
        $count = $senders->count();
        if ($count === 0) {
            return ''; // no senders? return empty notification
        }
        return str_replace(
            '{sender}',
            $this->sender($count, $user, $senders),
            $this->resource->headline);
    }

    private function sender(int $count, Sender $user, Eloquent\Collection $senders): string {
        if ($count === 2) {
            return $user->name . ' (oraz ' . $senders->last()->name . ')';
        }
        if ($count > 2) {
            return $user->name . ' (oraz ' . Declination::format($count - 1, ['osoba', 'osoby', 'osób']) . ')';
        }
        return $user->name;
    }

    private function initials(string $username): string {
        return (new Initials())->of($username);
    }
}
