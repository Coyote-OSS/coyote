<?php
namespace Coyote\Models;

use Carbon\Carbon;
use Coyote;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Multiacc\User[]|Eloquent\Collection $users
 * @property Multiacc\User[]|Eloquent\Collection $multiaccUsers
 * @property Multiacc\Note[]|Eloquent\Collection $notes
 */
class Multiacc extends Model {
    public function notes(): HasMany {
        return $this
            ->hasMany(Multiacc\Note::class)
            ->orderByDesc('created_at');
    }

    public function users(): BelongsToMany {
        return $this
            ->belongsToMany(Coyote\User::class, 'multiacc_users')
            ->withTimestamps();
    }

    public function multiaccUsers(): HasMany {
        return $this
            ->hasMany(Multiacc\User::class)
            ->orderByDesc('created_at');
    }
}
