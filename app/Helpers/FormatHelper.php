<?php

namespace App\Helpers;

use Carbon\Carbon;

class FormatHelper
{
    /**
     * Konversi angka nominal menjadi ejaan kalimat Terbilang Bahasa Indonesia
     */
    public static function terbilang(float $nilai): string
    {
        $nilai = abs($nilai);
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $temp = '';

        if ($nilai < 12) {
            $temp = ' '.$huruf[(int) $nilai];
        } elseif ($nilai < 20) {
            $temp = self::terbilang($nilai - 10).' belas';
        } elseif ($nilai < 100) {
            $temp = self::terbilang(floor($nilai / 10)).' puluh '.self::terbilang($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = ' seratus '.self::terbilang($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = self::terbilang(floor($nilai / 100)).' ratus '.self::terbilang($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = ' seribu '.self::terbilang($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = self::terbilang(floor($nilai / 1000)).' ribu '.self::terbilang($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = self::terbilang(floor($nilai / 1000000)).' juta '.self::terbilang($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $temp = self::terbilang(floor($nilai / 1000000000)).' miliar '.self::terbilang(fmod($nilai, 1000000000));
        } elseif ($nilai < 1000000000000000) {
            $temp = self::terbilang(floor($nilai / 1000000000000)).' triliun '.self::terbilang(fmod($nilai, 1000000000000));
        }

        return trim($temp);
    }

    /**
     * Format angka ke Rupiah
     */
    public static function rupiah(float $nilai, bool $withSymbol = true): string
    {
        $formatted = number_format($nilai, 2, ',', '.');

        return $withSymbol ? 'Rp '.$formatted : $formatted;
    }

    /**
     * Konversi hari bahasa inggris ke bahasa indonesia
     */
    public static function hari(string $dayName): string
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        return $days[$dayName] ?? $dayName;
    }

    /**
     * Konversi bulan angka/inggris ke bahasa indonesia
     */
    public static function bulan(string|int $month): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if (is_numeric($month)) {
            return $months[(int) $month] ?? (string) $month;
        }

        $timestamp = strtotime((string) $month);
        if ($timestamp === false) {
            return (string) $month;
        }

        $monthInt = (int) date('n', $timestamp);

        return $months[$monthInt];
    }

    /**
     * Format tanggal lengkap ke bahasa indonesia (Contoh: 17 Agustus 1945)
     */
    public static function tanggal(Carbon|string $date): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->day.' '.self::bulan($date->month).' '.$date->year;
    }
}
