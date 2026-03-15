export function formatShortenedNumber(
    value: number | bigint | Intl.StringNumericLiteral,
    locale: string,
): string {
    const formatter = new Intl.NumberFormat(locale, {
        notation: 'compact',
        compactDisplay: 'short',
        maximumFractionDigits: 2,
    });
    return formatter.format(value);
}

export function formatFullNumber(
    value: number | bigint | Intl.StringNumericLiteral,
    locale: string,
): string {
    const formatter = new Intl.NumberFormat(locale, {
        notation: 'standard',
        maximumFractionDigits: 2,
    });
    return formatter.format(value);
}
