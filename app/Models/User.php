<?php
namespace Coyote;

use Carbon\Carbon;
use Coyote\Feature\Trial\TrialSession;
use Coyote\Models\Scopes\ExcludeBlocked;
use Coyote\Models\UserPlanBundle;
use Coyote\Notifications\ResetPasswordNotification;
use Coyote\Services\Media;
use Coyote\Services\Media\File;
use Coyote\Services\Media\Photo;
use Coyote\User\Relation;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\RoutesNotifications;
use Illuminate\Support;
use Illuminate\Support\Collection;
use Laravel\Passport\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Ramsey\Uuid\Uuid;

/**
 * @property int $id
 * @property string $guest_id
 * @property bool $is_confirm
 * @property bool $is_blocked
 * @property int $group_id
 * @property string $group_name
 * @property int $visits
 * @property int $notifications
 * @property int $pm
 * @property int $notifications_unread
 * @property int $pm_unread
 * @property int $posts
 * @property int $allow_count
 * @property int $allow_subscribe
 * @property int $allow_smilies
 * @property int $allow_sig
 * @property int $allow_sticky_header
 * @property bool $marketing_agreement
 * @property bool $newsletter_agreement
 * @property int $birthyear
 * @property int $reputation
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $provider
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $visited_at
 * @property Carbon $deleted_at
 * @property string $date_format
 * @property string $timezone
 * @property string $ip
 * @property string $browser
 * @property string $website
 * @property string $github
 * @property string $location
 * @property float $latitude
 * @property float $longitude
 * @property string $firm
 * @property string $position
 * @property string $access_ip
 * @property string $sig
 * @property File $photo
 * @property bool $is_online
 * @property bool $alert_login
 * @property \Coyote\Notification\Setting[] $notificationSettings
 * @property Group[]|Collection $groups
 * @property Group $group
 * @property Relation $relations
 * @property bool $is_sponsor
 * @property User[] $followers
 * @property Tag[] $skills
 * @property string|null $gdpr
 * @property Guest|null $guest
 * @property TrialSession|null $trialSession
 * @property UserPlanBundle[]|Eloquent\Collection $planBundles
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {
    use Authenticatable, Authorizable, CanResetPassword, RoutesNotifications, HasApiTokens, SoftDeletes, ExcludeBlocked, HasPushSubscriptions;

    protected $table = 'users';
    protected $attributes = ['date_format' => '%Y-%m-%d %H:%M'];
    protected $fillable = [
        'provider',
        'provider_id',
        'photo',
        'date_format',
        'location',
        'latitude',
        'longitude',
        'website',
        'bio',
        'sig',
        'firm',
        'position',
        'birthyear',
        'allow_count',
        'allow_smilies',
        'allow_sig',
        'allow_subscribe',
        'allow_sticky_header',
        'marketing_agreement',
    ];
    protected $hidden = ['password', 'remember_token', 'email', 'provider_id', 'provider', 'guest_id'];
    protected $dateFormat = 'Y-m-d H:i:se';
    protected $casts = [
        'allow_smilies'       => 'int',
        'allow_sig'           => 'int',
        'allow_count'         => 'int',
        'allow_subscribe'     => 'bool',
        'allow_sticky_header' => 'int',
        'is_confirm'          => 'int',
        'is_blocked'          => 'bool',
        'is_online'           => 'bool',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'visited_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    public static function boot(): void {
        parent::boot();
        static::creating(function (User $model) {
            if (empty($model->guest_id)) {
                $model->guest_id = (string)Uuid::uuid4();
            }
        });
        static::saving(function (User $user) {
            // save group name. it rarely changes
            $user->group_name = $user->group_id ? $user->group->name : null;
        });
    }

    /**
     * Generuje liste z rocznikiem urodzenia (do wyboru m.in. w panelu uzytkownika)
     *
     * @return array
     * @deprecated
     */
    public static function birthYearList(): array {
        $result = [null => '--'];
        for ($i = 1950, $year = date('Y'); $i <= $year; $i++) {
            $result[$i] = $i;
        }
        return $result;
    }

    public static function dateFormatList(): array {
        $dateFormats = [
            '%d-%m-%Y %H:%M',
            '%Y-%m-%d %H:%M',
            '%m/%d/%y %H:%M',
            '%d-%m-%y %H:%M',
            '%d %b %y %H:%M',
            '%d %B %Y, %H:%M',
        ];
        return \array_combine($dateFormats, \array_map('\strFTime', $dateFormats));
    }

    public function group(): HasOne {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }

    public function groups(): BelongsToMany {
        return $this->belongsToMany(Group::class, 'group_users');
    }

    public function permissions(): HasManyThrough {
        return $this->hasManyThrough(Group\Permission::class, Group\User::class, 'user_id', 'group_id');
    }

    public function actkey(): HasMany {
        return $this->hasMany(Actkey::class);
    }

    public function guest(): BelongsTo {
        return $this->belongsTo(Guest::class);
    }

    public function skills(): MorphToMany {
        return $this->morphToMany(Tag::class, 'resource', 'tag_resources')
            ->withPivot(['priority', 'order'])
            ->orderByPivot('priority', 'desc');
    }

    public function invoices(): HasMany {
        return $this->hasMany(Invoice::class);
    }

    public function notifications(): HasMany {
        return $this->hasMany(Notification::class);
    }

    public function relations(): HasMany {
        return $this->hasMany(Relation::class);
    }

    public function followers(): HasManyThrough {
        return $this
            ->hasManyThrough(User::class, Relation::class, 'related_user_id', 'id', 'id', 'user_id')
            ->where('user_relations.is_blocked', false);
    }

    /**
     * @param string $objectId
     * @return Model|null|static
     */
    public function getUnreadNotification($objectId) {
        return $this
            ->hasOne(Notification::class)
            ->where('object_id', '=', $objectId)
            ->whereNull('read_at')
            ->first();
    }

    public function notificationSettings(): HasMany {
        return $this->hasMany(Notification\Setting::class);
    }

    public function getPhotoAttribute($value): File {
        if (!$value instanceof Photo) {
            $this->attributes['photo'] = Media\Factory::get()->userAvatar($value);
        }
        return $this->attributes['photo'];
    }

    /**
     * @deprecated
     */
    public function setIsActiveAttribute($value) {
        $this->is_online = false;
        $this->setAttribute('deleted_at', !$value ? Carbon::now() : null);
    }

    public function getIsActiveAttribute() {
        return $this->deleted_at === null;
    }

    public function canReceiveEmail(): bool {
        return $this->email && !$this->deleted_at && $this->is_confirm && !$this->is_blocked;
    }

    /**
     * Get user's permissions (including all user's groups)
     */
    public function getPermissions(): Support\Collection {
        return $this
            ->permissions()
            ->join('permissions AS p', 'p.id', '=', 'group_permissions.permission_id')
            ->orderBy('value')
            ->select(['name', 'value'])
            ->get()
            ->pluck('value', 'name');
    }

    /**
     * @param string $ip
     * @return bool
     */
    public function hasAccessByIp($ip): bool {
        if (empty($this->access_ip)) {
            return true;
        }

        $access = false;
        $ipParts = explode('.', $this->access_ip);

        for ($i = 0, $count = count($ipParts); $i < $count; $i += 4) {
            $regexp = str_replace('*', '.*', str_replace('.', '\.', implode('.', array_slice($ipParts, $i, 4))));

            if (preg_match('#^' . $regexp . '$#', $ip)) {
                $access = true;
                break;
            }
        }

        return $access;
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn(): string {
        return 'user:' . $this->id;
    }

    public function trialSession(): Eloquent\Relations\HasOne {
        return $this->hasOne(TrialSession::class);
    }

    public function planBundles(): HasMany {
        return $this->hasMany(UserPlanBundle::class);
    }
}
