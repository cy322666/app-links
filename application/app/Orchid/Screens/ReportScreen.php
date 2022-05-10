<?php

namespace App\Orchid\Screens;

use App\Orchid\Layouts\Examples\ChartBarExample;
use App\Orchid\Layouts\Examples\ChartLineExample;
use App\Orchid\Layouts\Examples\ChartPercentageExample;
use App\Orchid\Layouts\Examples\ChartPercentageZoneId;
use App\Orchid\Layouts\Examples\ChartPercentageZoneName;
use App\Orchid\Layouts\Examples\ChartPieExample;
use App\Services\Reports\FilterRequest;
use App\Services\Reports\ReportHelper;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;
use Orchid\Platform\Models\User;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use App\Models\Api\Action;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ReportScreen extends Screen
{
    /**
     * Fish text for the table.
     */
    public const TEXT_EXAMPLE = '';

    private int $countDiffDays;

    private array $reportColumns;

    private array $reportData;

    public FilterRequest $request;

    private string $targetReport = 'campaign';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $this->targetReport = $request->target_report ?? $this->targetReport;

        $this->request = (new FilterRequest())->getDatesByRequest($request);

        $this->countDiffDays = $this->request->countDays;

        $actionsAllPaginate = Action::orderBy('updated_at', 'DESC')->paginate(30);

        $actionsAll = Action::all();

        $actionsFilterAll = Action::query()
            ->whereBetween('date', [
                $this->request->dateAt->format('Y-m-d'),
                $this->request->dateTo->format('Y-m-d'),
            ])
            ->get();

        $arrayDates = ReportHelper::getArrayDates($this->countDiffDays);

        $actionsTransitionArray = ReportHelper::getArrayByCollection($actionsFilterAll, 'type', 'transition')
            ->groupBy('date')
            ->toArray();

        $actionsAndroidArray = ReportHelper::getArrayByCollection($actionsFilterAll, 'os', 'android')
            ->groupBy('date')
            ->toArray();

        $actionsOsArray = ReportHelper::getArrayByCollection($actionsFilterAll, 'os','ios')
            ->groupBy('date')
            ->toArray();

        $actionsInstallArray = ReportHelper::getArrayByCollection($actionsFilterAll, 'is_install', true)
            ->groupBy('date')
            ->toArray();

        $arrayAndroid = ReportHelper::prepareArray($arrayDates, $actionsAndroidArray);

        $arrayTransition = ReportHelper::prepareArray($arrayDates, $actionsTransitionArray);

        $arrayOs = ReportHelper::prepareArray($arrayDates, $actionsOsArray);

        $arrayInstall = ReportHelper::prepareArray($arrayDates, $actionsInstallArray);

        $arraySumCampaign = ReportHelper::sumByCollection($actionsFilterAll->groupBy('campaign_id')->sortBy('sum')->slice(0, 9));

//        $arraySumZone = ReportHelper::sumByCollection($actionsFilterAll->groupBy('zone_type')->sortBy('sum')->slice(0, 10));

//        $arraySumZoneId = ReportHelper::sumByCollection($actionsFilterAll->groupBy('zone_id')->sortBy('sum')->slice(0, 10));

        $this->reportData = ReportHelper::reportBuild($request->target_report ?? null, [
            'dateAt'    => $this->request->dateAt,
            'dateTo'    => $this->request->dateTo,
            'countDays' => $this->request->countDays,
        ]);

        $this->reportColumns = $this->reportData['columns'];

        unset($this->reportData['columns']);

        return [
            'actions' => $actionsAllPaginate,

            'chartsActionsType'  => [
                [
                    'name'   => 'Переходы',
                    'values' => array_values(array_map(function ($arrayDate) {

                        return count($arrayDate);

                    }, $arrayTransition)),

                    'labels' => $arrayDates,
                ],
                [
                    'name'   => 'Установки',
                    'values' => array_values(array_map(function ($arrayDate) {

                        return count($arrayDate);

                    }, $arrayInstall)),

                    'labels' => $arrayDates,
                ],
            ],
            'chartsOS'  => [
                [
                    'name'   => 'Android',
                    'values' => array_values(array_map(function ($arrayDate) {

                        return count($arrayDate);

                    }, $arrayAndroid)),

                    'labels' => $arrayDates,
                ],
                [
                    'name'   => 'iOS',
                    'values' => array_values(array_map(function ($arrayDate) {

                        return count($arrayDate);

                    }, $arrayOs)),

                    'labels' => $arrayDates,
                ],
            ],
            'chartsTransitionType' => [
                [
                    "name" => "Кампании",

                    "values" => array_values($arraySumCampaign),
                    "labels" => array_keys($arraySumCampaign),
                ]

            ],
//            'zone' => [
//                [
//                    "name" => "Регионы",
//
//                    "values" => array_values($arraySumZone),
//                    "labels" => array_keys($arraySumZone),
//                ]
//            ],

//            'zoneId' => [
//                [
//                    "name" => "Регионы",
//
//                    "values" => array_values($arraySumZoneId),
//                    "labels" => array_keys($arraySumZoneId),
//                ]
//            ],

            //по кампаниям, по названию, по системе и в разрезе дат

            //по странам: country | costs | count_transition | count_install | ctr | prelanding | direct | android | ios

            //по кампаниям: name | costs | count_transition | count_install | ctr | prelanding | direct | android | ios

            //по системе: os | costs | count_transition | count_install | ctr | prelanding | direct

            'reports' => $this->reportData,
            'metrics' => [
                'install_today' => ['value' => number_format(
                    $actionsAll
                        ->where('is_install', true)
                        ->where('install_at', Carbon::now()->format('Y-m-d'))
                        ->count()),
                   // 'diff' => 10.08
                ],

                'install_all'   => ['value' => number_format(
                    $actionsAll
                        ->where('is_install', true)
                        ->count()),
                  //  'diff' => -30.76
                ],

                'transition_today' => ['value' => number_format(
                    $actionsAll
                        ->where('type', 'transition')
                        ->where('date', Carbon::now()->format('Y-m-d'))
                        ->count()),
                    //'diff' => 0
                ],

                'transition_all' => ['value' => number_format(
                    $actionsAll->count()
                )],

                'transition_filtered' => ['value' => number_format(
                    $actionsFilterAll->count()
                )],

                'install_filtered' => ['value' => number_format(
                    $actionsFilterAll
                        ->where('is_install', true)
                        ->count()
                    )
                ],

                'spent_all' => ['value' => number_format(
                    $actionsAll
                        ->sum(function ($action) {

                            return $action->cost;
                    })
                )],

                'spent_filtered' => ['value' => number_format(
                    $actionsFilterAll
                        ->sum(function ($action) {

                            return $action->cost;
                        })
                )],

                'spent_today' => ['value' => number_format(
                    $actionsAll
                        ->where('date', Carbon::now()->format('Y-m-d'))
                        ->sum(function ($action) {

                            return $action->cost;
                    })
                )],

                'prelanding_today' => ['value' => number_format(
                    $actionsFilterAll
                        ->where('transition_type', 'prelanding')
                        ->where('date', Carbon::now()->format('Y-m-d'))
                        ->count()
                )],
                'prelanding_all' => ['value' => number_format(
                    $actionsAll
                        ->where('transition_type', 'prelanding')
                        ->count()
                )],

                'direct_today' => ['value' => number_format(
                    $actionsAll
                        ->where('transition_type', 'direct')
                        ->where('date', Carbon::now()->format('Y-m-d'))
                        ->count()
                )],
                'direct_all' => ['value' => number_format(
                    $actionsAll
                        ->where('transition_type', 'direct')
                        ->count()
                )],
            ],
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Отчеты';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Страница для просмотра аналитики';
    }

    /**
     * Views.
     *
     * @return iterable
     */
    public function layout(): iterable
    {

        return [
            Layout::columns([
                Layout::metrics([
                    'Установок сегодня' => 'metrics.install_today',
                    'Установок всего'   => 'metrics.install_all',
                    'Переходов сегодня' => 'metrics.transition_today',
                    'Переходов всего'   => 'metrics.transition_all',
                ]),
            ]),

            Layout::columns([
                Layout::metrics([
                    'Прелендов сегодня' => 'metrics.prelanding_today',
                    'Прелендов всего'   => 'metrics.prelanding_all',
                    'Прямых сегодня' => 'metrics.direct_today',
                    'Прямых всего'   => 'metrics.direct_all',
                ]),
            ]),

            Layout::columns([
                Layout::metrics([
                    'Установок за ('.$this->countDiffDays.') дней' => 'metrics.install_filtered',
                    'Прямых переходов за ('.$this->countDiffDays.') дней' => 'metrics.install_all',
                    'Переходов за ('.$this->countDiffDays.') дней' => 'metrics.transition_filtered',
                    'Прелендов за ('.$this->countDiffDays.') дней' => 'metrics.transition_all',
                ]),
            ]),

            Layout::columns([
                Layout::metrics([
                    'Потрачено всего'   => 'metrics.spent_all',
                    'Потрачено сегодня' => 'metrics.spent_today',
                    'Потрачено за ('.$this->countDiffDays.') дней' => 'metrics.spent_filtered',
                    'Потрачено в среднем' => 'metrics.spent_today',//TODO
                ]),
            ]),

            Layout::columns([
                ChartLineExample::class,
                ChartBarExample::class,
            ]),

            Layout::columns([

                ChartPieExample::class,

                Layout::rows([
                    Group::make([
                        Select::make('targetReport')
                            ->options([
                                'campaign' => 'Кампании',
                                'os'       => 'Платформы',
                                'country'  => 'Страны',
                                'name'     => 'Ссылки',
                                'zone_type'=> 'Регионы',
                                'zone_id'  => 'ID региона',
                            ])
                            ->value($this->targetReport)
                            ->popover('Выберите параметр для составления отчета')
                            ->title('Вид отчета'),
                    ]),

                    DateRange::make('filterDates')
                        ->title('Фильтр по дате'),

                        Group::make([

                            Button::make('Применить')
                                ->method('filter')
                                ->type(Color::DARK()),

                            Button::make('Сбросить')
                            ->method('reset')
                            ->type(Color::LIGHT()),

                    ])->autoWidth(),
                ]),
            ]),

            Layout::table(
                'reports',
                ReportHelper::prepareColumns(
                    $this->reportColumns,
                    $this->targetReport,
                    $this->reportData,
                    $this->request,
                )
            )->title('Отчеты по параметрам'),

            Layout::table('actions', [
                TD::make('transition_type', 'Тип перехода'),
                TD::make('date', 'Дата перехода'),
                TD::make('campaign_id', 'ID кампании'),
                TD::make('os', 'ОС'),
                TD::make('country', 'Страна'),
                TD::make('is_install', 'Установка'),
                TD::make('install_at', 'Дата установки'),
                TD::make('cost', 'Стоимость'),
            ])->title('Список событий'),
        ];
    }

    public function filter(Request $request): Redirector|RedirectResponse|Application
    {
        return redirect((new FilterRequest())->getDataBySave($request)->filter());
    }

    public function reset(): Redirector|Application|RedirectResponse
    {
        return redirect('admin/reports');
    }
}
