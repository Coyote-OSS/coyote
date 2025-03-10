<?php
namespace Coyote;

use Coyote\Services\Media\Factory as MediaFactory;
use Coyote\Services\Media\File;
use Coyote\Services\Media\Logo;
use Coyote\Services\Media\SerializeClass;
use Coyote\Tag\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $real_name
 * @property string $text
 * @property int $category_id
 * @property Category $category
 * @property File $logo
 * @property int $topics
 * @property int $jobs
 * @property int $microblogs
 */
class Tag extends Model
{
    use SoftDeletes, SerializeClass;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'real_name', 'category_id', 'text'];

    /**
     * @var string[]
     */
    protected $casts = ['resources' => 'json'];

    /**
     * @var string[]
     */
    protected $attributes = ['resources' => '{}'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getLogoAttribute($value): File
    {
        if (!($value instanceof Logo)) {
            $logo = app(MediaFactory::class)->make('logo', ['file_name' => $value]);
            $this->attributes['logo'] = $logo;
        }
        return $this->attributes['logo'];
    }

    public function getTopicsAttribute()
    {
        return $this->resources[Topic::class] ?? 0;
    }

    public function getJobsAttribute()
    {
        return $this->resources[Job::class] ?? 0;
    }

    public function getMicroblogsAttribute()
    {
        return $this->resources[Microblog::class] ?? 0;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
