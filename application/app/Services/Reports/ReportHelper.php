<?php

namespace App\Services\Reports;

use App\Models\Api\Link;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Orchid\Screen\Repository;
use Orchid\Screen\TD;

class ReportHelper
{
    public static function getArrayDates(int $countDays = 7): array
    {
        for ($i = $countDays; $i > 0; $i--) {

            $arrayDates[] = Carbon::now()->subDays($i)->format('Y-m-d');
        }

        return $arrayDates ?? [];
    }

    public static function getArrayByCollection(Collection|LengthAwarePaginator $collection, string $key, string $param): Collection
    {
        return $collection->filter(function ($model) use ($param, $key) {

            return $model->$key == $param;
        });
    }

    public static function prepareArray(array $arrayDates, array $actionsInstallArray): array
    {
        foreach (array_flip($arrayDates) as $date => $key) {

            $arrayInstall[$date] = $actionsInstallArray[$date] ?? [];
        }

        return $arrayInstall ?? [];
    }

    public static function sumByCollection(Collection|LengthAwarePaginator $collections): array
    {
        foreach ($collections as $campaignId => $collection) {

            $arraySum[$campaignId] = $collection->sum('cost');
        }
        return $arraySum ?? [];
    }

    public static function costByCollection(Collection|LengthAwarePaginator $collections): array
    {
        foreach ($collections as $campaignId => $collection) {

            $arraySum[$campaignId] = $collection->sum('cost') / $collection->count();
        }
        return $arraySum ?? [];
    }

    public static function reportBuild(?string $reportType, array $dates): array
    {
        if (str_contains($reportType, '|')) {

            $campaignId = explode('|', $reportType)[1];
            $reportType = 'campaign_id';
        }

        $strategy = match ($reportType) {
            'country' => new CountryStrategy($dates),
            'os'      => new OsStrategy($dates),
            'name'    => new NameStrategy($dates),
            'zone_type' => new ZoneTypeStrategy($dates),
            'zone_id' => new ZoneIdStrategy($dates),
            'campaign_id' => new CampaignIdStrategy($dates, $campaignId),
            default   => new CampaignStrategy($dates),
        };
        return $strategy->build();
    }

    public static function prepareColumns(
        array $columnsRaw,
        string $targetReport,
        array $reportData,
        FilterRequest $request,
    ): array
    {
        foreach ($columnsRaw as $columnCode => $columnTitle) {

            if ($targetReport != 'campaign') {

                $columns[] = TD::make($columnCode, $columnTitle);

            } elseif ($columnCode == 'id') {

                $columns[] = TD::make($columnCode, $columnTitle)->defaultHidden(true);
            } else {
                if ($columnCode == 'name') {

                    $columns[] = TD::make($columnCode, $columnTitle)
                        ->render(function (Repository $reportData) use ($request, $columnCode) {

                            return "<b><a href=".env('APP_URL').'/admin/reports/campaign/'.$reportData->get('id').">{$reportData->get('name')}</a></b>";
                        });
                } else {
                    $columns[] = TD::make($columnCode, $columnTitle);
                }
            }
        }
        return $columns ?? [];
    }

    public static function getReportZone($actionsRawZoneType)
    {
        foreach ($actionsRawZoneType as $type => $collection) {

            $sum = round($collection->sum('cost'), 2);

            $countInstall    = $collection->where('is_install', true)->count();
            $countTransition = $collection->count();

            $cr = $countInstall > 0 ? round(($countInstall / $countTransition) * 100, 1) : 0;

            $avgCostInstall = $countInstall > 0 ? round(($countInstall / $countTransition) * 100, 4) : 0;

            $avgCostTransition = $countTransition > 0 ? round($sum / $countTransition, 4) : 0;

            $repositories[] = new Repository([
                'type'      => $type,
                'costs_all' => $sum,
                'costs_install'     => $collection->where('is_install', true)->sum('cost'),
                'count_transition'  => $countTransition,
                'count_install'     => $countInstall,
                'cr'                => $cr.'%',
                'avg_cost_install'  => $avgCostInstall,
                'avg_cost_transition' => $avgCostTransition,
                'count_prelanding'  => $collection->where('transition_type', 'prelanding')->count(),
                'count_direct'      => $collection->where('transition_type', 'direct')->count(),
                'count_android'     => $collection->where('os', 'android')->count(),
                'count_ios'         => $collection->where('os', 'ios')->count(),
            ]);
        }
        return $repositories ?? [];
    }
}
