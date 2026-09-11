<?php
namespace Tests\Legacy\LaravelIntegration;

use Boduch\Grid\Grid;
use Boduch\Grid\GridHelper;
use Collective\Html\FormBuilder;
use Collective\Html\HtmlBuilder;
use Illuminate\Contracts\Validation;
use Illuminate\Contracts\View;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel;

class GridTest extends TestCase
{
    use Laravel\Application;

    private View\Factory $view;
    private Request $request;
    private GridHelper $gridHelper;
    private Validation\Factory $validator;
    private HtmlBuilder $htmlBuilder;
    private FormBuilder $formBuilder;

    #[Before]
    public function initialize(): void
    {
        $this->view = $this->laravel->app['view'];
        $this->request = $this->laravel->app['request'];
        $this->request->setLaravelSession($this->laravel->app['session.store']);
        $this->validator = $this->laravel->app['validator'];
        $this->htmlBuilder = new HtmlBuilder($this->laravel->app['url'], $this->view);
        $this->formBuilder = new FormBuilder($this->htmlBuilder, $this->laravel->app['url'], $this->view, $this->request->session()->token());
        $this->gridHelper = new GridHelper($this->request, $this->validator, $this->view, $this->htmlBuilder, $this->formBuilder);
    }

    #[Test]
    public function addColumn()
    {
        $grid = new Grid($this->gridHelper);
        $grid->addColumn('name', ['title' => 'First name']);
        $grid->addColumn('sex', ['title' => 'Sex', 'sortable' => true]);

        $this->assertInstanceOf(\Boduch\Grid\Column::class, $grid->getColumns()['name']);
        $this->assertEquals('First name', $grid->getColumns()['name']->getTitle());

        $this->assertInstanceOf(\Boduch\Grid\Column::class, $grid->getColumns()['sex']);
        $this->assertEquals('Sex', $grid->getColumns()['sex']->getTitle());
        $this->assertTrue($grid->getColumns()['sex']->isSortable());
    }

    #[Test]
    public function addColumnWithoutTitle()
    {
        $grid = new Grid($this->gridHelper);
        $grid->addColumn('first_name');
        $grid->addColumn('sex');

        $this->assertEquals('First Name', $grid->getColumns()['first_name']->getTitle());
        $this->assertEquals('Sex', $grid->getColumns()['sex']->getTitle());
    }

    #[Test]
    public function addColumnWithDecorators()
    {
        $grid = new Grid($this->gridHelper);
        $grid->addColumn('name', [
            'title'      => 'First name',
            'clickable'  => function () {
                return '';
            },
            'decorators' => [
                new \Boduch\Grid\Decorators\Url(),
            ],
        ]);

        $column = $grid->getColumns()['name'];

        $this->assertEquals(2, count($column->getDecorators()));
        $this->assertInstanceOf(\Boduch\Grid\Decorators\DecoratorInterface::class, $column->getDecorators()[0]);
        $this->assertInstanceOf(\Boduch\Grid\Decorators\DecoratorInterface::class, $column->getDecorators()[1]);
    }

    #[Test]
    public function renderColumnWithDecorator()
    {
        $grid = $this->getSampleGrid();
        $rows = $grid->getRows();

        $this->assertInstanceOf(\Boduch\Grid\Rows::class, $rows);
        $this->assertInstanceOf(\Boduch\Grid\Row::class, $rows[0]);

        $this->assertEquals("<a href=\"http://4programmers.net\">http://4programmers.net</a>", (string)$rows[0]->getValue('website'));
        $this->assertEquals("<a href=\"http://4programmers.net\">1</a>", (string)$rows[0]->getValue('id'));
    }

    #[Test]
    public function buildGridWithEachCallbackAndModifyColumnValue()
    {
        $grid = $this->getSampleGrid();
        $grid->after(function (\Boduch\Grid\Row $row) {
            $row->get('website')->setValue('');
        });

        $rows = $grid->getRows();

        $this->assertEquals('', (string)$rows[0]->getValue('website'));
    }

    #[Test]
    public function buildGridAndAddRowClass()
    {
        $grid = $this->getSampleGrid();
        $grid->after(function (\Boduch\Grid\Row $row) {
            $row->class = 'foo';
        });

        $rows = $grid->getRows();

        $this->assertEquals('foo', (string)$rows[0]->class);
    }

    #[Test]
    public function autoescapeCellOutput()
    {
        $grid = new Grid($this->gridHelper);
        $grid->addColumn('xss', [
            'title' => 'xss',
        ]);

        $collection = collect([
            ['xss' => '<xss>'],
        ]);

        $source = new \Boduch\Grid\Source\CollectionSource($collection);
        $grid->setSource($source);

        $rows = $grid->getRows();

        $this->assertEquals('&lt;xss&gt;', (string)$rows[0]->getValue('xss'));
    }

    #[Test]
    public function autoescapeCellOutputWithStrLimitDecorator()
    {
        $grid = new Grid($this->gridHelper);
        $grid->addColumn('xss', [
            'title'      => 'xss',
            'decorators' => [new \Boduch\Grid\Decorators\LongText()],
        ]);

        $collection = collect([
            ['xss' => '<xss>'],
        ]);

        $source = new \Boduch\Grid\Source\CollectionSource($collection);
        $grid->setSource($source);

        $rows = $grid->getRows();

        $this->assertEquals('&lt;xss&gt;', (string)$rows[0]->getValue('xss'));
    }

    #[Test]
    public function disableAutoescape()
    {
        $grid = $this->getSampleGrid(collect([
            ['id' => 1, 'name' => '<b>1</b>', 'website' => 'http://4programmers.net'],
        ]));

        $grid->getColumn('name')->setAutoescape(false);

        $rows = $grid->getRows();

        $this->assertEquals('<b>1</b>', (string)$rows[0]->getValue('name'));
    }

    private function getSampleGrid($collection = null)
    {
        if (empty($collection)) {
            $collection = collect([
                ['id' => 1, 'website' => 'http://4programmers.net'],
            ]);
        }

        $grid = new Grid($this->gridHelper);
        $grid->addColumn('website', [
            'title'      => 'Website',
            'decorators' => [
                new \Boduch\Grid\Decorators\Url(),
            ],
        ]);
        $grid->addColumn('name', [
            'title' => 'Name',
        ]);
        $grid->addColumn('id', [
            'title'     => 'ID',
            'clickable' => function ($row) {
                return '<a href="http://4programmers.net">' . $row['id'] . '</a>';
            },
        ]);

        $source = new \Boduch\Grid\Source\CollectionSource($collection);
        $grid->setSource($source);

        return $grid;
    }
}
