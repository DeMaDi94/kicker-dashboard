import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import DeleteNewsController from '@/actions/App/Http/News/DeleteNews/DeleteNewsController';
import StoreNewsController from '@/actions/App/Http/News/StoreNews/StoreNewsController';
import UpdateNewsController from '@/actions/App/Http/News/UpdateNews/UpdateNewsController';
import { AutoGrowTextarea } from '@/components/core/auto-grow-textarea';
import { useConfirm } from '@/components/core/dialogs';
import { Panel, PanelHeader } from '@/components/core/panel';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';
import { formatDate } from '@/lib/date';
import { linkify } from './linkify';
import type { NewsLine } from './types';

/** NEWS-02 — the newest three are shown, older ones behind „Alle anzeigen“. */
const VISIBLE = 3;

/*
 * NEWS-01–03 — a season's news above the overall table: anyone reads them,
 * a signed-in user writes one, its author or an admin changes or deletes it.
 */
export function NewsPanel({
    seasonId,
    news,
    canWrite,
}: {
    seasonId: number;
    news: NewsLine[];
    canWrite: boolean;
}) {
    const { t } = useTranslation();
    const [showAll, setShowAll] = useState(false);
    const [writing, setWriting] = useState(false);
    const [editing, setEditing] = useState<number | null>(null);
    const visible = showAll ? news : news.slice(0, VISIBLE);

    return (
        <Panel>
            <PanelHeader title={t('News')}>
                {canWrite && !writing && (
                    <Button size="sm" onClick={() => setWriting(true)}>
                        {t('Write news')}
                    </Button>
                )}
            </PanelHeader>

            {writing && (
                <div className="border-b border-brand-line-soft p-4">
                    <NewsForm
                        id="news-new"
                        text=""
                        onSubmit={(form, done) =>
                            form.submit(StoreNewsController(seasonId), {
                                preserveScroll: true,
                                onSuccess: done,
                            })
                        }
                        onClose={() => setWriting(false)}
                    />
                </div>
            )}

            <ul>
                {visible.map((item) =>
                    editing === item.id ? (
                        <li
                            key={item.id}
                            className="border-b border-brand-line-soft p-4 last:border-0"
                        >
                            <NewsForm
                                id={`news-${item.id}`}
                                text={item.text}
                                onSubmit={(form, done) =>
                                    form.submit(UpdateNewsController(item.id), {
                                        preserveScroll: true,
                                        onSuccess: done,
                                    })
                                }
                                onClose={() => setEditing(null)}
                            />
                        </li>
                    ) : (
                        <NewsEntry
                            key={item.id}
                            item={item}
                            onEdit={() => setEditing(item.id)}
                        />
                    ),
                )}
            </ul>

            {!showAll && news.length > VISIBLE && (
                <div className="border-t border-brand-line-soft px-4 py-2">
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => setShowAll(true)}
                    >
                        {t('Show all')}
                    </Button>
                </div>
            )}
        </Panel>
    );
}

function NewsEntry({ item, onEdit }: { item: NewsLine; onEdit: () => void }) {
    const { t, locale } = useTranslation();
    const confirm = useConfirm();

    // D17 — deleting a news post asks first.
    const remove = async () => {
        const confirmed = await confirm(t('This news post will be deleted.'), {
            title: t('Delete news post?'),
            okLabel: t('Delete'),
            danger: true,
        });

        if (confirmed) {
            router.delete(DeleteNewsController(item.id), {
                preserveScroll: true,
            });
        }
    };

    return (
        <li className="border-b border-brand-line-soft px-4 py-3 last:border-0">
            <div className="flex flex-wrap items-baseline gap-x-2 text-xs text-brand-muted">
                <span>{formatDate(item.postedAt, locale)}</span>
                {item.edited && <span>({t('edited')})</span>}
                <span aria-hidden>·</span>
                <span className="font-medium text-brand-ink-soft">
                    {item.authorName}
                </span>
                {item.mayChange && (
                    <span className="ml-auto flex gap-1">
                        <Button variant="ghost" size="sm" onClick={onEdit}>
                            {t('Edit')}
                        </Button>
                        <Button variant="ghost" size="sm" onClick={remove}>
                            {t('Delete')}
                        </Button>
                    </span>
                )}
            </div>
            <p className="mt-1 text-sm break-words whitespace-pre-line text-brand-ink">
                {linkify(item.text).map((part, index) =>
                    part.kind === 'link' ? (
                        <a
                            key={index}
                            href={part.value}
                            target="_blank"
                            rel="noopener noreferrer nofollow"
                            className="break-all text-brand-accent-strong underline"
                        >
                            {part.value}
                        </a>
                    ) : (
                        part.value
                    ),
                )}
            </p>
        </li>
    );
}

type NewsFormData = { text: string };

function NewsForm({
    id,
    text,
    onSubmit,
    onClose,
}: {
    id: string;
    text: string;
    onSubmit: (
        form: ReturnType<typeof useForm<NewsFormData>>,
        done: () => void,
    ) => void;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const form = useForm<NewsFormData>({ text });

    return (
        <form
            className="grid gap-3"
            onSubmit={(event) => {
                event.preventDefault();
                onSubmit(form, onClose);
            }}
        >
            <div className="grid gap-2">
                <Label htmlFor={id}>{t('News post')}</Label>
                <AutoGrowTextarea
                    id={id}
                    rows={3}
                    required
                    value={form.data.text}
                    onChange={(event) =>
                        form.setData('text', event.target.value)
                    }
                    className="text-sm"
                />
                <InputError message={form.errors.text} />
            </div>
            <div className="flex items-center gap-4">
                <Button disabled={form.processing}>{t('Save')}</Button>
                <Button type="button" variant="ghost" onClick={onClose}>
                    {t('Cancel')}
                </Button>
            </div>
        </form>
    );
}
