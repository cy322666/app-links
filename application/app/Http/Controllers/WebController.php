<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransitionRequest;
use App\Jobs\TransitionJob;
use App\Models\Api\Link;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class WebController extends Controller
{
    /**
     * Переход по ссылке
     *
     * @param TransitionRequest $request
     * @return Application|RedirectResponse|Redirector
     */
    public function transition(TransitionRequest $request)
    {
        $link = Link::query()
            ->where('uuid', $request->uuid)
            ->firstOrFail();

        TransitionJob::dispatch($request->toArray(), $link);

        if($link->is_prelanding === true) {

            return redirect($link->prelanding_url.'?clickid='.$request->clickid);
        } else {

            return redirect($link->app->url);
        }
    }
}
