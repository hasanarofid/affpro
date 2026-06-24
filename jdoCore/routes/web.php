<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\OrderController;
use App\Http\Controllers\Store\AccountController;
use App\Http\Controllers\Store\AddressController;
use App\Http\Controllers\Store\WalletController as StoreWalletController;
use App\Http\Controllers\Store\BankAccountController;
use App\Http\Controllers\Store\AffiliateController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\InvoiceController;

use App\Http\Controllers\Auth\OtpLoginController;

/*
|--------------------------------------------------------------------------
| Installer Routes (accessible before installation)
|--------------------------------------------------------------------------
*/

Route::prefix('install')->withoutMiddleware(\App\Http\Middleware\CheckInstalled::class)->group(function () {
    Route::get('/', [\App\Http\Controllers\InstallController::class, 'index'])->name('install.welcome');
    Route::get('/license', [\App\Http\Controllers\InstallController::class, 'licenseForm'])->name('install.license');
    Route::post('/license', [\App\Http\Controllers\InstallController::class, 'licenseValidate']);
    Route::get('/database', [\App\Http\Controllers\InstallController::class, 'databaseForm'])->name('install.database');
    Route::post('/database', [\App\Http\Controllers\InstallController::class, 'databaseSetup']);
    Route::get('/store', [\App\Http\Controllers\InstallController::class, 'storeForm'])->name('install.store');
    Route::post('/store', [\App\Http\Controllers\InstallController::class, 'storeSetup']);
    Route::get('/finish', [\App\Http\Controllers\InstallController::class, 'finishForm'])->name('install.finish');
    Route::post('/run', [\App\Http\Controllers\InstallController::class, 'runInstall'])->name('install.run');
    Route::get('/success', [\App\Http\Controllers\InstallController::class, 'success'])->name('install.success');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/login/otp', [OtpLoginController::class, 'showForm'])->name('otp.login');
Route::post('/login/otp/send', [OtpLoginController::class, 'sendOtp'])->name('otp.send');
Route::post('/login/otp/verify', [OtpLoginController::class, 'verifyOtp'])->name('otp.verify');

/*
|--------------------------------------------------------------------------
| Storefront Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');



// Products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{slug}/quick-view', [ProductController::class, 'quickView'])->name('products.quick-view');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::post('/checkout/apply-voucher', [CheckoutController::class, 'applyVoucher'])->name('checkout.applyVoucher');

// Shipping (provider-agnostic — resolves to active courier provider)
Route::prefix('api/shipping')->name('shipping.')->group(function () {
    Route::get('/destinations', [\App\Http\Controllers\Store\ShippingController::class, 'destinations'])->name('destinations');
    Route::post('/cost', [\App\Http\Controllers\Store\ShippingController::class, 'cost'])->name('cost');
    Route::post('/track', [\App\Http\Controllers\Store\ShippingController::class, 'track'])->name('track');
});

// Active promo popup for storefront
Route::get('/api/promo-popup/active', function () {
    $popup = \App\Models\PromoPopup::active()->orderBy('sort_order')->orderByDesc('id')->first();
    if (!$popup) {
        return response()->json(['data' => null]);
    }
    return response()->json([
        'data' => [
            'id'                    => $popup->id,
            'title'                 => $popup->title,
            'image_url'             => asset($popup->image),
            'link_url'              => $popup->resolved_url,
            'button_label'          => $popup->button_label,
            'display_delay'         => $popup->display_delay,
            'show_once_per_session' => $popup->show_once_per_session,
        ],
    ]);
})->name('promo-popup.active');

// Orders (public - for guest tracking)
Route::get('/order/success/{orderNumber}', [OrderController::class, 'success'])->name('orders.success');

Route::get('/order/track', [OrderController::class, 'trackIndex'])->name('orders.track.index');
Route::post('/order/track', [OrderController::class, 'trackProcess'])->name('orders.track.process');

Route::get('/order/track/{orderNumber}', [OrderController::class, 'track'])->name('orders.track');
Route::get('/order/payment/{orderNumber}', [OrderController::class, 'payment'])->name('orders.payment');
Route::post('/order/payment/{orderNumber}', [OrderController::class, 'uploadPayment'])->name('orders.uploadPayment');
Route::post('/order/cancel/{orderNumber}', [OrderController::class, 'cancel'])->name('orders.cancel');
Route::get('/order/invoice/{orderNumber}', [InvoiceController::class, 'customerInvoice'])->name('orders.invoice');

// Account (auth required)
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');

    // Addresses
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::put('/addresses/{address}/main', [AddressController::class, 'setDefault'])->name('addresses.setDefault');

    // Wallet
    Route::get('/wallet', [StoreWalletController::class, 'index'])->name('wallet');
    Route::post('/wallet/withdraw', [StoreWalletController::class, 'withdraw'])->name('wallet.withdraw');
    Route::post('/wallet/topup', [StoreWalletController::class, 'topup'])->name('wallet.topup');
    Route::get('/wallet/payment/{trx}', [StoreWalletController::class, 'payment'])->name('wallet.payment');
    Route::post('/wallet/payment/{trx}', [StoreWalletController::class, 'uploadPayment'])->name('wallet.uploadPayment');

    // Bank Accounts
    Route::get('/banks', [BankAccountController::class, 'index'])->name('banks');
    Route::post('/banks', [BankAccountController::class, 'store'])->name('banks.store');
    Route::put('/banks/{account}', [BankAccountController::class, 'update'])->name('banks.update');
    Route::delete('/banks/{account}', [BankAccountController::class, 'destroy'])->name('banks.destroy');
    Route::put('/banks/{account}/main', [BankAccountController::class, 'setDefault'])->name('banks.setDefault');

    // Affiliate
    Route::get('/affiliate', [AffiliateController::class, 'index'])->name('affiliate');
});

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth', 'admin', 'demo_mode'])->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // POS (Point of Sale)
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PosController::class, 'index'])->name('index');
        Route::get('/products', [\App\Http\Controllers\Admin\PosController::class, 'products'])->name('products');
        Route::post('/orders', [\App\Http\Controllers\Admin\PosController::class, 'storeOrder'])->name('orders.store');
        Route::get('/orders/{order}/invoice/thermal', [\App\Http\Controllers\Admin\PosController::class, 'thermalInvoice'])->name('invoice.thermal');
        Route::get('/orders/{order}/invoice/pdf', [\App\Http\Controllers\Admin\PosController::class, 'pdfInvoice'])->name('invoice.pdf');
    });
    Route::post('ai/generate', [\App\Http\Controllers\Admin\AiController::class, 'generateContent'])->name('ai.generate');
    Route::post('products/ai-description', [\App\Http\Controllers\Admin\ProductController::class, 'generateDescription'])->name('products.ai_description');
    
    // Import BigSeller Products
    Route::get('products/import', [\App\Http\Controllers\Admin\ProductImportController::class, 'create'])->name('products.import.create');
    Route::post('products/import', [\App\Http\Controllers\Admin\ProductImportController::class, 'store'])->name('products.import.store');
    
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
    Route::resource('brands', BrandController::class)->except(['show', 'create', 'edit']);

    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::put('orders/{order}/resi', [AdminOrderController::class, 'updateResi'])->name('orders.updateResi');
    Route::put('payments/{payment}/verify', [AdminOrderController::class, 'verifyPayment'])->name('payments.verify');

    Route::resource('banners', BannerController::class)->except(['show', 'create', 'edit']);
    Route::resource('vouchers', VoucherController::class)->except(['show']);
    Route::resource('flash-sales', \App\Http\Controllers\Admin\FlashSaleController::class);
    Route::post('flash-sales/{flash_sale}/products', [\App\Http\Controllers\Admin\FlashSaleController::class, 'addProduct'])->name('flash-sales.products.add');
    Route::delete('flash-sales/{flash_sale}/products/{product}', [\App\Http\Controllers\Admin\FlashSaleController::class, 'removeProduct'])->name('flash-sales.products.remove');
    Route::resource('promo-popups', \App\Http\Controllers\Admin\PromoPopupController::class)->except(['create', 'edit']);
    Route::resource('blog', BlogController::class)->except(['show']);
    Route::resource('pages', PageController::class)->except(['show']);

    Route::get('orders/{order}/invoice', [InvoiceController::class, 'adminInvoice'])->name('orders.invoice');

    Route::get('reports/transactions', [ReportController::class, 'transactions'])->name('reports.transactions');
    Route::get('reports/users', [ReportController::class, 'users'])->name('reports.users');
    Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit_loss');
    Route::get('reports/products', [ReportController::class, 'products'])->name('reports.products');
    Route::get('reports/search-users', [ReportController::class, 'searchUsers'])->name('reports.searchUsers');
    Route::get('reports/search-products', [ReportController::class, 'searchProducts'])->name('reports.searchProducts');

    // Marketing Analytics
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MarketingController::class, 'dashboard'])->name('dashboard');
        Route::get('/visitors', [\App\Http\Controllers\Admin\MarketingController::class, 'visitors'])->name('visitors');
        Route::get('/traffic-sources', [\App\Http\Controllers\Admin\MarketingController::class, 'trafficSources'])->name('traffic_sources');
        Route::get('/abandoned-carts', [\App\Http\Controllers\Admin\MarketingController::class, 'abandonedCarts'])->name('abandoned_carts');
        Route::get('/customer-insights', [\App\Http\Controllers\Admin\MarketingController::class, 'customerInsights'])->name('customer_insights');
        Route::get('/product-analytics', [\App\Http\Controllers\Admin\MarketingController::class, 'productAnalytics'])->name('product_analytics');
    });

    Route::get('users', [UserController::class, 'index'])->name('users.index');

    Route::middleware('superadmin')->group(function () {
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/wa-test', [SettingController::class, 'testWhatsApp'])->name('settings.wa_test');
        Route::resource('administrators', \App\Http\Controllers\Admin\AdminAccountController::class);
    });

    Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::get('wallets/requests', [\App\Http\Controllers\Admin\WalletController::class, 'requests'])->name('wallets.requests');
    Route::put('wallets/requests/{transaction}', [\App\Http\Controllers\Admin\WalletController::class, 'updateRequest'])->name('wallets.updateRequest');
    Route::get('wallets/{user}', [WalletController::class, 'show'])->name('wallets.show');
    Route::post('wallets/{user}/transaction', [WalletController::class, 'transaction'])->name('wallets.transaction');

    Route::get('chat', [\App\Http\Controllers\Admin\ChatController::class, 'index'])->name('chat.index');
    Route::get('chat/{conversation}', [\App\Http\Controllers\Admin\ChatController::class, 'show'])->name('chat.show');
    Route::post('chat/{conversation}', [\App\Http\Controllers\Admin\ChatController::class, 'store'])->name('chat.store');

    Route::get('themes', [ThemeController::class, 'index'])->name('themes.index');
    Route::post('themes/activate', [ThemeController::class, 'activate'])->name('themes.activate');
    Route::post('themes/upload', [ThemeController::class, 'upload'])->name('themes.upload');
    Route::delete('themes/delete', [ThemeController::class, 'delete'])->name('themes.delete');
    Route::get('themes/marketplace', [ThemeController::class, 'marketplace'])->name('themes.marketplace');
    Route::post('themes/marketplace/install', [ThemeController::class, 'marketplaceInstall'])->name('themes.marketplace.install');

    Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::post('modules/toggle', [ModuleController::class, 'toggle'])->name('modules.toggle');
    Route::post('modules/upload', [ModuleController::class, 'upload'])->name('modules.upload');
    Route::delete('modules/delete', [ModuleController::class, 'delete'])->name('modules.delete');
    Route::get('modules/marketplace', [ModuleController::class, 'marketplace'])->name('modules.marketplace');
    Route::post('modules/marketplace/install', [ModuleController::class, 'marketplaceInstall'])->name('modules.marketplace.install');

    // DK-DevStore (Marketplace)
    Route::prefix('devstore')->name('devstore.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DevStoreController::class, 'index'])->name('index');
        Route::get('/my-products', [\App\Http\Controllers\Admin\DevStoreController::class, 'myProducts'])->name('my-products');
        Route::get('/check-updates', [\App\Http\Controllers\Admin\DevStoreController::class, 'checkUpdates'])->name('check-updates');
        Route::post('/install', [\App\Http\Controllers\Admin\DevStoreController::class, 'install'])->name('install');
        Route::post('/purchase/init', [\App\Http\Controllers\Admin\DevStoreController::class, 'purchaseInit'])->name('purchase.init');
        Route::post('/purchase/create', [\App\Http\Controllers\Admin\DevStoreController::class, 'purchaseCreate'])->name('purchase.create');
        Route::post('/purchase/upload-proof', [\App\Http\Controllers\Admin\DevStoreController::class, 'purchaseUploadProof'])->name('purchase.upload-proof');
        Route::get('/purchase/status', [\App\Http\Controllers\Admin\DevStoreController::class, 'purchaseStatus'])->name('purchase.status');
        Route::get('/{slug}', [\App\Http\Controllers\Admin\DevStoreController::class, 'show'])->name('show');
    });

    // Admin Profile & Password Updates
    Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('password.update');

    // TinyMCE Image Upload
    Route::post('upload/image', [\App\Http\Controllers\Admin\UploadController::class, 'image'])->name('upload.image');
});

// Blog & Pages (storefront)
Route::get('/blog', [\App\Http\Controllers\Store\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\Store\BlogController::class, 'show'])->name('blog.show');

// Customer Chat
Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [\App\Http\Controllers\Store\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [\App\Http\Controllers\Store\ChatController::class, 'store'])->name('chat.store');
});
Route::get('/page/{slug}', [\App\Http\Controllers\Store\PageController::class, 'show'])->name('page.show');

// Cronjob / Queue Spooling (Khusus Shared Hosting wget)
Route::get('/cron/run', function () {
    // Jalankan scheduler (jika ada task terjadwal seperti hapus pesanan kadaluwarsa)
    \Illuminate\Support\Facades\Artisan::call('schedule:run');
    
    // Tarik dan eksekusi antrean (spooling) hingga kosong
    \Illuminate\Support\Facades\Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--max-time' => 50 // Mencegah server timeout
    ]);
    
    return response()->json([
        'status' => 'success',
        'message' => 'Scheduler & Queue Spooling executed'
    ]);
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
]);

// Clear Cache Route (Khusus Shared Hosting)
Route::get('/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    
    return response()->json([
        'status' => 'success',
        'message' => 'Cache berhasil dibersihkan (optimize:clear)!'
    ]);
});
