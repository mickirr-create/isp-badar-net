<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BandwidthController;
use App\Http\Controllers\Admin\CustomFieldController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\LogsController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\PoolController;
use App\Http\Controllers\Admin\RechargeController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\RouterController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\PlanController as CustomerPlanController;
use App\Http\Controllers\Customer\TransactionController as CustomerTransactionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Admin Auth Routes
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Admin Protected Routes
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Plans
    Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
    Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
    Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
    Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');

    // Routers
    Route::get('/routers', [RouterController::class, 'index'])->name('routers.index');
    Route::get('/routers/create', [RouterController::class, 'create'])->name('routers.create');
    Route::post('/routers', [RouterController::class, 'store'])->name('routers.store');
    Route::get('/routers/{router}/edit', [RouterController::class, 'edit'])->name('routers.edit');
    Route::put('/routers/{router}', [RouterController::class, 'update'])->name('routers.update');
    Route::delete('/routers/{router}', [RouterController::class, 'destroy'])->name('routers.destroy');

    // Bandwidth
    Route::get('/bandwidth', [BandwidthController::class, 'index'])->name('bandwidth.index');
    Route::get('/bandwidth/create', [BandwidthController::class, 'create'])->name('bandwidth.create');
    Route::post('/bandwidth', [BandwidthController::class, 'store'])->name('bandwidth.store');
    Route::get('/bandwidth/{bandwidth}/edit', [BandwidthController::class, 'edit'])->name('bandwidth.edit');
    Route::put('/bandwidth/{bandwidth}', [BandwidthController::class, 'update'])->name('bandwidth.update');
    Route::delete('/bandwidth/{bandwidth}', [BandwidthController::class, 'destroy'])->name('bandwidth.destroy');

    // Pools
    Route::get('/pools', [PoolController::class, 'index'])->name('pools.index');
    Route::get('/pools/create', [PoolController::class, 'create'])->name('pools.create');
    Route::post('/pools', [PoolController::class, 'store'])->name('pools.store');
    Route::get('/pools/{pool}/edit', [PoolController::class, 'edit'])->name('pools.edit');
    Route::put('/pools/{pool}', [PoolController::class, 'update'])->name('pools.update');
    Route::delete('/pools/{pool}', [PoolController::class, 'destroy'])->name('pools.destroy');

    // Recharges
    Route::get('/recharges', [RechargeController::class, 'index'])->name('recharges.index');
    Route::get('/recharges/create', [RechargeController::class, 'create'])->name('recharges.create');
    Route::post('/recharges', [RechargeController::class, 'store'])->name('recharges.store');
    Route::delete('/recharges/{recharge}', [RechargeController::class, 'destroy'])->name('recharges.destroy');

    // Vouchers
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

    // Payment Gateways
    Route::get('/payment-gateways', [PaymentGatewayController::class, 'index'])->name('payment-gateways.index');
    Route::put('/payment-gateways/{gateway}', [PaymentGatewayController::class, 'update'])->name('payment-gateways.update');
    Route::get('/payment-gateways/audit', [PaymentGatewayController::class, 'audit'])->name('payment-gateways.audit');
    Route::get('/payment-gateways/audit/{audit}', [PaymentGatewayController::class, 'auditView'])->name('payment-gateways.audit-view');

    // Reports
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/daily', [ReportsController::class, 'dailyReport'])->name('reports.daily');
    Route::get('/reports/chart-data', [ReportsController::class, 'chartData'])->name('reports.chart-data');
    Route::get('/reports/active-plans', [ReportsController::class, 'activePlans'])->name('reports.active-plans');

    // Logs
    Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
    Route::get('/logs/messages', [LogsController::class, 'messageLogs'])->name('message-logs.index');
    Route::delete('/logs/{log}', [LogsController::class, 'destroy'])->name('logs.destroy');
    Route::post('/logs/clear-old', [LogsController::class, 'clearOld'])->name('logs.clear-old');

    // Messages
    Route::get('/messages/send', [MessageController::class, 'sendForm'])->name('messages.send');
    Route::post('/messages/send', [MessageController::class, 'send']);
    Route::get('/messages/bulk', [MessageController::class, 'bulkForm'])->name('messages.bulk');
    Route::post('/messages/bulk', [MessageController::class, 'bulkSend'])->name('messages.bulk-send');

    // Custom Fields
    Route::get('/custom-fields', [CustomFieldController::class, 'index'])->name('custom-fields.index');
    Route::post('/custom-fields', [CustomFieldController::class, 'store'])->name('custom-fields.save');
    Route::delete('/custom-fields', [CustomFieldController::class, 'destroy'])->name('custom-fields.destroy');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/localization', [SettingsController::class, 'localization'])->name('settings.localization');
    Route::get('/settings/users', [SettingsController::class, 'users'])->name('settings.users');
    Route::get('/settings/users/create', [SettingsController::class, 'usersCreate'])->name('settings.users.create');
    Route::post('/settings/users', [SettingsController::class, 'usersStore'])->name('settings.users.store');
    Route::get('/settings/users/{user}/edit', [SettingsController::class, 'usersEdit'])->name('settings.users.edit');
    Route::put('/settings/users/{user}', [SettingsController::class, 'usersUpdate'])->name('settings.users.update');
    Route::delete('/settings/users/{user}', [SettingsController::class, 'usersDestroy'])->name('settings.users.destroy');
    Route::get('/settings/db-status', [SettingsController::class, 'dbStatus'])->name('settings.db-status');
    Route::get('/settings/db-backup', [SettingsController::class, 'dbBackup'])->name('settings.db-backup');
    Route::post('/settings/db-backup/download', [SettingsController::class, 'dbBackupDownload'])->name('settings.db-backup.download');
    Route::get('/settings/maintenance', [SettingsController::class, 'maintenance'])->name('settings.maintenance');
    Route::post('/settings/maintenance/toggle', [SettingsController::class, 'maintenanceToggle'])->name('settings.maintenance.toggle');
});

// Customer Auth Routes
Route::get('/customer/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
Route::post('/customer/login', [CustomerAuthController::class, 'login']);
Route::get('/customer/register', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
Route::post('/customer/register', [CustomerAuthController::class, 'register']);
Route::post('/customer/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

// Customer Protected Routes
Route::prefix('customer')->name('customer.')->middleware('auth:customer')->group(function () {
    Route::get('/', [DashboardController::class, 'customerDashboard'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    // Plans
    Route::get('/plans', [CustomerPlanController::class, 'index'])->name('plans.index');

    // Transactions
    Route::get('/transactions', [CustomerTransactionController::class, 'index'])->name('transactions.index');
});

// Root redirect
Route::get('/', function () {
    if (auth()->guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    if (auth()->guard('customer')->check()) {
        return redirect()->route('customer.dashboard');
    }
    return redirect()->route('admin.login');
});
