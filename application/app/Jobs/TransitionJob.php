<?php

namespace App\Jobs;

use App\Dto\ActionTransitionDto;
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
        $dto = ActionTransitionDto::transform($this->requestData);

        $this->link->actions()->create([
            'type'    => 'transition',
            'app_id'  => $this->link->app->id,
            'os'      => $dto->os,
            'country' => $dto->country,
            'cost'    => $dto->cost,
            'campaign_id' => $dto->campaignId,
            'click_id'  => $dto->clickId,
            'zone_id'   => $dto->zoneId,
            'zone_type' => $dto->zoneType,

            'date' => Carbon::now()->format('Y-m-d'),
            'transition_type' => $this->link->is_prelanding == true ? 'prelanding' : 'direct',
        ]);
    }
}
