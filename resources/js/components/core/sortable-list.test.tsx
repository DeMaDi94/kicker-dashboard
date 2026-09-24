import { render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { SortableGroup, SortableList } from './sortable-list';

type Row = { id: string; label: string };

const rows: Row[] = [
    { id: 'a', label: 'Kick-off meeting' },
    { id: 'b', label: 'Final report' },
];

const renderList = (disabled = false) =>
    render(
        <SortableList
            items={rows}
            getId={(row) => row.id}
            onReorder={vi.fn()}
            disabled={disabled}
        >
            {(row) => <span>{row.label}</span>}
        </SortableList>,
    );

/*
 * The drag starts at an explicit ⠿ handle, never at the row, so the text
 * around it stays selectable. The pointer drag itself belongs to @dnd-kit and
 * is covered end to end; what is asserted here is that every row offers the
 * handle and nothing else is a drag source.
 */
describe('reordering from a handle', () => {
    it('gives every row a ⠿ handle', () => {
        renderList();

        const handles = screen.getAllByRole('button', { name: 'Move row' });

        expect(handles).toHaveLength(2);
        expect(handles[0]).toHaveTextContent('⠿');
    });

    it('leaves the row itself out of the drag', () => {
        renderList();

        const row = screen.getByText('Kick-off meeting').closest('li');

        expect(row).not.toHaveAttribute('role', 'button');
        expect(row).not.toHaveAttribute('draggable');
    });

    it('cannot be dragged while the list is disabled', () => {
        renderList(true);

        expect(
            screen.getAllByRole('button', { name: 'Move row' })[0],
        ).toBeDisabled();
    });

    it('renders the lists of a group with a handle per row', () => {
        render(
            <SortableGroup onMove={vi.fn()}>
                <SortableList items={rows.slice(0, 1)} getId={(row) => row.id}>
                    {(row) => <span>{row.label}</span>}
                </SortableList>
                <SortableList items={rows.slice(1)} getId={(row) => row.id}>
                    {(row) => <span>{row.label}</span>}
                </SortableList>
            </SortableGroup>,
        );

        expect(
            screen.getAllByRole('button', { name: 'Move row' }),
        ).toHaveLength(2);
    });
});
