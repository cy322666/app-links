<?php

namespace App\Orchid\Screens;

use App\Orchid\Layouts\Examples\ChartBarExample;
use App\Orchid\Layouts\Examples\ChartLineExample;
use App\Orchid\Layouts\Examples\ChartPieExample;
use App\Services\Reports\ReportHelper;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Platform\Models\User;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
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

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $arrayDates = ReportHelper::getDatesByRequest($request);

        $this->countDiffDays = $arrayDates['countDays'];

        $actionsAllPaginate = Action::orderBy('date')->paginate(30);

        $actionsAll = Action::all();

        $actionsFilterAll = Action::query()
            ->whereBetween('date', [
                $arrayDates['dateAt']->format('Y-m-d'),
                $arrayDates['dateTo']->format('Y-m-d'),
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

        $arraySumCampaign = ReportHelper::sumByCollection($actionsFilterAll->groupBy('campaign_id'));

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

            'table'   => [
                new Repository(['id' => 100, 'name' => self::TEXT_EXAMPLE, 'price' => 10.24, 'created_at' => '01.01.2020']),
                new Repository(['id' => 200, 'name' => self::TEXT_EXAMPLE, 'price' => 65.9, 'created_at' => '01.01.2020']),
                new Repository(['id' => 300, 'name' => self::TEXT_EXAMPLE, 'price' => 754.2, 'created_at' => '01.01.2020']),
                new Repository(['id' => 400, 'name' => self::TEXT_EXAMPLE, 'price' => 0.1, 'created_at' => '01.01.2020']),
                new Repository(['id' => 500, 'name' => self::TEXT_EXAMPLE, 'price' => 0.15, 'created_at' => '01.01.2020']),

            ],
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
                    //  'diff' => -30.76
                ],

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
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [];
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
//                ChartPercentageExample::class,

            Layout::columns([
                ChartLineExample::class,
                ChartBarExample::class,
            ]),

            Layout::columns([

                ChartPieExample::class,

                Layout::rows([
                    Group::make([
                        DateRange::make('filterDates')
                            ->title('Фильтр по дате'),
                    ]),
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

            Layout::table('actions', [
                TD::make('type'),
                TD::make('transition_type'),
                TD::make('date'),
                TD::make('os'),
                TD::make('country'),
                TD::make('cost'),
            ]),
        ];
        //TODO тут таблица с действиями
    }

    public function filter(Request $request)
    {
        return redirect('admin/reports?date_at='.$request->filterDates['start'].'&date_to='.$request->filterDates['end']);
    }

    public function reset()
    {
        return redirect('admin/reports');
    }

    /**
     * @param Request $request
     */
    public function showToast(Request $request): void
    {
        Toast::warning($request->get('toast', 'Hello, world! This is a toast message.'));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export()
    {
        return response()->streamDownload(function () {
            $csv = tap(fopen('php://output', 'wb'), function ($csv) {
                fputcsv($csv, ['header:col1', 'header:col2', 'header:col3']);
            });

            collect([
                ['row1:col1', 'row1:col2', 'row1:col3'],
                ['row2:col1', 'row2:col2', 'row2:col3'],
                ['row3:col1', 'row3:col2', 'row3:col3'],
            ])->each(function (array $row) use ($csv) {
                fputcsv($csv, $row);
            });

            return tap($csv, function ($csv) {
                fclose($csv);
            });
        }, 'File-name.csv');
    }
}
