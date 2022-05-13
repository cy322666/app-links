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

    public static function prepareArray(array $arrayDates, array $actionsInstallArray): array
    {
        foreach (array_flip($arrayDates) as $date => $key) {

            $arrayInstall[$date] = $actionsInstallArray[$date] ?? [];
        }

        return $arrayInstall ?? [];
    }

    public static function getReport($collections, string $sortType): array
    {
        foreach ($collections as $type => $collection) {

            $sum = round($collection->sum('cost'), 2);

            $countInstall    = $collection->where('is_install', true)->count();
            $countTransition = $collection->count();

            $cr = $countInstall > 0 ? round(($countInstall / $countTransition) * 100, 1) : 0;

            $avgCostInstall = $countInstall > 0 ? round(($countInstall / $countTransition) * 100, 4) : 0;

            $avgCostTransition = $countTransition > 0 ? round($sum / $countTransition, 4) : 0;

            $repositories[] = [
                'type'      => $type,
                'costs_all' => $sum,
                'costs_install'     => $collection->where('is_install', true)->sum('cost'),
                'count_transition'  => $countTransition,
                'count_install'     => $countInstall,
                'cr'                => $cr,
                'avg_cost_install'  => $avgCostInstall,
                'avg_cost_transition' => $avgCostTransition,
                'count_prelanding'  => $collection->where('transition_type', 'prelanding')->count(),
                'count_direct'      => $collection->where('transition_type', 'direct')->count(),
                'count_android'     => $collection->where('os', 'android')->count(),
                'count_ios'         => $collection->where('os', 'ios')->count(),
            ];
        }

        if (!empty($repositories)) {

            //сортировка
            if (count($repositories) > 1) {

                static::sortByKey($repositories, str_replace('-', '', $sortType), strripos($sortType, '-'));
            }

            //оборачивание в Repository
            $repositoriesCollection = array_map(function ($repository) {

                return new Repository($repository);

            }, $repositories);
        }

        return $repositoriesCollection ?? [new Repository([])];
    }

    private static function sortByKey(array &$repositories, string $key, bool|int $sortType)
    {
        usort($repositories, function($arr1, $arr2) use ($key, $sortType) {

            $param1 = floatval($arr1[$key]);
            $param2 = floatval($arr2[$key]);

            return $sortType === false ? $param1 < $param2 : $param1 > $param2;
        });
    }
}
