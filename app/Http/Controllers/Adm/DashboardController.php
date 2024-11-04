<?php
namespace Coyote\Http\Controllers\Adm;

use Carbon\Carbon;
use Coyote\Domain\Registration\ChartSource;
use Coyote\Domain\Registration\HistoryRange;
use Coyote\Domain\Registration\Period;
use Coyote\Domain\Registration\PostsCreated;
use Coyote\Domain\Registration\UserRegistrations;
use Coyote\Domain\StringHtml;
use Coyote\Domain\View\Chart;
use Illuminate\Foundation\Application;
use Illuminate\View\View;

class DashboardController extends BaseController
{
    public function index(UserRegistrations $userRegistrations, PostsCreated $postCreated): View
    {
        return $this->view('adm.dashboard', [
            'checklist' => [
                $this->directoryWritable('storage/', \storage_path()),
                $this->directoryWritable('uploads/', \public_path()),
                [
                    'label' => 'Redis włączony',
                    'value' => \config('cache.default'),
                ],
                [
                    'label' => new StringHtml('PHP - <code>' . \PHP_VERSION . '</code>'),
                    'value' => true,
                ],
                [
                    'label' => new StringHtml('Laravel - <code>' . Application::VERSION . '</code>'),
                    'value' => true,
                ],
            ],

            'registrationsChartWeeks'  => $this->historyChartHtml($userRegistrations, Period::Week),
            'registrationsChartMonths' => $this->historyChartHtml($userRegistrations, Period::Month),
            'registrationsChartYears'  => $this->historyChartHtml($userRegistrations, Period::Year),

            'postsCreatedChartDays'   => $this->historyChartHtml($postCreated, Period::Day),
            'postsCreatedChartWeeks'  => $this->historyChartHtml($postCreated, Period::Week),
            'postsCreatedChartMonths' => $this->historyChartHtml($postCreated, Period::Month),
            'postsCreatedChartYears'  => $this->historyChartHtml($postCreated, Period::Year),
        ]);
    }

    private function historyChartHtml(ChartSource $source, Period $period): StringHtml
    {
        return new StringHtml($this->view('adm.registrations-chart', [
            'chart'              => $this->registrationsChart($source, $period),
            'chartLibrarySource' => Chart::librarySourceHtml(),
            'title'              => $source->title(),
        ]));
    }

    private function registrationsChart(ChartSource $source, Period $period): Chart
    {
        $range = new HistoryRange($this->dateNow(), $period, 30);
        return $this->chart(
            "$period->name.{$source->id()}",
            $source->inRange($range),
        );
    }

    private function dateNow(): string
    {
        return Carbon::now()->toDateString();
    }

    private function chart(string $chartId, array $registeredUsers): Chart
    {
        return new Chart(
            \array_keys($registeredUsers),
            \array_values($registeredUsers),
            ['#ff9f40'],
            "registration-history-chart-$chartId",
        );
    }

    public function directoryWritable(string $basePath, string $path): array
    {
        $permission = \decOct(\filePerms($path) & 0777);
        return [
            'label' => new StringHtml("Katalog <code>$basePath</code> ma prawa do zapisu - <code>$permission</code>"),
            'value' => \is_writeable(\storage_path()),
        ];
    }
}
