<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateArchiveKeys extends Command
{
    protected $signature   = 'archive:generate-keys {--force : Régénère les clés même si elles existent déjà}';
    protected $description = 'Génère la paire de clés RSA-2048 pour l\'archivage légal immuable';

    public function handle(): int
    {
        $keysDir = storage_path('app/keys');

        if (! is_dir($keysDir)) {
            mkdir($keysDir, 0755, true);
        }

        $privatePath = $keysDir . '/archive_private.pem';
        $publicPath  = $keysDir . '/archive_public.pem';

        if (file_exists($privatePath) && ! $this->option('force')) {
            $this->warn('Les clés existent déjà. Utilisez --force pour régénérer.');
            return self::SUCCESS;
        }

        // Assurer que openssl.cnf est trouvable (XAMPP Windows)
        if (PHP_OS_FAMILY === 'Windows' && ! getenv('OPENSSL_CONF')) {
            $candidates = [
                'C:\\xampp\\php\\extras\\openssl\\openssl.cnf',
                'C:\\xampp\\apache\\conf\\openssl.cnf',
            ];
            foreach ($candidates as $candidate) {
                if (file_exists($candidate)) {
                    putenv("OPENSSL_CONF={$candidate}");
                    break;
                }
            }
        }

        $config = [
            'digest_alg'       => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);

        if (! $res) {
            $this->error('Impossible de générer la paire de clés RSA : ' . openssl_error_string());
            return self::FAILURE;
        }

        openssl_pkey_export($res, $privateKey);
        $details   = openssl_pkey_get_details($res);
        $publicKey = $details['key'];

        file_put_contents($privatePath, $privateKey);
        file_put_contents($publicPath, $publicKey);

        // Protéger la clé privée (chmod 600)
        if (PHP_OS_FAMILY !== 'Windows') {
            chmod($privatePath, 0600);
        }

        // Fingerprint SHA-256 de la clé publique
        $fingerprint = hash('sha256', $publicKey);

        // Mettre à jour config/archive.php avec le fingerprint
        $configPath    = config_path('archive.php');
        $configContent = "<?php\n\nreturn [\n    'public_key_fingerprint' => '{$fingerprint}',\n];\n";
        file_put_contents($configPath, $configContent);

        $this->info('Clés RSA-2048 générées avec succès.');
        $this->line("  Clé privée : {$privatePath}");
        $this->line("  Clé publique : {$publicPath}");
        $this->line("  Fingerprint SHA-256 : {$fingerprint}");

        return self::SUCCESS;
    }
}
