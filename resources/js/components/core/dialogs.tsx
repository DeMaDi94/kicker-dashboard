import {
    createContext,
    useCallback,
    useContext,
    useMemo,
    useRef,
    useState,
    type ReactNode,
} from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslation, type Translate } from '@/hooks/use-translation';

/*
 * Confirmations, prompts and alerts are styled modals, never the browser's.
 * They are promise-returning so a call site reads like the native one it
 * replaces: `if (await confirm('…')) { … }`.
 *
 * Escape cancels, Enter accepts, a click on the backdrop dismisses and focus
 * starts on the primary action — Escape and the backdrop come from the Radix
 * dialog underneath, Enter and the focus are set here.
 *
 * Messages, labels and titles passed in are already translated; the defaults
 * are translated here.
 */

type ConfirmOptions = {
    title?: string;
    okLabel?: string;
    cancelLabel?: string;
    danger?: boolean;
};

type PromptOptions = {
    title?: string;
    okLabel?: string;
    placeholder?: string;
};

type AlertOptions = {
    title?: string;
    okLabel?: string;
};

type Request =
    | {
          kind: 'confirm';
          message: string;
          options: ConfirmOptions;
          resolve: (value: boolean) => void;
      }
    | {
          kind: 'prompt';
          label: string;
          defaultValue: string;
          options: PromptOptions;
          resolve: (value: string | null) => void;
      }
    | {
          kind: 'alert';
          message: string;
          options: AlertOptions;
          resolve: () => void;
      };

type DialogApi = {
    confirm: (message: string, options?: ConfirmOptions) => Promise<boolean>;
    prompt: (
        label: string,
        defaultValue?: string,
        options?: PromptOptions,
    ) => Promise<string | null>;
    alert: (message: string, options?: AlertOptions) => Promise<void>;
};

const DialogContext = createContext<DialogApi | null>(null);

export function DialogProvider({ children }: { children: ReactNode }) {
    const { t } = useTranslation();
    const [request, setRequest] = useState<Request | null>(null);
    const [value, setValue] = useState('');
    const inputRef = useRef<HTMLInputElement>(null);

    const close = useCallback(() => setRequest(null), []);

    const api = useMemo<DialogApi>(
        () => ({
            confirm: (message, options = {}) =>
                new Promise<boolean>((resolve) => {
                    setRequest({ kind: 'confirm', message, options, resolve });
                }),
            prompt: (label, defaultValue = '', options = {}) =>
                new Promise<string | null>((resolve) => {
                    setValue(defaultValue);
                    setRequest({
                        kind: 'prompt',
                        label,
                        defaultValue,
                        options,
                        resolve,
                    });
                }),
            alert: (message, options = {}) =>
                new Promise<void>((resolve) => {
                    setRequest({ kind: 'alert', message, options, resolve });
                }),
        }),
        [],
    );

    /* Cancelling is what Escape, the backdrop and the ✕ all do. */
    const cancel = useCallback(() => {
        if (!request) {
            return;
        }

        if (request.kind === 'confirm') {
            request.resolve(false);
        } else if (request.kind === 'prompt') {
            request.resolve(null);
        } else {
            request.resolve();
        }

        close();
    }, [request, close]);

    const accept = useCallback(() => {
        if (!request) {
            return;
        }

        if (request.kind === 'confirm') {
            request.resolve(true);
        } else if (request.kind === 'prompt') {
            request.resolve(value);
        } else {
            request.resolve();
        }

        close();
    }, [request, close, value]);

    return (
        <DialogContext.Provider value={api}>
            {children}
            <Dialog
                open={request !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        cancel();
                    }
                }}
            >
                {request !== null && (
                    <DialogContent
                        className="rounded-brand"
                        onKeyDown={(event) => {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                                accept();
                            }
                        }}
                        onOpenAutoFocus={(event) => {
                            /* The primary action holds focus; a prompt puts
                               it in the field and selects the default, so
                               typing replaces it. */
                            event.preventDefault();

                            if (request.kind === 'prompt') {
                                inputRef.current?.focus();
                                inputRef.current?.select();

                                return;
                            }

                            document
                                .querySelector<HTMLButtonElement>(
                                    '[data-core-dialog-ok]',
                                )
                                ?.focus();
                        }}
                    >
                        <DialogHeader>
                            <DialogTitle>{dialogTitle(request, t)}</DialogTitle>
                        </DialogHeader>

                        {request.kind === 'prompt' ? (
                            <div className="grid gap-2">
                                <Label htmlFor="core-prompt-input">
                                    {request.label}
                                </Label>
                                <Input
                                    id="core-prompt-input"
                                    ref={inputRef}
                                    type="text"
                                    value={value}
                                    placeholder={request.options.placeholder}
                                    onChange={(event) =>
                                        setValue(event.target.value)
                                    }
                                />
                            </div>
                        ) : (
                            <p className="whitespace-pre-line text-brand-ink">
                                {request.message}
                            </p>
                        )}

                        <DialogFooter>
                            {request.kind !== 'alert' && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    onClick={cancel}
                                >
                                    {request.kind === 'confirm'
                                        ? (request.options.cancelLabel ??
                                          t('Cancel'))
                                        : t('Cancel')}
                                </Button>
                            )}
                            <Button
                                type="button"
                                data-core-dialog-ok
                                variant={
                                    request.kind === 'confirm' &&
                                    request.options.danger
                                        ? 'destructive'
                                        : 'default'
                                }
                                onClick={accept}
                            >
                                {okLabel(request, t)}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                )}
            </Dialog>
        </DialogContext.Provider>
    );
}

function dialogTitle(request: Request, t: Translate): string {
    if (request.kind === 'confirm') {
        return (
            request.options.title ??
            (request.options.danger ? t('Confirm deletion') : t('Confirm'))
        );
    }

    if (request.kind === 'prompt') {
        return request.options.title ?? t('Input');
    }

    return request.options.title ?? t('Notice');
}

function okLabel(request: Request, t: Translate): string {
    if (request.kind === 'confirm') {
        return (
            request.options.okLabel ??
            (request.options.danger ? t('Delete') : t('OK'))
        );
    }

    if (request.kind === 'prompt') {
        return request.options.okLabel ?? t('Apply');
    }

    return request.options.okLabel ?? t('OK');
}

function useDialogs(): DialogApi {
    const api = useContext(DialogContext);

    if (api === null) {
        throw new Error(
            'useConfirm/usePrompt/useAlert must be used within DialogProvider.',
        );
    }

    return api;
}

export const useConfirm = (): DialogApi['confirm'] => useDialogs().confirm;
export const usePrompt = (): DialogApi['prompt'] => useDialogs().prompt;
export const useAlert = (): DialogApi['alert'] => useDialogs().alert;
