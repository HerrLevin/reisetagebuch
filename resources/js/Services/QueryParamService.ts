import { LocationQueryValue } from 'vue-router';

export function normalizeQueryParam(
    param: string | LocationQueryValue[] | LocationQueryValue | undefined,
): string | undefined {
    if (Array.isArray(param)) {
        return param[0] || undefined;
    }
    return param || undefined;
}
