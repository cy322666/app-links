<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class FilterRequest
{
    public Request $request;
    public $dateAt;
    public $dateTo;
    public $countDays;

    public function filter(): string
    {
        $queryDates  = 'date_at='.$this->dateAt.'&date_to='.$this->dateTo;
        $queryReport = 'target_report='.$this->request->targetReport ?? 'campaign';

        return 'admin/reports?'.$queryDates.'&'.$queryReport;
    }

    public function filterCampaign(): string
    {
        $queryDates  = 'date_at='.$this->dateAt.'&date_to='.$this->dateTo;
        $queryReport = 'target_report='.$this->request->targetReport ?? 'campaign';

        return url()->current().'?'.$queryDates.'&'.$queryReport;
    }

    public function getDatesByRequest(Request $request): static
    {
        $this->request = $request;

        $this->dateAt = $request->input('date_at') ? Carbon::parse($request->input('date_at')) : Carbon::now()->subDays(6);
        $this->dateTo = $request->input('date_to') ? Carbon::parse($request->input('date_to')) : Carbon::now()->addDay();

        $this->countDays = $this->dateAt->diffInDays($this->dateTo);

        return $this;
    }

    public function getDataBySave(Request $request): static
    {
        $this->request = $request;

        $this->dateAt = $request->filterDates['start'];
        $this->dateTo = $request->filterDates['end'];

        return $this;
    }
}
