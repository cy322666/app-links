<?php

namespace App\Services\Reports;

use App\Models\Api\Action;
use App\Models\Api\Link;
use Orchid\Screen\Repository;

class NameStrategy
{
    private string $dateAt;
    private string $dateTo;
    private int $countDays;

    public static array $columns = [
        'name' => 'Название',
        'costs' => 'Стоимость',
        'count_transition' => 'Количество переходов',
        'count_install' => 'Количество установок',
        'cr' => 'CR',
        'count_prelanding' => 'Количество прелендов',
        'count_direct' => 'Количество прямых',
        'count_android' => 'Количество Android',
        'count_ios' => 'Количество iOS',
    ];

    public function __construct(array $dates)
    {
        $this->dateAt = $dates['dateAt']->format('Y-m-d');
        $this->dateTo = $dates['dateTo']->format('Y-m-d');
        $this->countDays = $dates['countDays'];
    }

    //name | costs | count_transition | count_install | ctr | prelanding | direct | android | ios
    public function build()
    {
        $collections = Action::query()
            ->whereBetween('date', [
                $this->dateAt,
                $this->dateTo,
            ])
            ->get()
            ->groupBy('link_id');

        foreach ($collections as $typeCollection => $collection) {

            $countTransitionAll = $collection->count();
            $countInstallAll = $collection
                ->where('is_install', true)
                ->count();

            if ($countInstallAll > 0) {

                $cr = round(($countInstallAll / $countTransitionAll) * 100, 1);
            } else {
                $cr = 0;
            }

            $dataReport[] = new Repository([
                'name'  => Link::find($typeCollection)->name,
                'costs' => $collection->sum('cost'),
                'count_transition' => $countTransitionAll,
                'count_install'    => $countInstallAll,
                'cr' => $cr.' %',
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
