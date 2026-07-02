import type { LinkComponentBaseProps } from '@inertiajs/core';
import type { ClassValue } from 'clsx';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(
    href: NonNullable<LinkComponentBaseProps['href']>,
): string {
    return typeof href === 'string' ? href : href.url;
}

export function formatRupiah(
    value: number | string | null | undefined,
    abbreviate = false,
): string {
    if (value === null || value === undefined) {
        return 'Rp 0';
    }

    const num = typeof value === 'string' ? parseFloat(value) : value;

    if (isNaN(num)) {
        return 'Rp 0';
    }

    if (abbreviate) {
        const isNegative = num < 0;
        const absNum = Math.abs(num);
        const sign = isNegative ? '-' : '';

        if (absNum >= 1e9) {
            const val = parseFloat((absNum / 1e9).toFixed(2))
                .toString()
                .replace('.', ',');

            return `${sign}Rp ${val} M`;
        }

        if (absNum >= 1e6) {
            const val = parseFloat((absNum / 1e6).toFixed(2))
                .toString()
                .replace('.', ',');

            return `${sign}Rp ${val} Jt`;
        }

        if (absNum >= 1e3) {
            const val = parseFloat((absNum / 1e3).toFixed(2))
                .toString()
                .replace('.', ',');

            return `${sign}Rp ${val} Rb`;
        }

        return `${sign}Rp ${absNum}`;
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
}
