<?php

declare(strict_types=1);

namespace App\Http\Players\UpdatePlayer;

use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class UpdatePlayerController
{
    public function __invoke(Player $player, UpdatePlayerRequest $request, UpdatePlayerService $update): RedirectResponse
    {
        $update($player, $request->string('name')->toString(), $request->string('alias')->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Player saved.')]);

        return to_route('players.index');
    }
}
