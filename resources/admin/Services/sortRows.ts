export type SortDirection = 'asc' | 'desc';

/** Returns a sorted copy. Numbers compare numerically, everything else as text; empty values always go last. */
export function sortRows<T extends Record<string, unknown>>(
    rows: T[],
    key: string,
    direction: SortDirection,
): T[] {
    const factor = direction === 'asc' ? 1 : -1;
    return [...rows].sort((a, b) => {
        const x = a[key];
        const y = b[key];
        if (x == null || x === '') return y == null || y === '' ? 0 : 1;
        if (y == null || y === '') return -1;
        if (typeof x === 'number' && typeof y === 'number') {
            return (x - y) * factor;
        }
        return (
            String(x).localeCompare(String(y), undefined, { numeric: true }) *
            factor
        );
    });
}
