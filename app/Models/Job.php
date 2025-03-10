<?php
namespace Coyote;

use Carbon\Carbon;
use Coyote\Job\Location;
use Coyote\Models\Scopes\ForUser;
use Coyote\Models\Subscription;
use Coyote\Services\Eloquent\HasMany;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\RoutesNotifications;

/**
 * @property int $id
 * @property int $user_id
 * @property int $firm_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property \Carbon\Carbon $deadline_at
 * @property \Carbon\Carbon $boost_at
 * @property bool $is_expired
 * @property int $salary_from
 * @property int $salary_to
 * @property int $currency_id
 * @property int $is_remote
 * @property int $remote_range
 * @property int $enable_apply
 * @property int $visits
 * @property string $rate
 * @property bool $is_gross
 * @property string $employment
 * @property string $seniority
 * @property int $views
 * @property float $score
 * @property float $rank
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $recruitment
 * @property string $email
 * @property string $phone
 * @property User $user
 * @property Firm $firm
 * @property Tag[]|Eloquent\Collection $tags
 * @property Location[]|Eloquent\Collection $locations
 * @property Currency $currency
 * @property Feature[]|Eloquent\Collection $features
 * @property int $plan_id
 * @property bool $is_boost
 * @property bool $is_publish
 * @property bool $is_ads
 * @property bool $is_highlight
 * @property bool $is_on_top
 * @property Plan $plan
 * @property Comment[] $comments
 * @property Comment[] $commentsWithChildren
 * @property integer|null $ad_views
 * @property string|null $apply_type
 * @property string|null $application_url
 * @property integer|null application_redirects
 */
class Job extends Model
{
    use SoftDeletes, ForUser, RoutesNotifications, Searchable;

    const MONTHLY = 'monthly';
    const YEARLY = 'yearly';
    const WEEKLY = 'weekly';
    const HOURLY = 'hourly';

    const STUDENT = 'student';
    const JUNIOR = 'junior';
    const MID = 'mid';
    const SENIOR = 'senior';
    const LEAD = 'lead';
    const MANAGER = 'manager';

    const EMPLOYMENT = 'employment';
    const MANDATORY = 'mandatory';
    const CONTRACT = 'contract';
    const B2B = 'b2b';

    /**
     * Filling each field adds points to job offer score.
     */
    const SCORE_CONFIG = [
        'job'  => ['salary_from' => 25, 'salary_to' => 25, 'city' => 15, 'seniority' => 5],
        'firm' => ['name' => 15, 'logo' => 5, 'website' => 1, 'description' => 5],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'recruitment',
        'is_remote',
        'is_gross',
        'remote_range',
        'salary_from',
        'salary_to',
        'currency_id',
        'rate',
        'employment',
        'deadline_at',
        'email',
        'phone',
        'enable_apply',
        'apply_type',
        'application_url',
        'seniority',
        'plan_id',
        'locations',

        'currency',
        'plan',
    ];

    /**
     * Default fields values.
     *
     * @var array
     */
    protected $attributes = [
        'enable_apply' => true,
        'is_remote'    => false,
        'title'        => '',
        'remote_range' => 100,
        'currency_id'  => Currency::PLN,
        'is_gross'     => false,
        'rate'         => self::MONTHLY,
        'employment'   => self::EMPLOYMENT,
    ];

