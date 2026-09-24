import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';
import {
    matchKickerRows,
    readMatchdayRanking,
    type KickerMatch,
} from './kicker-paste';

type KickerPasteDialogProps = {
    players: { id: number; name: string; alias: string }[];
    onApply: (match: KickerMatch) => void;
};

/*
 * MD-05 — an addition to entering points by hand: the copied league page
 * goes in here, and its matchday ranking fills the points fields.
 */
export function KickerPasteDialog({
    players,
    onApply,
}: KickerPasteDialogProps) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);
    const [text, setText] = useState('');
    const [error, setError] = useState<string | undefined>();

    const apply = () => {
        const rows = readMatchdayRanking(text);

        if (rows === null) {
            setError(
                t(
                    'The text holds no matchday ranking. Copy the whole league page from the kicker Manager.',
                ),
            );

            return;
        }

        onApply(matchKickerRows(rows, players));
        setOpen(false);
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(next) => {
                setOpen(next);
                setText('');
                setError(undefined);
            }}
        >
            <DialogTrigger asChild>
                <Button
                    type="button"
                    variant="outline"
                    className="justify-self-start max-phone:h-11"
                >
                    {t('Paste from kicker')}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>{t('Paste from kicker')}</DialogTitle>
                <DialogDescription>
                    {t(
                        'Open your league in the kicker Manager on this matchday, select the whole page, copy it and paste it here.',
                    )}
                </DialogDescription>

                <div className="grid gap-2">
                    <Label htmlFor="kicker-paste" className="sr-only">
                        {t('Copied league page')}
                    </Label>
                    <textarea
                        id="kicker-paste"
                        rows={8}
                        value={text}
                        onChange={(event) => {
                            setText(event.target.value);
                            setError(undefined);
                        }}
                        className="w-full resize-y rounded-brand border border-brand-line bg-brand-card px-3 py-2 text-sm outline-none focus-visible:shadow-focus"
                    />
                    <InputError message={error} />
                </div>

                <DialogFooter className="gap-2">
                    <DialogClose asChild>
                        <Button type="button" variant="ghost">
                            {t('Cancel')}
                        </Button>
                    </DialogClose>
                    <Button
                        type="button"
                        onClick={apply}
                        disabled={text.trim() === ''}
                    >
                        {t('Apply')}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
