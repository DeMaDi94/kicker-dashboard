import {
    DndContext,
    PointerSensor,
    closestCenter,
    useSensor,
    useSensors,
    type DragEndEvent,
} from '@dnd-kit/core';
import {
    SortableContext,
    arrayMove,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { createContext, useContext, type ReactNode } from 'react';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';

/*
 * Reordering is done from an explicit ⠿ handle, the drop target is
 * highlighted, and the surrounding text stays selectable once the drag ends.
 * Dragging from the row itself is what makes a list impossible to select text
 * in; the handle is the fix and is required, not decoration.
 *
 * A reorder stays inside one list, unless the lists sit in one SortableGroup.
 */

/*
 * A row dropped into another list of the same group moves to that list, so
 * the lists of a group drag as one. A list inside a group reports its drags
 * to the group instead of handling them itself.
 */
const SortableGroupContext = createContext(false);

/* A few pixels of movement before a drag starts, so a click on the handle is
   still a click. */
const useDragSensors = () =>
    useSensors(
        useSensor(PointerSensor, { activationConstraint: { distance: 4 } }),
    );

export function SortableGroup({
    onMove,
    children,
}: {
    onMove: (activeId: string, overId: string) => void;
    children: ReactNode;
}) {
    const sensors = useDragSensors();

    const onDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;

        if (!over || active.id === over.id) {
            return;
        }

        onMove(String(active.id), String(over.id));
    };

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragEnd={onDragEnd}
        >
            <SortableGroupContext value={true}>{children}</SortableGroupContext>
        </DndContext>
    );
}

type SortableListProps<T> = {
    items: T[];
    /** Stable id per row; a reorder is reported as the new order. */
    getId: (item: T) => string;
    /** Not used inside a SortableGroup — the group reports the move. */
    onReorder?: (items: T[]) => void;
    children: (item: T) => ReactNode;
    className?: string;
    disabled?: boolean;
};

export function SortableList<T>({
    items,
    getId,
    onReorder,
    children,
    className,
    disabled = false,
}: SortableListProps<T>) {
    const inGroup = useContext(SortableGroupContext);
    const sensors = useDragSensors();

    const onDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;

        if (!over || active.id === over.id) {
            return;
        }

        const from = items.findIndex((item) => getId(item) === active.id);
        const to = items.findIndex((item) => getId(item) === over.id);

        if (from === -1 || to === -1) {
            return;
        }

        onReorder?.(arrayMove(items, from, to));
    };

    const list = (
        <SortableContext
            items={items.map(getId)}
            strategy={verticalListSortingStrategy}
        >
            <ul className={className}>
                {items.map((item) => (
                    <SortableRow
                        key={getId(item)}
                        id={getId(item)}
                        disabled={disabled}
                    >
                        {children(item)}
                    </SortableRow>
                ))}
            </ul>
        </SortableContext>
    );

    if (inGroup) {
        return list;
    }

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragEnd={onDragEnd}
        >
            {list}
        </DndContext>
    );
}

function SortableRow({
    id,
    disabled,
    children,
}: {
    id: string;
    disabled: boolean;
    children: ReactNode;
}) {
    const { t } = useTranslation();
    const {
        attributes,
        listeners,
        setNodeRef,
        setActivatorNodeRef,
        transform,
        transition,
        isDragging,
        isOver,
    } = useSortable({ id, disabled });

    return (
        <li
            ref={setNodeRef}
            style={{ transform: CSS.Transform.toString(transform), transition }}
            data-dragging={isDragging || undefined}
            className={cn(
                'flex items-center gap-2',
                // The drop target is visible while a row hovers over it.
                isOver && !isDragging && 'bg-brand-accent-soft',
            )}
        >
            <button
                type="button"
                ref={setActivatorNodeRef}
                aria-label={t('Move row')}
                disabled={disabled}
                className="cursor-grab rounded-brand px-1 text-xs text-brand-label opacity-60 outline-none focus-visible:shadow-focus disabled:cursor-default disabled:opacity-30"
                {...attributes}
                {...listeners}
            >
                ⠿
            </button>
            {children}
        </li>
    );
}
