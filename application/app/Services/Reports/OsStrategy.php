<?php

namespace App\Services\Reports;

use App\Models\Api\Action;
use Orchid\Screen\Repository;

class OsStrategy
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
    ];

    public function __construct(array $dates)
    {
        $this->dateAt = $dates['dateAt']->format('Y-m-d');
        $this->dateTo = $dates['dateTo']->format('Y-m-d');
        $this->countDays = $dates['countDays'];
    }

    //os | costs | count_transition | count_install | ctr | prelanding | direct
    public function build(): array
    {
        $osCollections = Action::query()
            ->whereBetween('date', [
                $this->dateAt,
                $this->dateTo,
            ])
            ->get()
            ->groupBy('os');

        foreach ($osCollections as $osType => $osCollection) {

            $countTransitionAll = $osCollection->count();
            $countInstallAll = $osCollection
                ->where('is_install', true)
                ->count();

            if ($countInstallAll > 0) {

                $cr = round(($countInstallAll / $countTransitionAll) * 100, 1);
            } else {
                $cr = 0;
            }

            $dataReport[] = new Repository([
                'name'  => $osType,
                'costs' => $osCollection->sum('cost'),
                'count_transition' => $countTransitionAll,
                'count_install'    => $countInstallAll,
                'cr' => $cr.' %',
                'count_prelanding' => $osCollection
                    ->where('transition_type', 'prelanding')
                    ->count(),
                'count_direct'     => $osCollection
                    ->where('transition_type', 'direct')
                    ->count(),
            ]);
            $dataReport['columns'] = self::$columns;
        }
        return $dataReport ?? ['columns' => self::$columns];
    }
}
