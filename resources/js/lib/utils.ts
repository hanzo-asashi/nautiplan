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
): string {
    if (value === null || value === undefined) {
        return 'Rp 0';
    }

    const num = typeof value === 'string' ? parseFloat(value) : value;

    if (isNaN(num)) {
        return 'Rp 0';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
}
