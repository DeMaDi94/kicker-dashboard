import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { AutoGrowTextarea } from '@/components/core/auto-grow-textarea';
import { useAlert, useConfirm, usePrompt } from '@/components/core/dialogs';
import { ListPager } from '@/components/core/list-pager';
import { NumberInput } from '@/components/core/number-input';
import { PageTitle } from '@/components/core/page-title';
import {
    AddButton,
    DeleteButton,
    GroupRule,
    Panel,
    PanelBody,
    PanelHeader,
} from '@/components/core/panel';
import { SortableList } from '@/components/core/sortable-list';
import { toast } from '@/components/core/toast';
import { activatableProps } from '@/components/core/toggleable';
import {
    useUndoRedo,
    useUndoRedoShortcuts,
} from '@/components/core/use-undo-redo';
import { ViewErrorBoundary } from '@/components/core/view-error-boundary';
import { Button } from '@/components/ui/button';

/*
 * The primitive gallery — every components/core primitive in its states,
 * inside the real shell, Tailwind and Inertia context. Cheaper than
 * Storybook, and a ready Playwright target for interaction tests.
 *
 * A developer tool, registered only outside production (routes/web.php) and
 * never shown to a user: its demo copy is English and deliberately not
 * translated. The primitives themselves render their own text in the active
 * locale.
 */
export default function Primitives() {
    return (
        <>
            <Head title="Primitives" />

            <PageTitle
                title="Primitives"
                description="Every core primitive in its states. Add a new primitive here too."
            />

            <div className="flex flex-col gap-4">
                <GroupRule label="Feedback" count="3" />
                <div className="grid gap-4 md:grid-cols-2">
                    <Dialogs />
                    <Toasts />
                    <Failing />
                </div>

                <GroupRule label="Input" count="3" />
                <div className="grid gap-4 md:grid-cols-2">
                    <Fields />
                    <History />
                    <Reordering />
                </div>

                <GroupRule label="Surfaces" count="2" />
                <div className="grid gap-4 md:grid-cols-2">
                    <Surfaces />
                    <Paging />
                </div>
            </div>
        </>
    );
}

Primitives.layout = {
    breadcrumbs: [{ title: 'Primitives', href: '/_primitives' }],
};

function Dialogs() {
    const confirm = useConfirm();
    const prompt = usePrompt();
    const alert = useAlert();
    const [answer, setAnswer] = useState('–');

    return (
        <Panel>
            <PanelHeader title="Dialogs" badge="useConfirm" />
            <PanelBody className="flex flex-col items-start gap-3">
                <div className="flex flex-wrap gap-2">
                    <Button
                        type="button"
                        onClick={() =>
                            confirm('Duplicate this project?').then((ok) =>
                                setAnswer(ok ? 'confirmed' : 'cancelled'),
                            )
                        }
                    >
                        Confirm
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        onClick={() =>
                            confirm('Delete this project?', {
                                danger: true,
                            }).then((ok) =>
                                setAnswer(ok ? 'deleted' : 'cancelled'),
                            )
                        }
                    >
                        Delete
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() =>
                            prompt('New name', 'Project').then((value) =>
                                setAnswer(value ?? 'cancelled'),
                            )
                        }
                    >
                        Prompt
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        onClick={() =>
                            alert('This project is locked.').then(() =>
                                setAnswer('read'),
                            )
                        }
                    >
                        Alert
                    </Button>
                </div>
                <output className="text-sm text-brand-muted">{answer}</output>
            </PanelBody>
        </Panel>
    );
}

function Toasts() {
    return (
        <Panel>
            <PanelHeader title="Toasts" badge="toast()" />
            <PanelBody className="flex flex-wrap gap-2">
                <Button type="button" onClick={() => toast('Project loaded')}>
                    ℹ Info
                </Button>
                <Button type="button" onClick={() => toast('Saved', 'ok')}>
                    ✓ Success
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    onClick={() => toast('Saving failed', 'err')}
                >
                    ⚠ Error
                </Button>
            </PanelBody>
        </Panel>
    );
}

function Failing() {
    const [broken, setBroken] = useState(false);

    return (
        <Panel>
            <PanelHeader title="A failing view" badge="ViewErrorBoundary" />
            <PanelBody className="flex flex-col items-start gap-3">
                <Button type="button" onClick={() => setBroken(true)}>
                    Make the view fail
                </Button>
                <ViewErrorBoundary view="Primitives">
                    {broken ? <Broken /> : <p className="text-sm">All good.</p>}
                </ViewErrorBoundary>
            </PanelBody>
        </Panel>
    );
}

