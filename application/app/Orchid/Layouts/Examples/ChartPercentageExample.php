<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Examples;

use Orchid\Screen\Layouts\Chart;

class ChartPercentageExample extends Chart
{
    /**
     * @var string
     */
    protected $title = 'Отчет по кампаниям';

    /**
     * @var int
     */
    protected $height = 133;

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
    protected $target = 'charts';
}
