<?php

namespace App\Helpers;

use Carbon\Carbon;

class ReportHelper
{
    /**
     * Format number to Rupiah format.
     */
    public static function formatRupiah(float|int|string $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }

    /**
     * Convert number to words in Indonesian (Terbilang).
     */
    public static function terbilang(float|int|string $number): string
    {
        $number = (float) $number;
        $number = abs($number);
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $temp = '';

        if ($number < 12) {
            $temp = ' '.$words[(int) $number];
        } elseif ($number < 20) {
            $temp = self::terbilang($number - 10).' belas';
        } elseif ($number < 100) {
            $temp = self::terbilang($number / 10).' puluh'.self::terbilang($number % 10);
        } elseif ($number < 200) {
            $temp = ' seratus'.self::terbilang($number - 100);
        } elseif ($number < 1000) {
            $temp = self::terbilang($number / 100).' ratus'.self::terbilang($number % 100);
        } elseif ($number < 2000) {
            $temp = ' seribu'.self::terbilang($number - 1000);
        } elseif ($number < 1000000) {
            $temp = self::terbilang($number / 1000).' ribu'.self::terbilang($number % 1000);
        } elseif ($number < 1000000000) {
            $temp = self::terbilang($number / 1000000).' juta'.self::terbilang($number % 1000000);
        } elseif ($number < 1000000000000) {
            $temp = self::terbilang($number / 1000000000).' milyar'.self::terbilang(fmod($number, 1000000000));
        } elseif ($number < 1000000000000000) {
            $temp = self::terbilang($number / 1000000000000).' trilyun'.self::terbilang(fmod($number, 1000000000000));
        }

        return trim($temp);
    }

    /**
     * Format date to Indonesian format (e.g., 17 Agustus 1945).
     */
    public static function formatTanggal(string|Carbon $date): string
    {
        $c = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $c->locale('id');

        return $c->translatedFormat('d F Y');
    }

    /**
     * Format date and time to Indonesian format.
     */
    public static function formatTanggalWaktu(string|Carbon $date): string
    {
        $c = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $c->locale('id');

        return $c->translatedFormat('d F Y H:i');
    }

    /**
     * Get Indonesian day name.
     */
    public static function namaHari(string|Carbon $date): string
    {
        $c = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $c->locale('id');

        return $c->translatedFormat('l');
    }

    /**
     * Get Indonesian month name.
     */
    public static function namaBulan(string|Carbon $date): string
    {
        $c = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $c->locale('id');

        return $c->translatedFormat('F');
    }
}
