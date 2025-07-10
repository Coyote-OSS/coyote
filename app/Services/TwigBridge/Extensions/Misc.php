<?php
namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Domain\Clock;
use Coyote\Services\Declination;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Misc extends AbstractExtension
{
    public function __construct(private Clock $clock)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('timer', [$this, 'totalRuntime']),
            new TwigFunction('github', [$this, 'githubAccountName']),
            new TwigFunction('declination', [Declination::class, 'format']),
            new TwigFunction('sortable', [$this, 'sortable'], ['is_safe' => ['html']]),
            new TwigFunction('is_url', [$this, 'isUrl'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('encrypt', function ($data) {
                return app('encrypter')->encrypt($data);
            }),
            // uzywane w szablonie home.twig do poprawnego wyswietlania title w sekcji "ostatnie zmiany na forum"
            new TwigFilter('unescape', function ($value) {
                return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
            }),
        ];
    }

    public function totalRuntime(): string
    {
        $timer = $this->clock->executionTimeAsSeconds();
        if ($timer < 1) {
            return (int)substr((string)$timer, 2, 3) . ' ms';
        }
        return number_format($timer, 2) . ' s';
    }

    /**
     * @param string $url
     * @return string
     */
    public function githubAccountName($url)
    {
        if (!$url) {
            return '';
        }
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return '';
        }
        return trim($path, '/');
    }

    public function sortable(): string
    {
        $args = func_get_args();

        $column = array_shift($args);
        $title = array_shift($args);
        $default = array_shift($args);

        $sort = request('sort', $default[0]);
        $order = request('order', $default[1]);

        $parameters = array_merge(
            request()->all(),
            ['sort' => $column, 'order' => $order == 'desc' ? 'asc' : 'desc'],
        );

        return link_to(
            request()->path() . '?' . http_build_query($parameters),
            $title,
            ['class' => "sort " . ($sort == $column ? strtolower($order) : '')],
        );
    }

    /**
     * @param string $url
     * @return bool
     */
    public function isUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
}
