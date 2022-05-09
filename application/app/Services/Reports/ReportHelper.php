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

    public static function sumByCollection(Collection|LengthAwarePaginator $collections)
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
        $strategy = match ($reportType) {
            'country' => new CountryStrategy($dates),
            'os'      => new OsStrategy($dates),
            'name'    => new NameStrategy($dates),
            'zone_type' => new ZoneTypeStrategy($dates),
            'zone_id' => new ZoneIdStrategy($dates),
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
//                Legend::make('links', [
//                    TD::make('id', 'ID')
//                        ->width('150')
//                        ->render(function (Repository $model) {
//                            // Please use view('path')
//                            return "<img src='https://picsum.photos/450/200?random={$model->get('id')}'
//                              alt='sample'
//                              class='mw-100 d-block img-fluid'>
//                            <span class='small text-muted mt-1 mb-0'># {$model->get('id')}</span>";
//                        }),
}
