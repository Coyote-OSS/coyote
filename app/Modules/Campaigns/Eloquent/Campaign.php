<?php
namespace Coyote\Modules\Campaigns\Eloquent;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    public $timestamps = false;
    protected $table = 'module_campaigns';
    protected $fillable = ['campaign_key', 'sidebar', 'horizontal', 'redirect_url'];
}
