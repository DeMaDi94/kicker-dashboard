import { usePage } from '@inertiajs/react';
import { DialogProvider } from '@/components/core/dialogs';
import { ViewErrorBoundary } from '@/components/core/view-error-boundary';
import { useTranslation } from '@/hooks/use-translation';
import { AppHeader } from '@/layouts/shell/header';
import { AppSidebar } from '@/layouts/shell/sidebar';
import { TabBar } from '@/layouts/shell/tab-bar';
import { useNavCollapsed } from '@/layouts/shell/use-nav-collapsed';
import type { AppLayoutProps } from '@/types';

/*
 * The application shell: the navigation rail, and beside it the header trail,
 * the tab bars and the view itself. The rail runs the full height — it carries
 * the mark at the top and the user chip at the bottom, so nothing needs a band
 * across the page above it.
 *
 * The rail, the trail and the tab bars stay put; the view scrolls under them.
 * Below the compact breakpoint that inverts: the rail is a horizontal bar and
 * the page scrolls as one document again, because there is no height to spare
 * for two scroll areas. `scroll-region` tells Inertia that this element, and
 * not the window, is the one to reset to the top on each visit.
 *
 * The view sits inside an error boundary, so a page that throws surfaces as a
 * toast and the shell around it keeps working.
 *
 * The confirm/prompt/alert dialogs are mounted here rather than in app.tsx:
 * they need the translations, and `withApp` sits outside the page. A
 * persistent layout mounts them once for every page that uses it.
 *
 * When printing, only the view prints.
 */
export default function AppLayout({
    breadcrumbs = [],
    tabs,
    subTabs,
    children,
}: AppLayoutProps) {
    const { component } = usePage();
    const { t } = useTranslation();
    const { collapsed, toggle } = useNavCollapsed();

    const view = breadcrumbs.at(-1);

    return (
        <DialogProvider>
            <div className="flex min-h-screen flex-col bg-brand-bg text-brand-ink compact:h-screen compact:flex-row compact:overflow-hidden print:block print:h-auto print:overflow-visible">
                <div className="contents print:hidden">
                    <AppSidebar collapsed={collapsed} />
                </div>

                <div className="flex min-w-0 flex-1 flex-col compact:overflow-hidden print:block print:overflow-visible">
                    <div className="contents print:hidden">
                        <AppHeader
                            breadcrumbs={breadcrumbs}
                            collapsed={collapsed}
                            onToggleNav={toggle}
                        />

                        {tabs && (
                            <TabBar items={tabs.items} label={tabs.label} />
                        )}

                        {subTabs && (
                            <TabBar
                                items={subTabs.items}
                                label={subTabs.label}
                                secondary
                            />
                        )}
                    </div>

                    <main
                        scroll-region="true"
                        className="flex-1 overflow-y-auto print:overflow-visible"
                    >
                        <div className="w-full max-w-[1180px] px-4 pt-6 pb-16 print:max-w-none print:p-0">
                            <ViewErrorBoundary
                                view={view ? t(view.title) : component}
                            >
                                {children}
                            </ViewErrorBoundary>
                        </div>
                    </main>
                </div>
            </div>
        </DialogProvider>
    );
}
