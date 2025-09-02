<?php
namespace Coyote\Post;

use Carbon\Carbon;
use Coyote\Flag;
use Coyote\Post;
use Coyote\Services\Parser\Factories\CommentFactory;
use Coyote\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $text
 * @property string $html
 * @property int $post_id
 * @property int $user_id
 * @property int $id
 * @property Post $post
 * @property User $user
 * @property Carbon $created_at
 */
class Comment extends Model {
    use SoftDeletes;

    protected $fillable = ['post_id', 'user_id', 'text'];
    protected $appends = ['html'];
    protected $dateFormat = 'Y-m-d H:i:se';
    protected $table = 'post_comments';

    private string|null $html = null;

    public function post(): BelongsTo {
        return $this->belongsTo(Post::class)->withTrashed();
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class)
            ->select(['id', 'name', 'photo', 'is_blocked', 'is_online', 'deleted_at', 'reputation', 'is_incognito'])
            ->withTrashed();
    }

    public function flags(): MorphToMany {
        return $this->morphToMany(Flag::class, 'resource', 'flag_resources');
    }

    public function getHtmlAttribute(): string {
        if ($this->html === null) {
            $app = new CommentFactory(app(), $this->user_id);
            $this->html = $app->parse($this->text);
        }
        return $this->html;
    }
}
