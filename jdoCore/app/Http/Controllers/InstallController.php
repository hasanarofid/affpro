<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Setting;

class InstallController extends Controller
{
    // ══════════════════════════════════════════════
    //  STEP 0 — Welcome / Requirements Check
    // ══════════════════════════════════════════════
    public function index()
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }

        $requirements = $this->checkRequirements();
        $allPassed = collect($requirements)->every(fn($r) => $r['status']);

        $permissions = $this->checkPermissions();
        $allPermissions = collect($permissions)->every(fn($p) => $p['writable']);

        return view('install.welcome', compact('requirements', 'allPassed', 'permissions', 'allPermissions'));
    }

    // ══════════════════════════════════════════════
    //  STEP 1 — License Validation
    // ══════════════════════════════════════════════
    public function licenseForm()
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }
        return view('install.license');
    }

    public function licenseValidate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $licenseKey = $request->license_key;
        $domain = $request->getHost();

        try {
            $devstoreUrl = rtrim($request->input('devstore_url', 'https://store.dapurkode.com'), '/') . '/api';

            $response = Http::withoutVerifying()
                ->timeout(15)
                ->post("{$devstoreUrl}/license/validate", [
                    'license_key' => $licenseKey,
                    'domain' => $domain,
                    'platform' => 'Jadiorder',
                ]);

            $result = $response->json();

            if (isset($result['valid']) && $result['valid']) {
                // Check platform locally
                $productName = strtolower($result['data']['platform'] ?? $result['data']['product'] ?? '');
                if ($productName && strpos($productName, 'jadiorder') === false) {
                    return back()->with('error', 'License Key ini tidak diperuntukkan bagi platform Jadiorder.')->withInput();
                }

                // Store license in session for next steps
                session([
                    'install_license_key' => $licenseKey,
                    'install_license_data' => $result['data'] ?? [],
                    'install_devstore_url' => $request->input('devstore_url', 'https://store.dapurkode.com'),
                ]);
                return redirect('/install/database');
            }

            return back()->with('error', $result['message'] ?? 'Lisensi tidak valid atau tidak untuk platform Jadiorder.')->withInput();
        } catch (\Exception $e) {
            // Allow skip if server unreachable (offline install)
            session([
                'install_license_key' => $licenseKey,
                'install_license_data' => [],
                'install_devstore_url' => $request->input('devstore_url', 'https://store.dapurkode.com'),
            ]);

            return redirect('/install/database')
                ->with('warning', 'Server lisensi tidak dapat dihubungi. Instalasi dilanjutkan secara offline. Pastikan License Key valid.');
        }
    }

    // ══════════════════════════════════════════════
    //  STEP 2 — Database Configuration
    // ══════════════════════════════════════════════
    public function databaseForm()
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }
        if (!session('install_license_key')) {
            return redirect('/install/license')->with('error', 'Silakan validasi lisensi terlebih dahulu.');
        }
        return view('install.database');
    }

    public function databaseSetup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Test database connection
        try {
            $pdo = new \PDO(
                "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_database}",
                $request->db_username,
                $request->db_password ?? ''
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            return back()->with('error', 'Koneksi database gagal: ' . $e->getMessage())->withInput();
        }

        session([
            'install_db' => [
                'host' => $request->db_host,
                'port' => $request->db_port,
                'database' => $request->db_database,
                'username' => $request->db_username,
                'password' => $request->db_password ?? '',
            ]
        ]);

        return redirect('/install/store');
    }

    // ══════════════════════════════════════════════
    //  STEP 3 — Store & Admin Setup
    // ══════════════════════════════════════════════
    public function storeForm()
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }
        if (!session('install_db')) {
            return redirect('/install/database')->with('error', 'Konfigurasi database terlebih dahulu.');
        }
        return view('install.store');
    }

    public function storeSetup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'store_url' => 'required|url|max:500',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:6|confirmed',
            'store_logo' => 'nullable|image|max:2048',
        ], [
            'admin_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('store_logo')) {
            $file = $request->file('store_logo');
            $filename = time() . '_logo_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/settings/logo'), $filename);
            $logoPath = 'uploads/settings/logo/' . $filename;
        }

        session([
            'install_store' => [
                'store_name' => $request->store_name,
                'store_url' => rtrim($request->store_url, '/'),
                'admin_name' => $request->admin_name,
                'admin_email' => $request->admin_email,
                'admin_password' => $request->admin_password,
                'store_logo' => $logoPath,
            ]
        ]);

        return redirect('/install/finish');
    }

    // ══════════════════════════════════════════════
    //  STEP 4 — Final Confirmation & Install
    // ══════════════════════════════════════════════
    public function finishForm()
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }
        if (!session('install_store') || !session('install_db') || !session('install_license_key')) {
            return redirect('/install')->with('error', 'Lengkapi semua langkah instalasi.');
        }

        $db = session('install_db');
        $store = session('install_store');
        $license = session('install_license_key');

        return view('install.finish', compact('db', 'store', 'license'));
    }

    public function runInstall(Request $request)
    {
        if (file_exists(storage_path('installed'))) {
            return response()->json(['success' => false, 'message' => 'Aplikasi sudah terinstall.']);
        }

        $db = session('install_db');
        $store = session('install_store');
        $licenseKey = session('install_license_key');
        $devstoreUrl = session('install_devstore_url', 'https://store.dapurkode.com');

        if (!$db || !$store || !$licenseKey) {
            return response()->json(['success' => false, 'message' => 'Data instalasi tidak lengkap. Ulangi proses dari awal.']);
        }

        try {
            // STEP 1: Generate .env file
            $this->generateEnvFile($db, $store, $licenseKey, $devstoreUrl);

            // Reload configs so the new .env values are used
            // Note: We need to manually set the DB config for this request since .env is read at boot time
            config([
                'database.connections.mysql.host' => $db['host'],
                'database.connections.mysql.port' => $db['port'],
                'database.connections.mysql.database' => $db['database'],
                'database.connections.mysql.username' => $db['username'],
                'database.connections.mysql.password' => $db['password'],
                'database.default' => 'mysql',
            ]);

            // Purge and reconnect
            DB::purge('mysql');
            DB::reconnect('mysql');

            // STEP 2: Generate app key
            Artisan::call('key:generate', ['--force' => true]);

            // STEP 3: Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // STEP 4: Run seeders
            Artisan::call('db:seed', ['--force' => true]);

            // STEP 5: Update admin account with user input
            $admin = User::where('email', 'admin@jadiorder.com')->first();
            if ($admin) {
                $admin->update([
                    'name' => $store['admin_name'],
                    'email' => $store['admin_email'],
                    'password' => Hash::make($store['admin_password']),
                ]);
            } else {
                // fallback: create fresh admin
                $admin = User::create([
                    'name' => $store['admin_name'],
                    'email' => $store['admin_email'],
                    'password' => Hash::make($store['admin_password']),
                    'is_active' => true,
                ]);
                $admin->assignRole('superadmin');
            }

            // STEP 6: Update settings
            Setting::setValue('store_name', $store['store_name'], 'general', 'string');
            if (!empty($store['store_logo'])) {
                Setting::setValue('store_logo', $store['store_logo'], 'general', 'string');
            }

            // Save DevStore license
            Setting::setValue('devstore_license_key', $licenseKey, 'general', 'string');
            Setting::setValue('devstore_url', $devstoreUrl, 'general', 'string');

            // STEP 7: Storage link
            Artisan::call('storage:link', ['--force' => true]);

            // STEP 8: Clear caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            // STEP 9: Mark as installed
            File::put(storage_path('installed'), json_encode([
                'installed_at' => now()->toIso8601String(),
                'version' => config('app.version', '1.0.0'),
                'domain' => request()->getHost(),
                'license_key' => substr($licenseKey, 0, 8) . '****',
            ]));

            // Clear install session data
            session()->forget(['install_license_key', 'install_license_data', 'install_devstore_url', 'install_db', 'install_store']);

            return response()->json(['success' => true, 'message' => 'Instalasi berhasil!']);

        } catch (\Exception $e) {
            Log::error('Installation error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Instalasi gagal: ' . $e->getMessage()]);
        }
    }

    // ══════════════════════════════════════════════
    //  SUCCESS PAGE
    // ══════════════════════════════════════════════
    public function success()
    {
        if (!file_exists(storage_path('installed'))) {
            return redirect('/install');
        }
        return view('install.success');
    }

    // ══════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════

    private function generateEnvFile(array $db, array $store, string $licenseKey, string $devstoreUrl): void
    {
        $appKey = 'base64:' . base64_encode(random_bytes(32));

        $env = <<<ENV
APP_NAME="{$store['store_name']}"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_URL={$store['store_url']}

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST={$db['host']}
DB_PORT={$db['port']}
DB_DATABASE={$db['database']}
DB_USERNAME={$db['username']}
DB_PASSWORD="{$db['password']}"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=file

MAIL_MAILER=log

DEVSTORE_URL={$devstoreUrl}
DEVSTORE_LICENSE_KEY={$licenseKey}
ENV;

        File::put(base_path('.env'), $env);
    }

    private function checkRequirements(): array
    {
        return [
            [
                'name' => 'PHP >= 8.2',
                'status' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'current' => PHP_VERSION,
            ],
            [
                'name' => 'PDO Extension',
                'status' => extension_loaded('pdo'),
                'current' => extension_loaded('pdo') ? 'Installed' : 'Not Installed',
            ],
            [
                'name' => 'PDO MySQL',
                'status' => extension_loaded('pdo_mysql'),
                'current' => extension_loaded('pdo_mysql') ? 'Installed' : 'Not Installed',
            ],
            [
                'name' => 'OpenSSL Extension',
                'status' => extension_loaded('openssl'),
                'current' => extension_loaded('openssl') ? 'Installed' : 'Not Installed',
            ],
            [
                'name' => 'Mbstring Extension',
                'status' => extension_loaded('mbstring'),
                'current' => extension_loaded('mbstring') ? 'Installed' : 'Not Installed',
            ],
            [
                'name' => 'Tokenizer Extension',
                'status' => extension_loaded('tokenizer'),
                'current' => extension_loaded('tokenizer') ? 'Installed' : 'Not Installed',
            ],
            [
                'name' => 'JSON Extension',
                'status' => extension_loaded('json'),
                'current' => extension_loaded('json') ? 'Installed' : 'Not Installed',
            ],
            [
                'name' => 'cURL Extension',
                'status' => extension_loaded('curl'),
                'current' => extension_loaded('curl') ? 'Installed' : 'Not Installed',
            ],
            [
                'name' => 'Fileinfo Extension',
                'status' => extension_loaded('fileinfo'),
                'current' => extension_loaded('fileinfo') ? 'Installed' : 'Not Installed',
            ],
            [
                'name' => 'GD / Imagick Extension',
                'status' => extension_loaded('gd') || extension_loaded('imagick'),
                'current' => extension_loaded('gd') ? 'GD Installed' : (extension_loaded('imagick') ? 'Imagick Installed' : 'Not Installed'),
            ],
        ];
    }

    private function checkPermissions(): array
    {
        $dirs = [
            storage_path(),
            storage_path('app'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
            public_path('uploads'),
        ];

        $result = [];
        foreach ($dirs as $dir) {
            if (!File::isDirectory($dir)) {
                @File::makeDirectory($dir, 0775, true);
            }
            $result[] = [
                'path' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $dir),
                'writable' => File::isWritable($dir),
            ];
        }

        // Also check .env writability
        $envPath = base_path('.env');
        $envExists = File::exists($envPath);
        $result[] = [
            'path' => '.env (file)',
            'writable' => $envExists ? File::isWritable($envPath) : File::isWritable(base_path()),
        ];

        return $result;
    }
}
