<?php

namespace App\Orchid\Screens;

use App\Models\Api\Error;
use Carbon\Carbon;
use Orchid\Screen\Layout;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;

class ErrorsScreen extends Screen
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'errors';

    public function name(): ?string
    {
        return 'Ошибки';
    }

    public function description(): ?string
    {
        return 'Страница c таблицей ошибок приложения';
    }

    public function query(): iterable
    {
        return [
            'errors' => Error::paginate(),
        ];
    }

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [

        ];
    }

    public function layout(): iterable
    {
        return [
            \Orchid\Support\Facades\Layout::table('errors', [
                TD::make('created_at')->render(function ($link) {
                    return Carbon::parse($link->created_at)->format('Y-m-d H:i:s');
                })->width(200),
                TD::make('text')->align(TD::ALIGN_LEFT)->width(500),
                TD::make('file')->align(TD::ALIGN_LEFT)->width(150),
                TD::make('line')->align(TD::ALIGN_CENTER)->width(50),
            ]),
        ];
    }
}
