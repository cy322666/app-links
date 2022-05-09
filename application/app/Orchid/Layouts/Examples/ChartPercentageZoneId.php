<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Examples;

use Orchid\Screen\Layouts\Chart;

class ChartPercentageZoneId extends Chart
{
    /**
     * @var string
     */
    protected $title = 'По ID регионов';

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
    protected $type = 'percentage';

    /**
     * @var string
     */
    protected $target = 'zoneId';

    protected $export = false;
}
