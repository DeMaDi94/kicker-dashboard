import { usePage } from '@inertiajs/react';
import { useState } from 'react';

/*
 * Whether the navigation rail is folded, remembered in an unencrypted cookie
 * (bootstrap/app.php) that the server reads back into the `navCollapsed` prop
 * (HandleInertiaRequests). It has to reach the server: the first paint is
 * server-rendered, and a preference only the browser knows would make the
 * rail unfold and then snap shut.
 *
 * The control that folds the rail and the rail itself are on opposite sides of
 * the shell, so the state lives here and AppLayout hands it to both.
 */
export const NAV_COLLAPSED_COOKIE = 'nav_collapsed';

const ONE_YEAR_IN_SECONDS = 60 * 60 * 24 * 365;

function remember(collapsed: boolean): void {
    document.cookie = `${NAV_COLLAPSED_COOKIE}=${collapsed ? '1' : '0'}; path=/; max-age=${ONE_YEAR_IN_SECONDS}; samesite=lax`;
}

export function useNavCollapsed(): {
    collapsed: boolean;
    toggle: () => void;
} {
    const { navCollapsed } = usePage().props;
    const [collapsed, setCollapsed] = useState(navCollapsed);

    return {
        collapsed,
        toggle: () =>
            setCollapsed((was) => {
                remember(!was);

                return !was;
            }),
    };
}
