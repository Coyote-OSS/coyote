<?php
namespace Coyote\Modules\Campaigns\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $campaign_key
 * @property string $redirect_url
 * @property string $sidebar
 * @property string $horizontal
 * @property string $active_since
 * @property string $active_until
 * @property int $target_views
 * @property string $name
 * @property ?string $description
 * @property CampaignVariant[]|Collection $variants
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
        'name',
        'description',
    ];

    public function variants(): HasMany {
        return $this->hasMany(CampaignVariant::class, 'campaign_id');
    }
}