    /**
     * Cast to when calling toArray() (for example before index in elasticsearch).
     *
     * @var array
     */
    protected $casts = [
        'is_remote'    => 'boolean',
        'is_boost'     => 'boolean',
        'is_gross'     => 'boolean',
        'is_publish'   => 'boolean',
        'is_ads'       => 'boolean',
        'is_highlight' => 'boolean',
        'is_on_top'    => 'boolean',
        'plan_id'      => 'int',
        'score'        => 'int',
        'enable_apply' => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deadline_at'  => 'datetime',
        'boost_at'     => 'datetime',
    ];

    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * We need to set firm id to null offer is private
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function (Job $model) {
            $model->score = $model->getScore();
        });

        static::creating(function (Job $model) {
            $model->boost_at = $model->freshTimestamp();
            $model->deadline_at = $model->freshTimestamp()->addDays($model->plan->length);
        });
    }

    /**
     * @return string[]
     */
    public static function getRatesList()
    {
        return trans('common.rate');
    }

    /**
     * @return string[]
     */
    public static function getTaxList()
    {
        return trans('common.tax');
    }

    /**
     * @return string[]
     */
    public static function getEmploymentList()
    {
        return trans('common.employment');
    }

    /**
     * @return string[]
     */
    public static function getSeniorityList()
    {
        return trans('common.seniority');
    }

    /**
     * @return array
     */
    public static function getRemoteRangeList()
    {
        $list = [];

        for ($i = 100; $i > 0; $i -= 10) {
            $list[$i] = "$i%";
        }

        return $list;
    }

    /**
     * @return int
     */
    public function getScore()
    {
        $score = 0;

        // 70 points maximum...
        foreach (self::SCORE_CONFIG['job'] as $column => $point) {
            if (!empty($this->{$column})) {
                $score += $point;
            }
        }

        // 30 points maximum...
        $score += min(30, (count($this->tags()->get()) * 10));
        // 50 points maximum
        $score += min(50, count($this->features()->wherePivot('checked', true)->get()) * 5);

        if ($this->firm_id) {
            $firm = $this->firm;

            // 26 points maximum ...
            foreach (self::SCORE_CONFIG['firm'] as $column => $point) {
                if (!empty($firm->{$column})) {
                    $score += $point;
                }
            }

            // 25 points maximum...
            $score += min(25, $firm->benefits()->count() * 5);
            $score -= ($firm->is_agency * 15);
        } else {
            $score -= 15;
        }

        return max(1, $score); // score can't be negative. 1 point min for elasticsearch algorithm
    }

    /**
     * Scope for currently active job offers
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopePriorDeadline($query)
    {
        return $query->where('deadline_at', '>', date('Y-m-d H:i:s'));
    }

    /**
     * @return HasMany
     */
    public function locations()
    {
        $instance = new Job\Location();

        return new HasMany($instance->newQuery(), $this, $instance->getTable() . '.' . $this->getForeignKey(), $this->getKeyName());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function page()
    {
        return $this->morphOne('Coyote\Page', 'content');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firm()
    {
        return $this->belongsTo('Coyote\Firm')->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo('Coyote\Currency');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referers()
    {
        return $this->hasMany('Coyote\Job\Referer');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'resource', 'tag_resources')->withPivot(['priority', 'order'])->orderByPivot('priority', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function features()
    {
        return $this->belongsToMany('Coyote\Feature', 'job_features')->orderBy('order')->withPivot(['checked', 'value']);
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
    public function applications()
    {
        return $this->hasMany('Coyote\Job\Application');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refers()
    {
        return $this->hasMany('Coyote\Job\Refer');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Coyote\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany('Coyote\Payment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'resource');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @todo duplikat z modelem Guide.php
     */
    public function commentsWithChildren()
    {
        $userRelation = fn($builder) => $builder->select(['id', 'name', 'photo', 'deleted_at', 'is_blocked', 'is_online'])->withTrashed();

        return $this
            ->comments()
            ->whereNull('parent_id')
            ->orderBy('id', 'DESC')
            ->with([
                'children' => function ($builder) use ($userRelation) {
                    return $builder->orderBy('id')->with(['user' => $userRelation]);
                },
                'user'     => $userRelation,
            ]);
    }

    /**
     * @param string $title
     */
    public function setTitleAttribute($title)
    {
        $title = trim($title);

        $this->attributes['title'] = $title;
        $this->attributes['slug'] = str_slug($title, '_');
    }

    /**
     * @param string $value
     */
    public function setSalaryFromAttribute($value)
    {
        $this->attributes['salary_from'] = $value === null ? null : (int)trim($value);
    }

    /**
     * @param string $value
     */
    public function setSalaryToAttribute($value)
    {
        $this->attributes['salary_to'] = $value === null ? null : (int)trim($value);
    }

    public function getDeadlineAttribute(): int
    {
        $deadline = new Carbon($this->deadline_at);
        $current = Carbon::now();
        return $deadline->diff($current)->dayz;
    }

    public function getIsExpiredAttribute(): bool
    {
        return new Carbon($this->deadline_at)->isPast();
    }

    public function getCurrencySymbolAttribute(): string
    {
        return $this->currency()->value('symbol');
    }

    public function setLocationsAttribute($locations): void
    {
        $models = [];

        // remove empty locations before adding...
        foreach (array_filter(array_map(array_filter(...), $locations)) as $location) {
            $models[] = new Job\Location($location);
        }

        // set only if not empty. important!
        if ($models) {
            $this->setRelation('locations', collect($models));
        }
    }

    /**
     * Set currency as name. This is being used on API routes
     */
    public function setCurrencyAttribute($currency)
    {
        $this->attributes['currency_id'] = Currency::where('name', $currency)->value('id');
    }

    /**
     * This is being used on API routes
     *
     */
    public function setRecruitmentAttribute($recruitment)
    {
        if (!empty($recruitment)) {
            $this->attributes['enable_apply'] = false;
        }

        $this->attributes['recruitment'] = $recruitment;
    }

    /**
     * Set plan as name. This is being used on API routes
     *
     * @param string $plan
     */
    public function setPlanAttribute($plan)
    {
        $this->plan_id = Plan::where('is_active', 1)->whereRaw('LOWER(name) = ?', $plan)->value('id');
    }

    /**
     * @return Payment
     */
    public function getUnpaidPayment()
    {
        return $this->payments()->where('status_id', Payment::NEW)->orderBy('created_at', 'DESC')->first();
    }

    /**
     * @param string $url
     */
    public function addReferer($url)
    {
        if ($url && mb_strlen($url) < 200) {
            $referer = $this->referers()->firstOrNew(['url' => $url]);

            if (!$referer->id) {
                $referer->save();
            } else {
                $referer->increment('count');
            }
        }
    }

    /**
     * @param float|null $salary
     * @return float|null
     *
     * @see \Coyote\Http\Resources\Elasticsearch\JobResource
     */
    public function monthlySalary(?float $salary): ?float
    {
        if (empty($salary) || $this->rate === self::MONTHLY) {
            return $salary;
        }

        // we need to calculate monthly salary in order to sorting data by salary
        if ($this->rate == self::YEARLY) {
            $salary = round($salary / 12);
        } else if ($this->rate == self::WEEKLY) {
            $salary = round($salary * 4);
        } else if ($this->rate == self::HOURLY) {
            $salary = round($salary * 8 * 5 * 4);
        }

        return $salary;
    }
}
