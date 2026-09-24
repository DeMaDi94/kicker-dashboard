import { Component, type ErrorInfo, type ReactNode } from 'react';
import { toast } from '@/components/core/toast';
import { useTranslation } from '@/hooks/use-translation';

/*
 * A view that fails to render must not take the shell down with it: the
 * navigation keeps working and the failure surfaces as a toast naming the
 * view, for 9 s (docs/DECISIONS.md B9).
 *
 * `view` is the view's name, already translated by the caller.
 */

const VIEW_ERROR_TOAST_MS = 9000;

type Props = {
    view: string;
    children: ReactNode;
    /* A view may say the failure in its own words and in its own place;
       `fallback` gets the error message so it can. */
    fallback?: (message: string) => ReactNode;
};

export function ViewErrorBoundary({ view, children, fallback }: Props) {
    const { t } = useTranslation();

    return (
        <Boundary
            view={view}
            fallback={fallback}
            describe={(message) =>
                t('Could not load the view „:view“: :message', {
                    view,
                    message,
                })
            }
            failedText={t('This view could not be loaded.')}
        >
            {children}
        </Boundary>
    );
}

/* Error boundaries can only be classes, and a class cannot call a hook, so the
   translated strings are handed in by the function component above. */
type BoundaryProps = Props & {
    describe: (message: string) => string;
    failedText: string;
};
type State = { failed: boolean; message: string };

class Boundary extends Component<BoundaryProps, State> {
    state: State = { failed: false, message: '' };

    static getDerivedStateFromError(error: Error): State {
        return { failed: true, message: error.message };
    }

    componentDidCatch(error: Error, info: ErrorInfo): void {
        console.error(info.componentStack);

        toast(this.props.describe(error.message), 'err', VIEW_ERROR_TOAST_MS);
    }

    /* A new view renders again rather than staying dead. */
    componentDidUpdate(previous: BoundaryProps): void {
        if (previous.view !== this.props.view && this.state.failed) {
            this.setState({ failed: false, message: '' });
        }
    }

    render(): ReactNode {
        if (!this.state.failed) {
            return this.props.children;
        }

        return (
            this.props.fallback?.(this.state.message) ?? (
                <p className="p-4 text-brand-muted">{this.props.failedText}</p>
            )
        );
    }
}
