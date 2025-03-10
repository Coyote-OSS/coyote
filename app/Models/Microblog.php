<?php
namespace Coyote;

use Carbon\Carbon;
use Coyote\Microblog\Vote;
use Coyote\Models\Asset;
use Coyote\Models\Scopes\ForUser;
use Coyote\Models\Scopes\UserRelationsScope;
use Coyote\Models\Subscription;
use Coyote\Services\Media\SerializeClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support;

/**
 * @property int $id
 * @property int $user_id
 * @property int $parent_id
 * @property int $votes
 * @property int $score
 * @property int $is_sponsored
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property string $text
 * @property string $html
 * @property Microblog $parent
 * @property Microblog[] $comments
 * @property Tag[] $tags
 * @property User $user
 * @property Microblog[] $children
 * @property Microblog\Vote[]|Support\Collection $voters
 * @property Asset[]|Support\Collection $assets
 */
class Microblog extends Model
{
    use SoftDeletes, Taggable, ForUser, SerializeClass, Searchable;

    protected $fillable = ['parent_id', 'user_id', 'text'];

    protected $dateFormat = 'Y-m-d H:i:se';

    protected $casts = [
        'is_sponsored' => 'bool',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    protected $attributes = [
        'votes' => 0,
        'views' => 1,
    ];

    private ?string $html = null;

    public static function boot(): void
    {
        parent::boot();
        static::creating(fn(Microblog $model) => $model->resetScore());
        static::addGlobalScope(resolve(UserRelationsScope::class));
    }

    public function assets(): MorphMany
    {
        return $this->morphMany(Asset::class, 'content');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'resource', 'tag_resources');
    }

    public function resetScore(): void
    {
        $this->score = $this->getScore();
    }

    private function getScore(): int
    {
        $timestamp = $this->created_at ? $this->created_at->timestamp : time();
        $hours = (int)($timestamp - 1380153600) / 3600; // since 26 september, 2023.
        return $this->votes * 5 + $hours;
    }

    public function comments(): HasMany
    {
        return $this
            ->hasMany(self::class, 'parent_id', 'id')
            ->orderBy('microblogs.id', 'ASC');
    }

    public function subscribers(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'resource');
    }

    public function voters(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function page(): MorphOne
    {
        return $this->morphOne(Page::class, 'content');
    }

    public function parent(): HasOne
    {
        return $this
            ->hasOne(Microblog::class, 'id', 'parent_id')
            ->withoutGlobalScope(UserRelationsScope::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)
            ->select(['id', 'name', 'deleted_at', 'is_blocked', 'photo', 'is_online', 'reputation'])
            ->withTrashed();
    }

    public function flags(): MorphToMany
    {
        return $this->morphToMany(Flag::class, 'resource', 'flag_resources');
    }

    public function setHtmlAttribute(string $value): void
    {
        $this->html = $value;
    }

    public function getHtmlAttribute(): string
    {
        if ($this->html === null) {
            $this->html = app('parser.post')->parse($this->text);
        }
        return $this->html;
    }

    public function scopeIncludeIsSubscribed(Builder $builder, int $userId): Builder
    {
        $this->addSelectIfNull($builder);
        return $builder
            ->addSelect(new Expression('CASE WHEN mw.user_id IS NULL THEN false ELSE true END AS is_subscribed'))
            ->leftJoin('subscriptions AS mw', fn(JoinClause $join) => $join
                ->on('mw.resource_id', '=', 'microblogs.id')
                ->where('mw.resource_type', '=', static::class)
                ->where('mw.user_id', '=', $userId));
    }

    public function scopeIncludeIsVoted(Builder $builder, int $userId): Builder
    {
        $this->addSelectIfNull($builder);
        return $builder
            ->addSelect(new Expression('CASE WHEN mv.id IS NULL THEN false ELSE true END AS is_voted'))
            ->leftJoin('microblog_votes AS mv', fn(JoinClause $join) => $join
                ->on('mv.microblog_id', '=', 'microblogs.id')
                ->where('mv.user_id', '=', $userId));
    }

    private function addSelectIfNull(Builder $builder): void
    {
        if ($builder->getQuery()->columns === null) {
            $builder->select([$builder->getQuery()->from . '.*']);
        }
    }
}
