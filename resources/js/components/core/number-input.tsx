import { useCallback, useEffect, useState, type ComponentProps } from 'react';
import { Input } from '@/components/ui/input';
import { useTranslation } from '@/hooks/use-translation';
import { formatFixed, formatNumber, parseNumber } from '@/lib/number';

/*
 * A number field read and written in the active locale's notation. The text
 * is the user's while they type and is only re-formatted on blur: `decimals`
 * fixes the number of decimals then; left unset, unnecessary decimals are
 * dropped.
 *
 * A negative value is clamped to 0 on entry rather than rejected keystroke by
 * keystroke; `allowNegative` turns that off for a signed field.
 */

type NumberInputProps = Omit<
    ComponentProps<typeof Input>,
    'value' | 'onChange' | 'type'
> & {
    value: number | null;
    onValueChange: (value: number | null) => void;
    /** Fixed decimals on blur; unset drops unnecessary ones. */
    decimals?: number;
    allowNegative?: boolean;
};

export function NumberInput({
    value,
    onValueChange,
    decimals,
    allowNegative = false,
    onBlur,
    ...props
}: NumberInputProps) {
    const { locale } = useTranslation();

    const display = useCallback(
        (n: number | null): string =>
            n === null
                ? ''
                : decimals === undefined
                  ? formatNumber(n, locale)
                  : formatFixed(n, locale, decimals),
        [decimals, locale],
    );

    const [text, setText] = useState(() => display(value));

    /* A value changed elsewhere becomes visible; what the user is typing is
       left alone as long as it still means the same number. */
    useEffect(() => {
        setText((current) =>
            parseNumber(current, locale) === value ? current : display(value),
        );
    }, [value, display, locale]);

    return (
        <Input
            {...props}
            type="text"
            inputMode="decimal"
            value={text}
            onChange={(event) => {
                const raw = event.target.value;
                const parsed = parseNumber(raw, locale);

                if (!allowNegative && parsed !== null && parsed < 0) {
                    setText('0');
                    onValueChange(0);

                    return;
                }

                setText(raw);
                onValueChange(parsed);
            }}
            onBlur={(event) => {
                setText(display(parseNumber(text, locale)));
                onBlur?.(event);
            }}
        />
    );
}
