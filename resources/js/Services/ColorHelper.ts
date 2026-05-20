export type RGB = {
    red: number;
    green: number;
    blue: number;
};

export function contrastingForeground(
    background: string | RGB,
    foreground: string | RGB,
    threshold = 4.5,
) {
    if (isContrastHighEnough(background, foreground, threshold))
        return foreground;
    return contrastingColor(background);
}

export function isContrastHighEnough(
    background: string | RGB,
    foreground: string | RGB,
    threshold = 4.5,
) {
    const bg =
        typeof background === 'string' ? hexToRGBArray(background) : background;
    const fg =
        typeof foreground === 'string' ? hexToRGBArray(foreground) : foreground;

    const lumaBg = relativeLuminance(bg);
    const lumaFg = relativeLuminance(fg);

    const contrastRatio =
        (Math.max(lumaBg, lumaFg) + 0.05) / (Math.min(lumaBg, lumaFg) + 0.05);

    return contrastRatio >= threshold;
}

function srgbToLinear(channel: number): number {
    const s = channel / 255;
    return s <= 0.03928 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
}

function relativeLuminance(rgb: RGB): number {
    const r = srgbToLinear(rgb.red);
    const g = srgbToLinear(rgb.green);
    const b = srgbToLinear(rgb.blue);
    return 0.2126 * r + 0.7152 * g + 0.0722 * b; // result in 0..1
}

export function contrastingColor(color: string | RGB) {
    return luma(color) >= 0.5 ? '#000' : '#fff';
}

function luma(color: string | RGB): number {
    const rgb = typeof color === 'string' ? hexToRGBArray(color) : color;
    return relativeLuminance(rgb);
}

function hexToRGBArray(color: string): RGB {
    if (color.startsWith('#')) color = color.substring(1);
    if (color.length === 3)
        color =
            color.charAt(0) +
            color.charAt(0) +
            color.charAt(1) +
            color.charAt(1) +
            color.charAt(2) +
            color.charAt(2);
    else if (color.length !== 6) throw new Error('Invalid hex color: ' + color);

    const red = Number.parseInt(color.substring(0, 2), 16);
    const green = Number.parseInt(color.substring(2, 4), 16);
    const blue = Number.parseInt(color.substring(4, 6), 16);

    return { red, green, blue };
}

export function generateColorFromString(str: string): string {
    let hash = 0;
    str.split('').forEach((char) => {
        const value = char.codePointAt(0) || 0;
        hash = value + ((hash << 5) - hash);
    });
    let colour = '';
    for (let i = 0; i < 3; i++) {
        const value = (hash >> (i * 8)) & 0xff;
        colour += value.toString(16).padStart(2, '0');
    }
    return colour;
}
