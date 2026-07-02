<?php
namespace Coyote\Modules\Campaigns\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $redirect_url
 * @property string|null $active_since
 * @property string|null $active_until
 * @property int|null $target_views
 * @property string|null $name
 * @property string|null $description
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
