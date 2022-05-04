<?php

namespace App\Http\Controllers;

use App\Http\Requests\HookRequest;
use App\Http\Requests\TransitionRequest;
use App\Models\Api\Action;
use App\Models\Api\App;
use App\Models\Api\Link;
use Carbon\Carbon;

class ApiController extends Controller
{
    /**
     * Вебхук от установленного приложения
     *
     * @param HookRequest $request
     * @return void
     */
    public function install(HookRequest $request)
    {
        $action = Action::query()
            ->where('click_id', $request->clickid)
            ->firstOrFail();

        $action->is_install = true;
        $action->install_at = Carbon::now()->format('Y-m-d');
        $action->save();
    }
}
