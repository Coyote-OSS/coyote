<?php
namespace Coyote\Models\Multiacc;

use Carbon\Carbon;
use Coyote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $content
 * @property Coyote\User $moderator
 * @property Carbon $created_at
 */
class Note extends Model {
    protected $table = 'multiacc_notes';
    protected $fillable = ['content', 'moderator_id', 'multiacc_id'];

    public function moderator(): BelongsTo {
        return $this->belongsTo(Coyote\User::class, 'moderator_id');
    }
}
