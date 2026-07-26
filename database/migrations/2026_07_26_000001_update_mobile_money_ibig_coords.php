<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Mise à jour des vrais numéros Mobile Money IBIG SARL (section 19.5).
 * Remplace les numéros fictifs par les coordonnées officielles
 * et insère Moov Money si absent.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Orange Money : +225 07 78 88 25 92
        DB::table('payment_method_configs')
            ->where('type', 'mobile_money')
            ->where('operator', 'orange')
            ->update(['account_number' => '+225 07 78 88 25 92']);

        // Wave : +225 07 78 88 25 92
        DB::table('payment_method_configs')
            ->where('type', 'mobile_money')
            ->where('operator', 'wave')
            ->update(['account_number' => '+225 07 78 88 25 92']);

        // MTN MoMo : +225 05 55 05 99 01
        DB::table('payment_method_configs')
            ->where('type', 'mobile_money')
            ->where('operator', 'mtn')
            ->update(['account_number' => '+225 05 55 05 99 01']);

        // Moov Money : insérer si absent
        $moovExists = DB::table('payment_method_configs')
            ->where('type', 'mobile_money')
            ->where('operator', 'moov')
            ->exists();

        if (! $moovExists) {
            DB::table('payment_method_configs')->insert([
                'type'           => 'mobile_money',
                'country'        => 'CI',
                'label'          => 'Moov Money Côte d\'Ivoire',
                'operator'       => 'moov',
                'account_number' => '+225 01 53 59 55 44',
                'account_holder' => 'IBIG SARL',
                'currency'       => 'XOF',
                'instructions'   => 'Envoyez via Moov Money puis conservez le reçu de transaction.',
                'is_active'      => true,
                'sort_order'     => 4,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Restauration des anciens numéros fictifs (rollback)
        DB::table('payment_method_configs')
            ->where('type', 'mobile_money')
            ->where('operator', 'orange')
            ->update(['account_number' => '+225 07 07 00 11 22']);

        DB::table('payment_method_configs')
            ->where('type', 'mobile_money')
            ->where('operator', 'wave')
            ->update(['account_number' => '+225 05 05 99 88 77']);

        DB::table('payment_method_configs')
            ->where('type', 'mobile_money')
            ->where('operator', 'mtn')
            ->update(['account_number' => '+225 05 55 44 33 22']);

        // Supprimer Moov Money seulement s'il a été inséré par cette migration
        DB::table('payment_method_configs')
            ->where('type', 'mobile_money')
            ->where('operator', 'moov')
            ->where('account_number', '+225 01 53 59 55 44')
            ->delete();
    }
};
