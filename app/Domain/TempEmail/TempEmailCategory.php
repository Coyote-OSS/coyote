<?php
namespace Coyote\Domain\TempEmail;

enum TempEmailCategory {
    case TEMPORARY;
    case TRUSTED;
    case UNKNOWN;
}
