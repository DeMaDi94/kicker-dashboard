<?php

declare(strict_types=1);

namespace App\Domain\Users;

/**
 * B13 — code checks these, never a role name, so a project can add a role
 * without touching the checks. Restoring a deleted user needs `users.delete`.
 */
enum Permission: string
{
    case ViewUsers = 'users.view';
    case CreateUsers = 'users.create';
    case UpdateUsers = 'users.update';
    case DeleteUsers = 'users.delete';

    // ACC-03 — the league's admin-only actions.
    case CreatePlayers = 'players.create';
    case CreateSeasons = 'seasons.create';
    case SetSeasonPlayers = 'seasons.set-players';
    // PEN-04 — the interim settlement changes later (ACC-03).
    case SetSeasonSettlement = 'seasons.set-settlement';
    // SEA-06 — deleting a season; restoring one needs it too.
    case DeleteSeasons = 'seasons.delete';

    // VIS-04, D14 — the visit statistics, for admins only.
    case ViewVisits = 'visits.view';

    // D16 — the installation's settings (outgoing mail), for admins only.
    case ManageSettings = 'settings.manage';
}
