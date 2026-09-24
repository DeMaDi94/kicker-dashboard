import { toast as sonner } from 'sonner';

/**
 * The three kinds of toast: `info ℹ`, `ok ✓`, `err ⚠`, 4.5 s by default
 * (docs/DECISIONS.md B9), individually closable (the close button is on the
 * Toaster, components/ui/sonner.tsx). Passing `0` as the duration keeps a
 * toast until it is dismissed.
 *
 * The message is already translated: this is a plain function, callable from
 * outside React, so it cannot reach `useTranslation()` itself.
 */
export type ToastKind = 'info' | 'ok' | 'err';

export const TOAST_ICON: Record<ToastKind, string> = {
    info: 'ℹ',
    ok: '✓',
    err: '⚠',
};

export const TOAST_DURATION_MS = 4500;

/**
 * Raising a toast must never itself fail: it is the channel errors are
 * reported through, so a failure here would swallow the one being reported.
 */
export function toast(
    message: string,
    kind: ToastKind = 'info',
    durationMs: number = TOAST_DURATION_MS,
): void {
    try {
        sonner(`${TOAST_ICON[kind]} ${message}`, {
            duration: durationMs > 0 ? durationMs : Infinity,
            className: `core-toast core-toast-${kind}`,
        });
    } catch {
        // Deliberately swallowed, see above.
    }
}
