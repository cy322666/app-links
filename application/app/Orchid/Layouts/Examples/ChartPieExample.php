<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Examples;

use Orchid\Screen\Layouts\Chart;

class ChartPieExample extends Chart
{
    /**
     * @var string
     */
    protected $title = 'Кампании по сумме';

    /**
     * @var int
     */
    protected $height = 350;

    protected $export = false;

    /**
     * Available options:
     * 'bar', 'line',
     * 'pie', 'percentage'.
     *
     * @var string
     */
    protected $type = 'pie';

    /**
     * @var string
     */
    protected $target = 'chartsTransitionType';
}
