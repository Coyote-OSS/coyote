<?php

namespace Coyote;

use Coyote\Wiki\Page as Wiki_Page;
use Coyote\Wiki\Subscriber;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $wiki_id
 * @property int $parent_id
 * @property int $views
 * @property string $title
 * @property string $long_title
 * @property string $slug
 * @property string $excerpt
 * @property string $text
 * @property string $path
 * @property int $is_locked
 * @property string $template
 * @property Wiki\Comment[] $comments
 * @property Wiki\Attachment[] $attachments
 * @property Wiki\Log[]|Eloquent\Collection $logs
 */
class Wiki extends Model
{
    use SoftDeletes, Searchable;

    protected $table = 'wiki';

    protected $fillable = ['title', 'long_title', 'excerpt', 'text'];

    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * Related to Laravel 5.8. deleted_at has different date format that created_at and carbon throws exception
     *
     * @var string[]
     */
    protected $casts = [
        'deleted_at' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Html version of the post.
     *
     * @var null|string
     */
    private $html = null;

    /**
     * Make slug. This function maintains compatibility to older 4programmers.net version.
     *
     * @param $title
     * @return string
     */
    public static function slug($title)
    {
        $title = trim($title, '/.');
        $title = str_replace(
            ['^', '$', ';', '#', '&', '(', ')', '`', '\\', '|', ',', '?', '%', '~', '[', ']', '{', '}', ':', '\/', '=', '!', '"', "'", '<', '>'],
            '',
            $title,
        );

        $title = ucfirst(mb_strtolower($title));
        $title = str_replace(' ', '_', str_replace(["\t", "\n"], '', $title));

        return trim(preg_replace('/[\\_]+/', '_', $title), '_');
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
    public function subscribers()
    {
        return $this->hasMany(Subscriber::class, 'wiki_id', 'wiki_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany('Coyote\Wiki\Log', 'wiki_id', 'wiki_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paths()
    {
        return $this->hasMany('Coyote\Wiki\Path');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent()
    {
        return $this->hasOne('Coyote\Wiki', 'id', 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany('Coyote\Wiki', 'parent_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Coyote\Wiki\Comment', 'wiki_id', 'wiki_id')->orderByDesc('id');
    }

    /**
     * @return mixed
     */
    public function authors()
    {
        return $this
            ->hasMany('Coyote\Wiki\Author', 'wiki_id', 'wiki_id')
            ->join('users', 'users.id', '=', 'user_id')
            ->orderBy('share', 'DESC');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function links()
    {
        return $this->hasMany('Coyote\Wiki\Link', 'path_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany('Coyote\Wiki\Attachment');
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function wasUserInvolved($userId)
    {
        return $this->logs()->forUser($userId)->exists();
    }

    /**
     * @param string $column
     * @param int $amount
     * @param array $extra
     * @return int
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        // we cannot update view so let's update "views" column in wiki_pages table
        $page = new Wiki_Page();
        $page->timestamps = false;

        return $page->where('id', $this->wiki_id)->update([$column => $this->views + $amount]);
    }

    /**
     * @return string
     */
    public function getHtmlAttribute()
    {
        if ($this->html !== null) {
            return $this->html;
        }

        return $this->html = app('parser.wiki')->parse($this->text ?? '');
    }
}
