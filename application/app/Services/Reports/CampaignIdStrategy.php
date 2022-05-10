<?php

namespace App\Services\Reports;

use App\Models\Api\Action;
use Orchid\Screen\Repository;

class CampaignIdStrategy
{
    private string $dateAt;
    private string $dateTo;
    private int $countDays;
    private int $campaignId;

    public static array $columns = [
        'type' => 'Тип',
        'costs' => 'Стоимость',
        'avg_costs_install' => 'Средняя стоимость установки',
        'avg_costs_transition' => 'Средняя стоимость клика',
        'count_transition' => 'Количество переходов',
        'count_install' => 'Количество установок',
        'cr' => 'CR',
        'count_prelanding' => 'Количество прелендов',
        'count_direct' => 'Количество прямых',
        'count_android' => 'Количество Android',
        'count_ios' => 'Количество iOS',
    ];

    public function __construct(array $dates, string $campaignId)
    {
        $this->dateAt = $dates['dateAt']->format('Y-m-d');
        $this->dateTo = $dates['dateTo']->format('Y-m-d');
        $this->countDays  = $dates['countDays'];
        $this->campaignId = $campaignId;
    }

    public function build()
    {
        $collections = Action::query()
            ->whereBetween('date', [
                $this->dateAt,
                $this->dateTo,
            ])
            ->where('campaign_id', $this->campaignId);

        $collectionsZone = $collections
            ->get()
            ->groupBy('zone_type');

        foreach ($collectionsZone as $typeCollection => $collection) {

            $countTransitionAll = $collection->count();
            $countInstallAll = $collection
                ->where('is_install', true)
                ->count();

            $sum = $collection->sum('cost');

            if ($countInstallAll > 0) {

                $cr = round(($countInstallAll / $countTransitionAll) * 100, 1);
                $avgCostInstall = $sum / $countInstallAll;
            } else {
                $cr = 0;
                $avgCostInstall = '-';
            }
            if ($countTransitionAll > 0) {

                $avgCostTransition = $sum / $countTransitionAll;
            } else
                $avgCostTransition = '-';

            $dataReport[] = new Repository([
                'type'  => $typeCollection,
                'costs' => $sum,
                'avg_costs_install' => $avgCostInstall,
                'avg_costs_transition' => $avgCostTransition,
                'count_transition' => $countTransitionAll,
                'count_install'    => $countInstallAll,
                'cr' => $cr.'%',
                'count_prelanding' => $collection
                    ->where('transition_type', 'prelanding')
                    ->count(),
                'count_direct' => $collection
                    ->where('transition_type', 'direct')
                    ->count(),
                'count_android' => $collection
                    ->where('os', 'android')
                    ->count(),
                'count_ios' =>  $collection
                    ->where('os', 'ios')
                    ->count(),
            ]);
            $dataReport['columns'] = self::$columns;
        }
        return $dataReport ?? ['columns' => self::$columns];
    }
}
