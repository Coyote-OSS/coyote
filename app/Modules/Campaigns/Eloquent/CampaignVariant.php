<?php
namespace Coyote\Modules\Campaigns\Eloquent;

use Coyote\Modules\Campaigns\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $campaign_id
 * @property string $image_url
 * @property string $type
 * @property int $views
 * @property int $clicks
 * @property int $exposures
 * @property Eloquent\Campaign $campaign
 */
class CampaignVariant extends Model {
    public $timestamps = false;
    protected $table = 'module_campaign_variants';
    protected $fillable = ['campaign_id', 'image_url', 'type', 'views', 'clicks', 'exposures'];

    public function campaign(): BelongsTo {
        return $this->belongsTo(Eloquent\Campaign::class);
    }
}
