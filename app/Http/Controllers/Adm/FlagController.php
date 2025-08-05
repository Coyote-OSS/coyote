<?php
namespace Coyote\Http\Controllers\Adm;

use Carbon\Carbon;
use Coyote\Domain\Administrator\AvatarCdn;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialRequest;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialStore;
use Coyote\Domain\Administrator\UserMaterial\List\View\MarkdownRender;
use Coyote\Domain\Administrator\UserMaterial\List\View\MaterialList;
use Coyote\Domain\Administrator\UserMaterial\List\View\Time;
use Coyote\Domain\View\Filter\SearchFilterFormat;
use Coyote\Domain\View\Pagination\BootstrapPagination;
use Illuminate\View\View;

class FlagController extends BaseController {
    public function index(MaterialStore $store, MarkdownRender $render): View {
        $this->breadcrumb->push('Dodane treści', route('adm.flag'));
        $paramFilterString = $this->queryOrNull('filter');
        $format = new SearchFilterFormat($paramFilterString ?? '');
        $filter = $format->toSearchFilter();
        $request = new MaterialRequest(
            \max(1, (int)$this->request->query('page', 1)),
            20,
            $filter);

        $materials = new MaterialList(
            $render,
            new Time(Carbon::now()),
            $store->fetch($request),
            new AvatarCdn());

        return $this->view('adm.flag.home', [
            'materials'        => $materials,
            'pagination'       => new BootstrapPagination($request->page, $request->pageSize, $materials->total(), ['filter' => $this->queryOrNull('filter')]),
            'filter'           => $filter->toString(),
            'availableFilters' => [
                'type:post', 'type:comment', 'type:microblog',
                'is:deleted', 'not:deleted',
                'is:reported', 'not:reported', 'report:open', 'report:closed',
                'author:{id}',
            ],
        ]);
    }

    private function queryOrNull(string $key): ?string {
        if ($this->request->query->has($key)) {
            return $this->request->query->get($key, '');
        }
        return null;
    }
}
