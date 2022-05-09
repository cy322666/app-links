<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Layouts\Chart;

class ChartZoneNameLayout extends Chart
{
    /**
     * Add a title to the Chart.
     *
     * @var string
     */
    protected $title = 'Сумма затрат на клики по регионам';

    protected $height = 200;
    /**
     * Available options:
     * 'bar', 'line',
     * 'pie', 'percentage'.
     *
     * @var string
     */
    protected $type = 'percentage';

    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the chart.
     *
     * @var string
     */
    protected $target = 'zoneName';

    /**
     * Determines whether to display the export button.
     *
     * @var bool
     */
    protected $export = false;
}
