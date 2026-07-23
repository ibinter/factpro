<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $gateways = [
            [
                'gateway' => 'wave_ci',
                'is_active' => false,
                'config' => null,
                'supported_countries' => json_encode(['CI', 'SN', 'ML', 'BF', 'GN', 'UG', 'TZ']),
                'supported_currencies' => json_encode(['XOF', 'UGX', 'TZS']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gateway' => 'mtn_momo',
                'is_active' => false,
                'config' => null,
                'supported_countries' => json_encode(['CI', 'GH', 'CM', 'BJ', 'RW', 'UG', 'ZM', 'ZW']),
                'supported_currencies' => json_encode(['XOF', 'GHS', 'XAF', 'RWF', 'UGX']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gateway' => 'orange_money',
                'is_active' => false,
                'config' => null,
                'supported_countries' => json_encode(['CI', 'SN', 'ML', 'BF', 'CM', 'MG', 'GN']),
                'supported_currencies' => json_encode(['XOF', 'XAF', 'MGA']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gateway' => 'paydunya',
                'is_active' => false,
                'config' => null,
                'supported_countries' => json_encode(['SN', 'CI', 'BJ', 'TG', 'GN', 'ML', 'BF']),
                'supported_currencies' => json_encode(['XOF']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gateway' => 'stripe',
                'is_active' => false,
                'config' => null,
                'supported_countries' => json_encode(['*']),
                'supported_currencies' => json_encode(['USD', 'EUR', 'GBP', 'XOF']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($gateways as $gw) {
            DB::table('gateway_configs')->updateOrInsert(
                ['gateway' => $gw['gateway']],
                $gw
            );
        }
    }

    public function down(): void {
        DB::table('gateway_configs')->whereIn('gateway', ['wave_ci', 'mtn_momo', 'orange_money', 'paydunya', 'stripe'])->delete();
    }
};
