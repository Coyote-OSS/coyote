<?php
namespace Coyote\Models\Multiacc;

use Carbon\Carbon;
use Coyote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $multiacc_id
 * @property int $user_id
 * @property int $moderator_id
 * @property Coyote\User $user
 * @property Coyote\User $moderator
 * @property Carbon $created_at
 */
class User extends Model {
    protected $table = 'multiacc_users';

    public function user(): BelongsTo {
        return $this->belongsTo(Coyote\User::class, 'user_id');
    }

    public function moderator(): BelongsTo {
        return $this->belongsTo(Coyote\User::class, 'moderator_id');
    }
}
