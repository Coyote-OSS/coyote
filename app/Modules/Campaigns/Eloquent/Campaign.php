<?php
namespace Coyote\Modules\Campaigns\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $campaign_key
 * @property string $redirect_url
 * @property string $sidebar
 * @property string $horizontal
 * @property string $active_since
 * @property string $active_until
 * @property int $target_views
 */
class Campaign extends Model {
    public $timestamps = false;
    protected $table = 'module_campaigns';
    protected $fillable = [
        'campaign_key',
        'sidebar',
        'horizontal',
        'redirect_url',
        'active_since',
        'active_until',
        'target_views',
    ];
}
