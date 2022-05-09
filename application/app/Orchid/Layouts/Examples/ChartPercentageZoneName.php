<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Examples;

use Orchid\Screen\Layouts\Chart;

class ChartPercentageZoneName extends Chart
{
    /**
     * @var string
     */
    protected $title = 'Регионы по кликам';

    /**
     * @var int
     */
    protected $height = 200;

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
    protected $target = '123';

    protected $export = false;
}
