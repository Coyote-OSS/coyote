<?php

namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

class Link extends Decorator
{
    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @param \Closure $closure
     * @return $this
     */
    public function render(\Closure $closure)
    {
        $this->closure = $closure;

        return $this;
    }

    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        // disable auto escape so we can display <a> html tag in cell
        $cell->getColumn()->setAutoescape(false);

        $cell->setValue(($this->closure)($cell->getData()));
    }
}
