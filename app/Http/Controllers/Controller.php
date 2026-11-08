<?php
namespace Coyote\Http\Controllers;

use Boduch\Grid\GridBuilder;
use Coyote\Services\Breadcrumbs;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\FormBuilder;
use Coyote\Services\Guest;
use Coyote\User;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class Controller extends \Illuminate\Routing\Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected Breadcrumbs $breadcrumb;
    protected ?int $userId;
    protected ?User $auth;
    protected string $guestId;
    protected ?array $settings = null;
    protected Request $request;

    public function __construct()
    {
        $this->breadcrumb = new Breadcrumbs();
        $this->middleware(function (Request $request, $next) {
            $this->auth = $request->user();
            $this->userId = $request->user() ? $this->auth->id : null;
            $this->guestId = $request->session()->get('guest_id');
            $this->request = $request;
            return $next($request);
        });
    }

    /**
     * @param string|null $view
     * @param array $data
     * @return View
     */
    protected function view($view = null, $data = [])
    {
        if (!$this->request->ajax()) {
            $data['breadcrumbLegacyComponent'] = $this->breadcrumb->render();
            $data['breadcrumbs'] = $this->breadcrumb->breadcrumbsModel();
        }
        return view($view, $data);
    }

    /**
     * @param string $name
     * @param $value
     * @return string
     */
    protected function setSetting(string $name, $value)
    {
        /** @var Guest $guest */
        $guest = app(Guest::class);
        return $guest->setSetting($name, $value);
    }

    /**
     * Get user's settings as array (setting => value)
     *
     * @return array|null
     */
    protected function getSettings()
    {
        /** @var Guest $app */
        $app = app(Guest::class);
        $settings = $app->getSettings();
        return [
            'colorScheme'     => $settings['colorScheme'] ?? 'system',
            'lastColorScheme' => $settings['lastColorScheme'] ?? 'light',
            'topicMode'       => ($settings['homepage.mode'] ?? 0) ? 'newest' : 'interesting',
            'reputationMode'  => ['week', 'month', 'quarter'][$settings['homepage.reputation'] ?? 0],
            'membersDocked'   => ($settings['homepage.members'] ?? null) === 'docked',
        ];
    }

    protected function getSetting(string $name, $default = null)
    {
        /** @var Guest $guest */
        $guest = app(Guest::class);
        return $guest->getSetting($name, $default);
    }

    protected function createForm($formClass, $data = null, array $options = []): Form
    {
        /** @var FormBuilder $builder */
        $builder = app(FormBuilder::class);
        return $builder->createForm($formClass, $data, $options);
    }

    protected function gridBuilder(): GridBuilder
    {
        return app(GridBuilder::class);
    }

    protected function transaction(callable $callback)
    {
        /** @var Connection $connection */
        $connection = app(Connection::class);
        return $connection->transaction($callback);
    }
}
