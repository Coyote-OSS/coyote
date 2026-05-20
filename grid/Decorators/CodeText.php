<?php
namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

class CodeText extends Decorator {
    public function decorate(Cell $cell): void {
        $cell->getColumn()->setAutoescape(false);
        $cell->setValue('<code>' . htmlSpecialChars($cell->getUnescapedValue()) . '</code>');
    }
}
