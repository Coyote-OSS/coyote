<?php
namespace Coyote\Http\Controllers\Adm;

use Carbon\Carbon;
use Coyote\Domain\Registration\ChartSource;
use Coyote\Domain\Registration\HistoryRange;
use Coyote\Domain\Registration\JobsCreated;
use Coyote\Domain\Registration\Period;
use Coyote\Domain\Registration\PostsCreated;
use Coyote\Domain\Registration\UserActivity;
use Coyote\Domain\Registration\UserRegistrations;
use Coyote\Domain\StringHtml;
use Coyote\Domain\View\Chart;
use Illuminate\View\View;

class StatisticsController extends BaseController {
    public function index(
        UserRegistrations $userRegistrations,
        PostsCreated      $postCreated,
        JobsCreated       $jobsCreated,
        UserActivity      $activity,
    ): View {
        $this->breadcrumb->push('Statystyki', route('adm.statistics'));
        return $this->view('adm.statistics', [
            'registrationsChartWeeks'  => $this->historyChartHtml($userRegistrations, Period::Week),
            'registrationsChartMonths' => $this->historyChartHtml($userRegistrations, Period::Month),
            'registrationsChartYears'  => $this->historyChartHtml($userRegistrations, Period::Year),

            'postsCreatedChartDays'   => $this->historyChartHtml($postCreated, Period::Day),
            'postsCreatedChartWeeks'  => $this->historyChartHtml($postCreated, Period::Week),
            'postsCreatedChartMonths' => $this->historyChartHtml($postCreated, Period::Month),
            'postsCreatedChartYears'  => $this->historyChartHtml($postCreated, Period::Year),

            'jobsCreatedChartDays'   => $this->historyChartHtml($jobsCreated, Period::Day),
            'jobsCreatedChartWeeks'  => $this->historyChartHtml($jobsCreated, Period::Week),
            'jobsCreatedChartMonths' => $this->historyChartHtml($jobsCreated, Period::Month),
            'jobsCreatedChartYears'  => $this->historyChartHtml($jobsCreated, Period::Year),

            'activityChartDays'   => $this->historyChartHtml($activity, Period::Day),
            'activityChartWeeks'  => $this->historyChartHtml($activity, Period::Week),
            'activityChartMonths' => $this->historyChartHtml($activity, Period::Month),
            'activityChartYears'  => $this->historyChartHtml($activity, Period::Year),
        ]);
    }

    private function historyChartHtml(ChartSource $source, Period $period): StringHtml {
        return new StringHtml($this->view('adm.registrations-chart', [
            'chart'              => $this->registrationsChart($source, $period),
            'chartLibrarySource' => Chart::librarySourceHtml(),
            'title'              => $source->title(),
        ]));
    }

    private function registrationsChart(ChartSource $source, Period $period): Chart {
        $range = new HistoryRange($this->dateNow(), $period, 30);
        return $this->chart(
            "$period->name.{$source->id()}",
            $source->inRange($range),
        );
    }

    private function dateNow(): string {
        return Carbon::now()->toDateString();
    }

    private function chart(string $chartId, array $registeredUsers): Chart {
        return new Chart(
            \array_keys($registeredUsers),
            \array_values($registeredUsers),
            ['#ff9f40'],
            "registration-history-chart-$chartId",
        );
    }
}
