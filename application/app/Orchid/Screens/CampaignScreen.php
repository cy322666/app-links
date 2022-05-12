<?php

namespace App\Orchid\Screens;

use App\Models\Api\Action;
use App\Orchid\Layouts\ChartZoneCostClickLayout;
use App\Orchid\Layouts\ChartZoneCostInstallLayout;
use App\Orchid\Layouts\ChartZoneInstallLayout;
use App\Orchid\Layouts\ChartZoneNameLayout;
use App\Services\Reports\FilterRequest;
use App\Services\Reports\ReportHelper;
use App\Services\Reports\ZoneIdStrategy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class CampaignScreen extends Screen
{
    private int $countDiffDays;

    private array $reportColumns;
    private array $reportColumnsZoneId;

    private array $reportData;
    private array $reportDataZoneId;

    public function query(Action $action, Request $request): iterable
    {
//        $actionsAllRaw = Action::query()
//            ->where('campaign_id', $action->campaign_id)
//            ->get();

//        $costAll = $actionsAllRaw->sum('cost');

//        $costCount = $actionsAllRaw->count();

//        $actionZoneIdRaw = $actionsAllRaw->groupBy('zone_id');
//
//        $arraySumZoneClick = ReportHelper::sumByCollection(
//            $actionZoneIdRaw
//                ->sortBy('sum')
//                ->slice(0,6),
//        );
//        $arraySumZoneInstall = ReportHelper::sumByCollection(
//            $actionZoneIdRaw
//                ->where('is_install', true)
//                ->sortBy('sum')
//                ->slice(0,6)
//        );

//        dd(Action::query()
//            ->where('campaign_id', $action->campaign_id)
//            ->get()
//            ->groupBy('zone_type'));

        $actionsRaw = DB::table('actions')
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
            ->where('campaign_id', $action->campaign_id)
            ->get();

        return [
            'actions' => Action::query()
                ->where('campaign_id', $action->campaign_id)
                ->orderBy('updated_at', 'desc')
                ->limit(15)
                ->get(),

            'zoneType' => ReportHelper::getReportZone($actionsRaw->groupBy('zone_type')),

            'zoneId' => ReportHelper::getReportZone($actionsRaw->groupBy('zone_id')),

//            'zoneName' => [
//                [
//                    "values" => array_values($arraySumZoneClick),
//                    "labels" => array_keys($arraySumZoneClick),
//                ],
//            ],
//            'zoneInstall' => [
//                [
//                    "values" => array_values($arraySumZoneInstall),
//                    "labels" => array_keys($arraySumZoneInstall),
//                ]
//            ],
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Детали кампании';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Страница просмотра кампании';
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
     * @throws \Throwable
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [

//            Layout::columns([
//                ChartZoneCostClickLayout::class,
//                ChartZoneCostInstallLayout::class,
//            ]),

            Layout::table('zoneType', [
                TD::make('type', 'Тип'),
                TD::make('costs_all','Общ ст.'),
                TD::make('costs_install','Общ. ст. установок'),
                TD::make('count_transition','Кол-во переходов'),
                TD::make('count_install','Кол-во установок'),
                TD::make('avg_cost_transition','Ср. ст. клика'),
                TD::make('avg_cost_install','Ср. ст. установки'),
                TD::make('cr'),
                TD::make('count_prelanding','Кол-во прелендов'),
                TD::make('count_direct','Кол-во прямых'),
                TD::make('count_android','Кол-во Android'),
                TD::make('count_ios','Кол-во iOS'),
            ])->title('Отчет Zone type'),

            Layout::table('zoneId', [
                TD::make('type', 'Тип'),
                TD::make('costs_all','Общ ст.')->sort(),
                TD::make('costs_install','Общ. ст. установок'),
                TD::make('count_transition','Кол-во переходов'),
                TD::make('count_install','Кол-во установок'),
                TD::make('avg_cost_transition','Ср. ст. клика'),
                TD::make('avg_cost_install','Ср. ст. установки'),
                TD::make('cr'),
                TD::make('count_prelanding','Кол-во прелендов'),
                TD::make('count_direct','Кол-во прямых'),
                TD::make('count_android','Кол-во Android'),
                TD::make('count_ios','Кол-во iOS'),
            ])->title('Отчет Zone ID'),

            Layout::table('actions', [
                TD::make('transition_type', 'Тип перехода'),
                TD::make('date', 'Дата перехода'),
                TD::make('os', 'ОС'),
                TD::make('country', 'Страна'),
                TD::make('is_install', 'Установка'),
                TD::make('install_at', 'Дата установки'),
                TD::make('cost', 'Стоимость'),
            ])->title('События кампании'),
        ];
    }

    public function save(Request $request)
    {
    }
}
