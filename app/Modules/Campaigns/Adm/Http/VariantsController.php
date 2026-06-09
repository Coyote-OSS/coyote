<?php
namespace Coyote\Modules\Campaigns\Adm\Http;

use Coyote\Http\Controllers\Adm\BaseController;

class VariantsController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->breadcrumb->push('Kampanie', route('adm.campaigns'));
        $this->breadcrumb->push('Warianty', route('adm.campaigns'));
    }

    public function save(): void {}
}
