<?php

namespace App\Orchid\Screens;

use App\Models\Api\Action;
use App\Services\Reports\FilterRequest;
use App\Services\Reports\ReportHelper;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class CampaignScreen extends Screen
{
    private int $countDays;

    public $dateAt;
    public $dateTo;

    public function query(Action $action, Request $request): iterable
    {
        $this->dateAt = $request->input('date_at') ? Carbon::parse($request->input('date_at')) : Carbon::now()->subDays(29);
        $this->dateTo = $request->input('date_to') ? Carbon::parse($request->input('date_to')) : Carbon::now()->addDay();

        $this->countDays = $this->dateAt->diffInDays($this->dateTo);

        $actionsRaw = DB::table('actions')
            ->select([
                'created_at',
                'type',
                'os',
                'country',
                'cost',
                'date',
                'transition_type',
                'is_install',
                'install_at',
                'zone_id',
                'zone_type',
            ])
            ->where('campaign_id', $action->campaign_id)
            ->whereBetween('date', [
                $this->dateAt->format('Y-m-d'),
                $this->dateTo->format('Y-m-d'),
            ])
            ->get();

        $countAll = $actionsRaw->count();

        $countInstall = $actionsRaw
            ->where('is_install', true)
            ->count();

        return [
            'actions' => Action::query()
                ->where('campaign_id', $action->campaign_id)
                ->orderBy('updated_at', 'desc')
                ->limit(15)
                ->get(),

            'zoneType' => ReportHelper::getReport($actionsRaw->groupBy('zone_type'), $request->sort ?? 'count_install'),
            'zoneId'   => ReportHelper::getReport($actionsRaw->groupBy('zone_id'), $request->sort ?? 'count_install'),

            'count_transition' => ['value' => $countAll],
            'count_install'    => ['value' => $countInstall],
            'costs_transition' => ['value' => round($actionsRaw->sum('cost'), 2)],
            'cr' => ['value' => $countInstall > 0 ? round(($countInstall / $countAll) * 100, 1).'%' : "0%"],
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Детали кампании';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Страница просмотра кампании';
    }

    /**
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     *
     * @throws \Throwable
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [

            Layout::columns([

                Layout::metrics([
                    'Потрачено за ('.$this->countDays.') дней' => 'costs_transition',
                    'Общий CR за ('.$this->countDays.') дней'  => 'cr',
                    'Переходов за ('.$this->countDays.') дней' => 'count_transition',
                    'Установок за ('.$this->countDays.') дней' => 'count_install',
                ]),

                Layout::rows([
                    DateRange::make('filterDates')
                        ->title('Фильтр по дате'),

                    Group::make([
                        Button::make('Применить')
                            ->method('filter')
                            ->type(Color::DARK()),
                    ])->autoWidth(),
                ]),
            ]),

            Layout::table('zoneType', [
                TD::make('type', 'Тип'),
                TD::make('costs_all','Общ ст.')->sort(),
//                TD::make('costs_install','Общ. ст. установок')->sort(),
                TD::make('count_transition','Кол-во переходов')->sort(),
                TD::make('count_install','Кол-во установок')->sort(),
                TD::make('avg_cost_transition','Ср. ст. клика')->sort(),
                TD::make('avg_cost_install','Ср. ст. установки')->sort(),
                TD::make('cr')->render(function ($action) {
                    return $action['cr'].'%';
                })->sort(),
                TD::make('count_prelanding','Кол-во прелендов')->sort(),
                TD::make('count_direct','Кол-во прямых')->sort(),
                TD::make('count_android','Кол-во Android')->sort(),
                TD::make('count_ios','Кол-во iOS')->sort(),
            ])->title('Отчет Zone type'),

            Layout::table('zoneId', [
                TD::make('type', 'Тип'),
                TD::make('costs_all','Общ ст.')->sort(),
//                TD::make('costs_install','Общ. ст. установок')->sort(),
                TD::make('count_transition','Кол-во переходов')->sort(),
                TD::make('count_install','Кол-во установок')->sort(),
                TD::make('avg_cost_transition','Ср. ст. клика')->sort(),
                TD::make('avg_cost_install','Ср. ст. установки')->sort(),
                TD::make('cr')->render(function ($action) {
                    return $action['cr'].'%';
                })->sort(),
                TD::make('count_prelanding','Кол-во прелендов')->sort(),
                TD::make('count_direct','Кол-во прямых')->sort(),
                TD::make('count_android','Кол-во Android')->sort(),
                TD::make('count_ios','Кол-во iOS')->sort(),
            ])->title('Отчет Zone ID'),

            Layout::table('actions', [
                TD::make('transition_type', 'Тип перехода'),
                TD::make('date', 'Дата перехода'),
                TD::make('os', 'ОС'),
                TD::make('country', 'Страна'),
                TD::make('is_install', 'Установка'),
                TD::make('install_at', 'Дата установки'),
                TD::make('cost', 'Стоимость'),
            ])->title('События кампании'),
        ];
    }

    public function filter(Request $request)
    {
        $query = '?date_at='.$request->filterDates['start'].'&date_to='.$request->filterDates['end'];

        $url = str_replace('/filter', '', $request->fullUrl());

        return redirect($url.$query);
    }
}
