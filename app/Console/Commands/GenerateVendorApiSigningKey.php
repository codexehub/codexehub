<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Generates an RSA keypair for Vendor API response signing (SRS §61/§63,
 * APISEC-003) and writes it to `storage/keys/vendor-api/{key-id}-{private,public}.pem`
 * — the default paths `config/vendor_api.php` looks for a key id under. Used
 * for the initial key and for every later rotation (generate a new key id,
 * add it to `config/vendor_api.php`'s `response_signing.keys` map, then flip
 * `response_signing.active_key_id` once it's deployed).
 */
class GenerateVendorApiSigningKey extends Command
{
    protected $signature = 'vendor-api:generate-signing-key {--key-id=v1 : Identifier for this key, e.g. v1, v2} {--force : Overwrite existing key files}';

    protected $description = 'Generate an RSA keypair for Vendor API response signing';

    public function handle(): int
    {
        $keyId = (string) $this->option('key-id');

        if ($keyId === '' || ! preg_match('/^[a-z0-9\-]+$/i', $keyId)) {
            $this->error('--key-id must be a non-empty alphanumeric/dash string.');

            return self::FAILURE;
        }

        $directory = storage_path('keys/vendor-api');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $privatePath = "{$directory}/{$keyId}-private.pem";
        $publicPath = "{$directory}/{$keyId}-public.pem";

        if (! $this->option('force') && (is_file($privatePath) || is_file($publicPath))) {
            $this->error("Key files for '{$keyId}' already exist. Pass --force to overwrite (this invalidates anything relying on the old key).");

            return self::FAILURE;
        }

        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            $this->error('OpenSSL key generation failed: '.(openssl_error_string() ?: 'unknown error'));
            $this->line('On Windows/XAMPP this is usually a missing default openssl.cnf — set the OPENSSL_CONF');
            $this->line('environment variable to a valid one (e.g. C:\\xampp\\php\\extras\\openssl\\openssl.cnf) and retry.');

            return self::FAILURE;
        }

        openssl_pkey_export($resource, $privateKeyPem);
        $publicKeyPem = openssl_pkey_get_details($resource)['key'];

        file_put_contents($privatePath, $privateKeyPem);
        chmod($privatePath, 0600);
        file_put_contents($publicPath, $publicKeyPem);

        $this->info("Generated Vendor API signing key '{$keyId}':");
        $this->line("  private: {$privatePath} (0600, keep secret, never commit)");
        $this->line("  public:  {$publicPath}");
        $this->newLine();
        $this->line("Add/confirm an entry for '{$keyId}' in config/vendor_api.php's response_signing.keys map,");
        $this->line("then set VENDOR_API_RESPONSE_SIGNING_KEY_ID={$keyId} once ready to activate it.");

        return self::SUCCESS;
    }
}
