<?php

namespace Coyote;

use Coyote\Forum\Access;
use Coyote\Models\Scopes\TrackForum;
use Coyote\Models\Subscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property int $parent_id
 * @property int $topics
 * @property int $posts
 * @property int $redirects
 * @property int $order
 * @property int $last_post_id
 * @property int $is_locked
 * @property int $require_tag
 * @property int $enable_prune
 * @property int $enable_reputation
 * @property int $enable_anonymous
 * @property int $prune_days
 * @property int $prune_last
 * @property string $name
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $section
 * @property string $url
 * @property Forum $parent
 * @property Forum\Track[] $tracks
 * @property bool $is_prohibited
 * @property Post $post
 * @property \Carbon\Carbon $read_at
 * @property Group[]|\Illuminate\Support\Collection $groups
 * @property Tag[] $tags
 */
class Forum extends Model
{
    use TrackForum;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'name',
        'title',
        'slug',
        'description',
        'section',
        'url',
        'order',
        'is_locked',
        'require_tag',
        'enable_reputation',
        'enable_anonymous',
        'enable_tags',
        'enable_homepage',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'redirects'         => 'int',
        'is_locked'         => 'bool',
        'is_prohibited'     => 'bool',
        'require_tag'       => 'bool',
        'enable_reputation' => 'bool',
        'enable_anonymous'  => 'bool',
        'enable_prune'      => 'bool',
        'read_at'           => 'datetime',
    ];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var bool
     */
    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::saving(function (Forum $model) {
            if (empty($model->parent_id)) {
                $model->parent_id = null;
            }
        });
    }

    public function subscribers(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'resource');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function access()
    {
        return $this->hasMany(Access::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function groups()
    {
        return $this->hasManyThrough(Group::class, Access::class, 'forum_id', 'id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany(\Coyote\Forum\Permission::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tracks()
    {
        return $this->hasMany('Coyote\Forum\Track');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function page()
    {
        return $this->morphOne('Coyote\Page', 'content');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function order()
    {
        return $this->hasMany('Coyote\Forum\Order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent()
    {
        return $this->hasOne('Coyote\Forum', 'id', 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function post()
    {
        return $this->hasOne(Post::class, 'id', 'last_post_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'resource', 'tag_resources');
    }

    /**
     * Checks ability for specified forum and user id
     *
     * @param string $name
     * @param int $userId
     * @return bool
     */
    public function ability(string $name, int $userId)
    {
        static $acl = null;

        if (!isset($acl[$userId])) {
            $acl[$userId] = $this->permissions()
                ->join('permissions AS p', 'p.id', '=', 'forum_permissions.permission_id')
                ->join('group_users AS ug', 'ug.group_id', '=', 'forum_permissions.group_id')
                ->where('ug.user_id', $userId)
                ->orderBy('value')
                ->select(['name', 'value'])
                ->pluck('value', 'name');
        }

        return isset($acl[$userId][$name]) ? $acl[$userId][$name] : false;
    }

    /**
     * @param string|null $guestId
     * @return mixed
     */
    public function markTime(?string $guestId)
    {
        if ($guestId !== null && !array_key_exists('read_at', $this->attributes)) {
            $this->attributes['read_at'] = $this->tracks()
                ->select('marked_at')
                ->where('guest_id', $guestId)
                ->value('marked_at');
        }

        return $this->read_at;
    }

    /**
     * Mark forum as read
     *
     * @param $guestId
     */
    public function markAsRead($guestId)
    {
        $markTime = now();

        $sql = "INSERT INTO forum_track (forum_id, guest_id, marked_at)
                VALUES(?, ?, ?)
                ON CONFLICT ON CONSTRAINT forum_track_forum_id_guest_id_unique DO
                UPDATE SET marked_at = ?";

        $this->getConnection()->statement($sql, [$this->id, $guestId, $markTime, $markTime]);
    }

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
