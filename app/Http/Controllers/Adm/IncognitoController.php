<?php
namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Grids\Adm\IncognitoGrid;
use Coyote\User;
use Illuminate\View\View;

class IncognitoController extends BaseController {
    public function index(): View {
        $grid = $this->gridBuilder()
            ->createGrid(IncognitoGrid::class)
            ->setSource(new EloquentSource(
                User::query()->where('is_incognito', true),
            ));
        return $this->view('adm.incognito.home', [
            'grid' => $grid,
        ]);
    }
}
