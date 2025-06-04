<?php
namespace Coyote\Console\Commands;

class LogoDownloadForbidden extends \RuntimeException {
    public function __construct(
        public string $logoUrl,
    ) {
        parent::__construct();
    }
}
