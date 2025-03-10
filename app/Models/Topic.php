<?php

namespace Coyote;

use Carbon\Carbon;
use Coyote\Models\Scopes\Sortable;
use Coyote\Models\Scopes\TrackForum;
use Coyote\Models\Scopes\TrackTopic;
use Coyote\Models\Subscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * @property int $id
 * @property string $slug
 * @property int $replies
 * @property int $replies_real
 * @property \Carbon\Carbon $last_post_created_at
 * @property int $last_post_id
 * @property int $first_post_id
 * @property int $is_locked
 * @property int $is_sticky
 * @property int $views
 * @property int $forum_id
 * @property int $prev_forum_id
 * @property int $poll_id
 * @property int $score
 * @property float $rank
 * @property string $title
 * @property Forum $forum
 * @property Post\Accept $accept
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property Tag[] $tags
 * @property int $mover_id
 * @property int $locker_id
 * @property \Carbon\Carbon $moved_at
 * @property \Carbon\Carbon $locked_at
 * @property \Carbon\Carbon $read_at
 * @property int $subscribers
 * @property Poll $poll
 * @property Post $firstPost
 * @property boolean $is_tree
 */
class Topic extends Model
{
    use SoftDeletes, Sortable, Taggable, TrackTopic, TrackForum, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'slug', 'forum_id', 'is_sticky', 'poll_id'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * Hide tags from JSON or/and array. Tag contain closure that can't be serialized. We need to serialize post
     * in PostWasDeleted() class.
     *
     * @var array
     */
    protected $hidden = ['tags'];

    /**
     * @var array
     */
    protected $casts = [
        'is_locked'            => 'bool',
        'is_sticky'            => 'bool',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
        'deleted_at'           => 'datetime',
        'last_post_created_at' => 'datetime',
        'moved_at'             => 'datetime',
        'locked_at'            => 'datetime',
        'read_at'              => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function (Topic $model) {
            $model->rank = $model->getRank();
        });
    }

    /**
     * Scope used in topic filtering.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return mixed
     */
    public function scopeForUser($query, $userId)
    {
        return $this->buildWhereIn($query, $userId, 'topic_users');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @param string $table
     * @return mixed
     */
    private function buildWhereIn($query, $userId, $table)
    {
        return $query->whereIn('topics.id', function (Builder $sub) use ($userId, $table) {
            return $sub->select('topic_id')
                ->from($table)
                ->where('user_id', $userId);
        });
    }

    /**
     * @param $subject
     */
    public function setTitleAttribute($title)
    {
        $this->attributes['title'] = trim($title);
        $this->attributes['slug'] = str_slug($title, '_');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'resource', 'tag_resources');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function subscribers()
    {
        return $this->morphMany(Subscription::class, 'resource');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(Topic\User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forum()
    {
        return $this->belongsTo('Coyote\Forum');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function page()
    {
        return $this->morphOne('Coyote\Page', 'content');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function accept()
    {
        return $this->hasOne('Coyote\Post\Accept');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tracks()
    {
        return $this->hasMany('Coyote\Topic\Track');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany('Coyote\Post');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function firstPost()
    {
        return $this->hasOne('Coyote\Post', 'id', 'first_post_id')->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastPost()
    {
        return $this->hasOne('Coyote\Post', 'id', 'last_post_id')->withTrashed();
    }

    /**
     * This is being used in TopicRepository
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function poll()
    {
        return $this->belongsTo('Coyote\Poll');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prevForum()
    {
        return $this->belongsTo(Forum::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mover()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function locker()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Subscribe/unsubscribe to topic
     *
     * @param int $userId
     * @param bool $flag
     */
    public function subscribe($userId, $flag)
    {
        if (!$flag) {
            $this->subscribers()->forUser($userId)->delete();
        } else {
            $this->subscribers()->firstOrCreate(['user_id' => $userId]);
        }
    }

    /**
     * @param string|null $guestId
     * @return Carbon
     */
    public function markTime(?string $guestId)
    {
        if ($guestId !== null && !array_key_exists('read_at', $this->attributes)) {
            $this->attributes['read_at'] = $this->tracks()->select('marked_at')->where('guest_id', $guestId)->value('marked_at');
        }

        return $this->read_at;
    }

    /**
     * Mark topic as read
     *
     * @param \Carbon\Carbon $markTime
     * @param string $guestId
     */
    public function markAsRead($markTime, $guestId)
    {
        $sql = "INSERT INTO topic_track (topic_id, forum_id, guest_id, marked_at)
                VALUES(?, ?, ?, ?)
                ON CONFLICT ON CONSTRAINT topic_track_topic_id_guest_id_unique DO
                UPDATE SET marked_at = ?";

        $this->getConnection()->statement($sql, [$this->id, $this->forum_id, $guestId, $markTime, $markTime]);
        $this->read_at = $markTime;
    }

    /**
     * @param int $userId
     * Lock/unlock topic
     */
    public function lock(int $userId)
    {
        $this->is_locked = !$this->is_locked;
        $this->locked_at = $this->is_locked ? $this->freshTimestamp() : null;
        $this->locker_id = $this->is_locked ? $userId : null;

        $this->save();
    }

    /**
     * @return integer
     */
    public function getRank()
    {
        if ($this->id === null) {
            $this->last_post_created_at = $this->created_at = Carbon::now();
        }

        return min(1000, 200 * $this->score)
            + min(1000, 100 * $this->replies)
            + min(1000, 15 * $this->views)
            - ((time() - $this->last_post_created_at->timestamp) / 4500)
            - ((time() - $this->created_at->timestamp) / 1000);
    }

    /**
     * @param string $column
     * @param int $amount
     * @param array $extra
     * @return bool
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        return $this->forceFill(['rank' => $this->getRank(), 'views' => $this->views + $amount])->save();
    }
}
