<?php

namespace App\Orchid\Screens;

use App\Orchid\Layouts\ChartZoneCostClickLayout;
use App\Orchid\Layouts\ChartZoneCostInstallLayout;
use App\Orchid\Layouts\ChartZoneInstallLayout;
use App\Orchid\Layouts\ChartZoneNameLayout;
use App\Services\Reports\FilterRequest;
use App\Services\Reports\ReportHelper;
use App\Services\Reports\ZoneIdStrategy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
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

    public function query(\App\Models\Api\Action $action): iterable
    {
        $actionsAllRaw = \App\Models\Api\Action::query()
            ->where('campaign_id', $action->campaign_id);

        $actionsAll = $actionsAllRaw->get();

        $arraySumZoneClick = ReportHelper::sumByCollection(
            $actionsAll
                ->groupBy('zone_id')
                ->sortBy('sum')
                ->slice(0,6),
        );
        $arraySumZoneInstall = ReportHelper::sumByCollection(
            $actionsAll
                ->where('is_install', true)
                ->groupBy('zone_type')
                ->sortBy('sum')
                ->slice(0,6)
        );
        //TODO стоимость всех кликов делить на количество
        $arrayCostZoneClick = ReportHelper::costByCollection(
            $actionsAll
                ->groupBy('zone_type')
                ->sortBy('sum')
                ->slice(0, 6)
        );
        $arrayCostZoneInstall = ReportHelper::costByCollection(
            $actionsAll
                ->where('is_install', true)
                ->groupBy('zone_type')
                ->sortBy('sum')
                ->slice(0, 6)
        );

        $this->reportData = ReportHelper::reportBuild( 'campaign_id|'.$action->campaign_id, [
            'dateAt'    => Carbon::parse('2021-01-01'),
            'dateTo'    => Carbon::now(),
            'countDays' => 1,
        ]);

        $this->reportDataZoneId = (new ZoneIdStrategy([
            'dateAt'    => Carbon::parse('2021-01-01'),
            'dateTo'    => Carbon::now(),
            'countDays' => 1,
        ]))->build();

        $this->reportColumns = $this->reportData['columns'];
        $this->reportColumnsZoneId = $this->reportDataZoneId['columns'];

        unset($this->reportData['columns']);
        unset($this->reportDataZoneId['columns']);

        return [
            'actions' => $actionsAllRaw
                ->orderBy('updated_at', 'DESC')
                ->paginate(30),

            'zoneType' => $this->reportData,

            'zoneId' => $this->reportDataZoneId,

            'zoneName' => [
                [
                    "values" => array_values($arraySumZoneClick),
                    "labels" => array_keys($arraySumZoneClick),
                ],
            ],
            'zoneInstall' => [
                [
                    "values" => array_values($arraySumZoneInstall),
                    "labels" => array_keys($arraySumZoneInstall),
                ]
            ],
            'zoneCostClick' => [
                [
                    "values" => array_values($arrayCostZoneClick),
                    "labels" => array_keys($arrayCostZoneClick),
                ],
            ],
            'zoneCostInstall' => [
                [
                    "values" => array_values($arrayCostZoneInstall),
                    "labels" => array_keys($arrayCostZoneInstall),
                ]
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

            Layout::columns([
                ChartZoneNameLayout::class,
                ChartZoneInstallLayout::class,
            ]),

            Layout::columns([
                ChartZoneCostClickLayout::class,
                ChartZoneCostInstallLayout::class,
            ]),

            Layout::table(
                'zoneType',
                ReportHelper::prepareColumns(
                    $this->reportColumns,
                    'campaign_id',
                    $this->reportData,
                    new FilterRequest(),
                )
            )->title('Отчет Zone type'),

            Layout::table(
                'zoneId',
                ReportHelper::prepareColumns(
                    $this->reportColumnsZoneId,
                    'campaign_id',
                    $this->reportDataZoneId,
                    new FilterRequest(),
                )
            )->title('Отчет Zone ID'),

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
