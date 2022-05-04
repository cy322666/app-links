<?php

namespace App\Jobs;

use App\Http\Requests\TransitionRequest;
use App\Models\Api\Link;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TransitionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private array $requestData,
        private Link $link,
    )
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info(__METHOD__, $this->requestData);

        $this->link->actions()->create([
            'type'    => 'transition',
            'app_id'  => $this->link->app->id,
            'os'      => $this->requestData['os'] ?? null,
            'country' => $this->requestData['country'] ?? null,
            'cost'    => $this->requestData['cost'] ?? null,
            'campaign_id' => $this->requestData['campaign_id'] ?? null,
            'click_id'  => $this->requestData['click_id'] ?? null,

            'date' => Carbon::now()->format('Y-m-d'),
            'transition_type' => $this->link->is_prelanding == true ? 'prelanding' : 'direct',
        ]);
    }
}
