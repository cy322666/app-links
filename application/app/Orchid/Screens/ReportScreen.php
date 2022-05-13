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
use Illuminate\Support\Facades\DB;
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
    private int $countDays;
    private string $targetReport;
    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $dateAt = $request->input('date_at') ? Carbon::parse($request->input('date_at')) : Carbon::now()->subDays(6);
        $dateTo = $request->input('date_to') ? Carbon::parse($request->input('date_to')) : Carbon::now()->addDay();

        $this->targetReport = $request->targetReport ?? 'campaign_id';

        $this->countDays = $dateAt->diffInDays($dateTo);

        $actionsFilterAll = DB::table('actions')
            ->select([
                'created_at',
                'type',
                'os',
                'country',
                'cost',
                'date',
                'transition_type',
                'is_install',
                'campaign_id',
                'install_at',
                'zone_id',
                'zone_type',
            ])
            ->whereBetween('date', [
                $dateAt->format('Y-m-d'),
                $dateTo->format('Y-m-d'),
            ])
            ->get();

        $actionsTodayAll = DB::table('actions')
            ->select([
                'created_at',
                'type',
                'os',
                'country',
                'cost',
                'date',
                'transition_type',
                'is_install',
                'install_at',
                'zone_id',
                'zone_type',
            ])
            ->where('updated_at', Carbon::now()->format('Y-m-d'))
            ->get();

        $arrayDates = ReportHelper::getArrayDates($this->countDays);

        $actionsTransitionArray = $actionsFilterAll
            ->groupBy('date')
            ->toArray();

        $actionsInstallArray = $actionsFilterAll
            ->where('is_install', true)
            ->groupBy('date')
            ->toArray();

        $actionsAndroidArray = $actionsFilterAll
            ->where('os', 'android')
            ->groupBy('date')
            ->toArray();

        $actionsIOsArray = $actionsFilterAll
            ->where('os', 'ios')
            ->groupBy('date')
            ->toArray();

        return [

            'actions' => Action::query()
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get(),

            'chartsActionsType' => [
                [
                    'name'   => 'Переходы',
                    'values' => array_values(array_map(function ($arrayDate) {

                        return count($arrayDate);

                    }, ReportHelper::prepareArray($arrayDates, $actionsTransitionArray))),

                    'labels' => $arrayDates,
                ],
                [
                    'name'   => 'Установки',
                    'values' => array_values(array_map(function ($arrayDate) {

                        return count($arrayDate);

                    }, ReportHelper::prepareArray($arrayDates, $actionsInstallArray))),

                    'labels' => $arrayDates,
                ],
            ],
            'chartsOS' => [
                [
                    'name'   => 'Android',
                    'values' => array_values(array_map(function ($arrayDate) {

                        return count($arrayDate);

                    }, ReportHelper::prepareArray($arrayDates, $actionsAndroidArray))),

                    'labels' => $arrayDates,
                ],
                [
                    'name'   => 'iOS',
                    'values' => array_values(array_map(function ($arrayDate) {

                        return count($arrayDate);

                    }, ReportHelper::prepareArray($arrayDates, $actionsIOsArray))),

                    'labels' => $arrayDates,
                ],
            ],

            'reports' => ReportHelper::getReport($actionsFilterAll->groupBy($this->targetReport), $request->sort ?? 'count_install'),

            'metrics' => [
                'install_today' => ['value' => number_format(
                    $actionsTodayAll
                        ->where('is_install', true)
                        ->count()),
                ],

                'transition_today' => ['value' => number_format(
                    $actionsTodayAll
                        ->where('type', 'transition')
                        ->count()),
                ],

                'transition_filtered' => ['value' => number_format(
                    $actionsFilterAll->count()
                )],

                'direct_filtered' => ['value' => number_format(
                    $actionsFilterAll
                        ->where('transition_type', 'direct')
                        ->count()
                )],

                'prelanding_filtered' => ['value' => number_format(
                    $actionsFilterAll
                        ->where('transition_type', 'prelanding')
                        ->count()
                )],

                'install_filtered' => ['value' => number_format(
                    $actionsFilterAll
                        ->where('is_install', true)
                        ->count()
                    )
                ],

                'spent_filtered' => [
                    'value' => number_format($actionsFilterAll->sum('cost'))
                ],

                'spent_today' => ['value' => number_format(
                    $actionsTodayAll
                        ->sum('cost')
                )],

                'prelanding_today' => ['value' => number_format(
                    $actionsFilterAll
                        ->where('transition_type', 'prelanding')
                        ->count()
                )],

                'direct_today' => ['value' => number_format(
                    $actionsTodayAll
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
                    'Прямых сегодня'    => 'metrics.direct_today',
                    'Переходов сегодня' => 'metrics.transition_today',
                    'Прелендов сегодня' => 'metrics.prelanding_today',
                ]),
            ]),

            Layout::columns([
                Layout::metrics([
                    'Установок за ('.$this->countDays.') дней' => 'metrics.install_filtered',
                    'Прямых переходов за ('.$this->countDays.') дней' => 'metrics.direct_filtered',
                    'Переходов за ('.$this->countDays.') дней' => 'metrics.transition_filtered',
                    'Прелендов за ('.$this->countDays.') дней' => 'metrics.prelanding_filtered',
                ]),
            ]),

            Layout::columns([
                Layout::metrics([
                    'Потрачено сегодня' => 'metrics.spent_today',
                    'Потрачено за ('.$this->countDays.') дней' => 'metrics.spent_filtered',
                ]),
            ]),

            Layout::columns([
                ChartLineExample::class,
                ChartBarExample::class,
            ]),

            Layout::columns([

                ChartBarExample::class,

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

            Layout::table('reports', [
                TD::make('type', 'Название')->render(function ($action) {

                    $actionId = Action::query()->where('campaign_id', $action['type'])->first()->id;

                    return "<b><a href=".route('platform.campaign', $actionId).">{$action['type']}</a></b>";
                }),
                TD::make('costs_all','Общ ст.')->sort(),
                TD::make('costs_install','Общ. ст. установок')->sort(),
                TD::make('count_transition','Кол-во переходов')->sort(),
                TD::make('count_install','Кол-во установок')->sort(),
                TD::make('avg_cost_transition','Ср. ст. клика')->sort(),
                TD::make('avg_cost_install','Ср. ст. установки')->sort(),
                TD::make('cr')->sort(),
                TD::make('count_prelanding','Кол-во прелендов')->sort(),
                TD::make('count_direct','Кол-во прямых')->sort(),
                TD::make('count_android','Кол-во Android')->sort(),
                TD::make('count_ios','Кол-во iOS')->sort(),
            ])->title('Отчеты по параметрам'),

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
