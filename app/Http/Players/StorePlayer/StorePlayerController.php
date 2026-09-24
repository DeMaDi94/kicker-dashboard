<?php

declare(strict_types=1);

namespace App\Http\Players\StorePlayer;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class StorePlayerController
{
    public function __invoke(StorePlayerRequest $request, StorePlayerService $store): RedirectResponse
    {
        $store($request->string('name')->toString(), $request->string('alias')->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Player created.')]);

        return to_route('players.index');
    }
}
