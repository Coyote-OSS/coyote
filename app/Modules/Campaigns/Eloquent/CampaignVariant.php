<?php
namespace Coyote\Modules\Campaigns\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $campaign_id
 * @property string $image_url
 * @property string $type
 */
class CampaignVariant extends Model {
    public $timestamps = false;
    protected $table = 'module_campaign_variants';
    protected $fillable = ['campaign_id', 'image_url', 'type'];

    public function campaign(): BelongsTo {
        return $this->belongsTo(Campaign::class);
    }
}
