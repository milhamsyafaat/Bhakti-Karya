<?php

namespace App\Services;

use App\Models\ZonaOngkir;

/**
 * Perhitungan ongkos kirim.
 */
class OngkirService
{
    /**
     * @return array{ongkir:float, gratis:bool, kurang:float, zona:?array, jarak_km:float}
     */
    public static function hitung(float $subtotal, ?string $zonaId, string $metodeKirim = 'antar'): array
    {
        $zona = $zonaId ? ZonaOngkir::find($zonaId) : null;
        $jarak = $zona ? (float) $zona['jarak_km'] : 0.0;

        // Ambil di toko = tanpa ongkir
        if ($metodeKirim === 'ambil') {
            return ['ongkir' => 0.0, 'gratis' => true, 'kurang' => 0.0, 'zona' => $zona, 'jarak_km' => 0.0];
        }

        $tarif = $zona ? (float) $zona['tarif'] : 30000.0;

        $radiusGratis = (float) \App\Models\Pengaturan::get('radius_gratis_km', 2);
        $minGratis = (float) \App\Models\Pengaturan::get('min_belanja_gratis_ongkir', 100000);

        $gratis = ($jarak > 0 && $jarak <= $radiusGratis) || ($subtotal >= $minGratis);
        $kurang = (!$gratis && $subtotal < $minGratis) ? max(0, $minGratis - $subtotal) : 0.0;

        return [
            'ongkir'    => $gratis ? 0.0 : $tarif,
            'gratis'    => $gratis,
            'kurang'    => $kurang,
            'zona'      => $zona,
            'jarak_km'  => $jarak,
            'tarif'     => $tarif,
        ];
    }

    public static function labelJam(?string $time): string
    {
        if (!$time) {
            return '-';
        }
        return substr($time, 0, 5);
    }
}
