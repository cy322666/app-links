<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public static function getDatesByRequest(Request $request) : array
    {
        $date_at = $request->input('date_at') ? Carbon::parse($request->input('date_at')) : Carbon::now()->subDays(6);
        $date_to = $request->input('date_to') ? Carbon::parse($request->input('date_to')) : Carbon::now()->addDay();

        return [
            'dateAt' => $date_at,
            'dateTo' => $date_to,
            'countDays' => $date_at->diffInDays($date_to)
        ];
    }

    public static function reportBuild(?string $reportType, array $dates): array
    {
        $strategy = match ($reportType) {
            'country' => new CountryStrategy($dates),
            'os'      => new OsStrategy($dates),
            'name'    => new NameStrategy($dates),
            default   => new CampaignStrategy($dates),
        };
        return $strategy->build();
    }

    public static function prepareColumns(array $columnsRaw): array
    {
        foreach ($columnsRaw as $columnCode => $columnTitle) {

            $columns[] = TD::make($columnCode, $columnTitle);
        }
        return $columns ?? [];
    }
}