function Broken(): never {
    throw new Error('A deliberate failure');
}

function Fields() {
    const [hours, setHours] = useState<number | null>(8);
    const [rate, setRate] = useState<number | null>(0.3);
    const [title, setTitle] = useState(
        'A long title that wraps onto a second line, so the field grows with it instead of scrolling',
    );
    const [optional, setOptional] = useState(false);

    return (
        <Panel>
            <PanelHeader title="Fields" />
            <PanelBody className="flex flex-col gap-3">
                <label className="text-sm">
                    Hours
                    <NumberInput value={hours} onValueChange={setHours} />
                </label>
                <label className="text-sm">
                    Rate (two decimals)
                    <NumberInput
                        value={rate}
                        decimals={2}
                        onValueChange={setRate}
                    />
                </label>
                <label className="text-sm">
                    Title
                    <AutoGrowTextarea
                        value={title}
                        onChange={(event) => setTitle(event.target.value)}
                    />
                </label>
                <span
                    className="cursor-pointer self-start rounded-brand text-sm outline-none focus-visible:shadow-focus"
                    {...activatableProps(
                        'checkbox',
                        () => setOptional((value) => !value),
                        { checked: optional },
                    )}
                >
                    {optional ? '☑' : '☐'} optional (activatableProps)
                </span>
                <p className="brand-figure text-sm text-brand-figure">
                    {hours ?? '–'} h × {rate ?? '–'}
                </p>
            </PanelBody>
        </Panel>
    );
}

function History() {
    const [note, setNote] = useState({ text: '' });
    const history = useUndoRedo(note, setNote);

    useUndoRedoShortcuts(history, true);

    return (
        <Panel>
            <PanelHeader title="Undo / redo" badge="⌘Z · ⇧⌘Z" />
            <PanelBody className="flex flex-col gap-3">
                <input
                    aria-label="Note"
                    className="w-full rounded-brand border border-brand-line bg-brand-card px-3 py-2 outline-none focus-visible:shadow-focus"
                    value={note.text}
                    onChange={(event) => {
                        const next = { text: event.target.value };
                        setNote(next);
                        history.record(next);
                    }}
                />
                <div className="flex gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        disabled={!history.canUndo}
                        onClick={history.undo}
                        aria-label="Undo"
                    >
                        ↶
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={!history.canRedo}
                        onClick={history.redo}
                        aria-label="Redo"
                    >
                        ↷
                    </Button>
                </div>
            </PanelBody>
        </Panel>
    );
}

function Reordering() {
    const [rows, setRows] = useState([
        { id: 'a', label: 'Kick-off' },
        { id: 'b', label: 'Design' },
        { id: 'c', label: 'Build' },
        { id: 'd', label: 'Review' },
    ]);

    return (
        <Panel>
            <PanelHeader title="Reordering" badge="SortableList" />
            <PanelBody>
                <SortableList
                    items={rows}
                    getId={(row) => row.id}
                    onReorder={setRows}
                    className="flex flex-col gap-1"
                >
                    {(row) => (
                        <span className="flex-1 text-sm">{row.label}</span>
                    )}
                </SortableList>
            </PanelBody>
        </Panel>
    );
}

function Surfaces() {
    const [rows, setRows] = useState(['First row', 'Second row']);

    return (
        <Panel>
            <PanelHeader title="Panel" badge="badge">
                <span className="text-xs text-brand-faint">header slot</span>
            </PanelHeader>
            <PanelBody className="flex flex-col gap-2">
                <GroupRule label="Group" count={rows.length} />
                {rows.map((row) => (
                    <div
                        key={row}
                        className="flex items-center gap-2 border-b border-brand-line-soft pb-2 text-sm"
                    >
                        <span className="flex-1">{row}</span>
                        <DeleteButton
                            aria-label={`Delete ${row}`}
                            onClick={() =>
                                setRows((was) =>
                                    was.filter((each) => each !== row),
                                )
                            }
                        />
                    </div>
                ))}
                <AddButton
                    className="self-start"
                    onClick={() =>
                        setRows((was) => [...was, `Row ${was.length + 1}`])
                    }
                >
                    + Row
                </AddButton>
            </PanelBody>
        </Panel>
    );
}

function Paging() {
    const [page, setPage] = useState(2);

    return (
        <Panel>
            <PanelHeader title="Paging" badge="ListPager" />
            <PanelBody>
                <ListPager page={page} lastPage={5} onPageChange={setPage} />
            </PanelBody>
        </Panel>
    );
}
