<?php

namespace App\Orchid\Screens;

use App\Models\Api\App;
use App\Models\Api\Link;
use App\Orchid\Layouts\ChartZoneCostClickLayout;
use App\Orchid\Layouts\ChartZoneCostInstallLayout;
use App\Orchid\Layouts\ChartZoneInstallLayout;
use App\Orchid\Layouts\ChartZoneNameLayout;
use App\Orchid\Layouts\Examples\ChartBarExample;
use App\Orchid\Layouts\Examples\ChartLineExample;
use App\Orchid\Layouts\Examples\ChartPercentageZoneId;
use App\Orchid\Layouts\Examples\ChartPercentageZoneName;
use App\Services\Reports\ReportHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Platform\Models\Role;
use Orchid\Platform\Models\User;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Map;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\RadioButtons;
use Orchid\Screen\Fields\Range;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\UTM;
use Orchid\Screen\Layouts\Legend;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Ramsey\Uuid\Uuid;

class CampaignScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Api\Action $action): iterable
    {
        $actionsAllRaw = \App\Models\Api\Action::query()
            ->where('campaign_id', $action->campaign_id);

        $actionsAll = $actionsAllRaw->get();

        $arraySumZoneClick = ReportHelper::sumByCollection(
            $actionsAll
                ->groupBy('zone_id')
                ->sortBy('sum'),
        );
        $arraySumZoneInstall = ReportHelper::sumByCollection(
            $actionsAll
                ->where('is_install', true)
                ->groupBy('zone_type')
                ->sortBy('sum')
        );
        //стоимость всех кликов делить на количество
        $arrayCostZoneClick = ReportHelper::costByCollection(
            $actionsAll
                ->groupBy('zone_type')
                ->sortBy('sum'),
        );
        $arrayCostZoneInstall = ReportHelper::costByCollection(
            $actionsAll
                ->where('is_install', true)
                ->groupBy('zone_type')
                ->sortBy('sum')
        );

        return [
            'actions' => $actionsAllRaw->paginate(30),

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
