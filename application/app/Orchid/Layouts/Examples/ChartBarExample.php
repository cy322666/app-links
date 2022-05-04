<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Examples;

use Orchid\Screen\Layouts\Chart;

class ChartBarExample extends Chart
{
    /**
     * @var string
     */
    protected $title = 'Операционные системы';

    /**
     * Available options:
     * 'bar', 'line',
     * 'pie', 'percentage'.
     *
     * @var string
     */
    protected $type = 'bar';

    protected $export = false;

    /**
     * @var string
     */
    protected $target = 'chartsOS';
}
