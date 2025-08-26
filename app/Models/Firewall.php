<?php
namespace Coyote;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $moderator_id
 * @property Carbon $expire_at
 * @property string $reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $ip
 * @property string $fingerprint
 * @property User $user
 */
class Firewall extends Model {
    protected $table = 'firewall';

    protected $fillable = ['expire_at', 'user_id', 'ip', 'reason', 'moderator_id', 'fingerprint'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expire_at'  => 'datetime',
    ];

    protected $dateFormat = 'Y-m-d H:i:se';

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
