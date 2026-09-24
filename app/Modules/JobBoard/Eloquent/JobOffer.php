<?php
namespace Coyote\Modules\JobBoard\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property int $clicks
 */
class JobOffer extends Model {
    protected $table = 'jobs';
    protected $fillable = ['user_id', 'firm_id', 'plan_id', 'is_publish', 'title', 'slug', 'deadline_at', 'clicks'];
}
