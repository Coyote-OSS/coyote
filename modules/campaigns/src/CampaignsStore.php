<?php
namespace Modules\Campaigns;

interface CampaignsStore {
    function createIfNotExists(string $campaignKey): bool;
}
