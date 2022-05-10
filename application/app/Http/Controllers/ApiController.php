<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckRequest;
use App\Http\Requests\HookRequest;
use App\Http\Requests\TransitionRequest;
use App\Models\Api\Action;
use App\Models\Api\App;
use App\Models\Api\Link;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        Log::info(__METHOD__, $request->toArray());

        $action = Action::query()
            ->where('click_id', $request->clickid)
            ->firstOrFail();

        $action->is_install = true;
        $action->install_at = Carbon::now()->format('Y-m-d');
        $action->save();
    }

    /**
     * Проверка clickId на создание менее 24 часов
     * @param CheckRequest $request
     * @return bool[]
     */
    public function check(CheckRequest $request) : array
    {
        return [
            'isAlive' => (boolean)Action::query()
                ->where('click_id', $request->clickid)
                ->where('created_at', '>', Carbon::now()->subDay()->format('Y-m-d H:i:s'))
                ->first()
        ];
    }
}
