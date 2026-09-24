/**
 * Plain numbers in the active locale (`useTranslation().locale`). Formatting
 * is Intl's; parsing reads the separators back out of Intl, so what a field
 * shows is always what it accepts.
 */

const decimalCache = new Map<string, string>();

/* The group separator needs no lookup: parsing keeps only digits, the
   decimal separator and a minus, so it falls out with everything else. */
const decimalSeparator = (locale: string): string => {
    const cached = decimalCache.get(locale);

    if (cached !== undefined) {
        return cached;
    }

    const decimal =
        new Intl.NumberFormat(locale)
            .formatToParts(1.5)
            .find((part) => part.type === 'decimal')?.value ?? '.';

    decimalCache.set(locale, decimal);

    return decimal;
};

/** Up to `maxDecimals` decimals; unnecessary ones are dropped. */
export const formatNumber = (
    value: number,
    locale: string,
    maxDecimals = 2,
): string =>
    new Intl.NumberFormat(locale, {
        maximumFractionDigits: maxDecimals,
    }).format(value);

/** Exactly `decimals` decimals, e.g. a rate re-formatted on blur. */
export const formatFixed = (
    value: number,
    locale: string,
    decimals = 2,
): string =>
    new Intl.NumberFormat(locale, {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(value);

/**
 * Parse a number written in `locale`. Group separators and anything else
 * that is not a digit, the decimal separator or a minus are ignored; a
 * leading minus makes it negative. Null when the input holds no digit, or
 * when what is left is not one number (two decimal separators).
 */
export const parseNumber = (input: string, locale: string): number | null => {
    if (!/\d/.test(input)) {
        return null;
    }

    const decimal = decimalSeparator(locale);
    const inClass = decimal.replace(/[\\\]^-]/g, '\\$&');
    const kept = input.replace(new RegExp(`[^\\d${inClass}\\-−]`, 'g'), '');
    const negative = /^[-−]/.test(kept);
    const digits = kept.replace(/[-−]/g, '').replace(decimal, '.');
    const value = Number(digits);

    if (digits === '' || Number.isNaN(value)) {
        return null;
    }

    return negative ? -value : value;
};
