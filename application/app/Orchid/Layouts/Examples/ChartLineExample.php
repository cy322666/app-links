<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Examples;

use Orchid\Screen\Layouts\Chart;

class ChartLineExample extends Chart
{
    /**
     * @var string
     */
    protected $title = 'Переходы / Установки';

    /**
     * @var string
     */
    protected $target = 'chartsActionsType';

    /**
     * Configuring line.
     *
     * @var array
     */
    protected $lineOptions = [
        'spline'     => 5,
        'regionFill' => 1,
        'hideDots'   => 1,
        'hideLine'   => 0,
        'heatline'   => 0,
        'dotSize'    => 1,
    ];

    protected $export = false;

    /**
     * To highlight certain values on the Y axis, markers can be set.
     * They will shown as dashed lines on the graph.
     */
    protected function markers(): ?array
    {
        return [
            [
                'label'   => '',
                'value'   => 0,
            ],
        ];
    }
}
