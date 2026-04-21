<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\Sales\CustomerController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\WholesaleController;
use App\Http\Controllers\WholesaleStorageController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;
use App\Models\Till;
use Illuminate\Http\Request;
// 1. Лицензия и Язык (Публичные)
Route::get('/license', [LicenseController::class, 'show'])->name('license.show');
Route::post('/license/activate', [LicenseController::class, 'activate'])->name('license.activate');
Route::get('lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// 2. Группа под проверкой лицензии
Route::middleware(['license'])->group(function () {



Route::post('/api/setup-device', function (Request $request) {
    try {
        // 1. Проверяем, пришло ли число
        if (!$request->has('name')) {
            return response()->json(['error' => 'Номер кассы не указан'], 422);
        }
        $fullName = "Касса №" . $request->name;
        $till = Till::firstOrCreate(
            ['name' => $fullName],
            ['ip_address' => $request->ip() ?? '127.0.0.1']
        );

        return response()->json([
            'id' => $till->id,
            'name' => $till->name
        ]);

    } catch (\Exception $e) {
        
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
    // Редирект после логина
    Route::get('/', function () {
        if (!auth()->check()) return redirect()->route('login');
        $role = auth()->user()->role ?? 'guest';
        return match($role) {
            'admin'     => redirect()->route('welcome'),
            'salesman'  => redirect()->route('sales.index'),
            'storage'   => redirect()->route('storage.index'),
            'wholesale' => redirect()->route('wholesale.redirect'),
            default     => redirect()->route('login'),
        };
    });

    // Гости (Логин)
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
    });

    // Авторизованные пользователи
    Route::middleware(['auth'])->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        // Shortcut routes for role-based redirects
        Route::redirect('/sales', '/admin/sales')->name('sales.index');
        Route::redirect('/storage', '/admin/inventory')->name('storage.redirect');
        Route::redirect('/wholesale', '/admin/wholesale')->name('wholesale.index');

        // Группа только для АДМИНА
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/welcome', fn() => view('welcome'))->name('welcome');
            
            // Admin Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
            
            // Boss Dashboard
          Route::prefix('boss')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('boss.dashboard');
    Route::get('/expense', [DashboardController::class, 'expensesIndex'])->name('boss.expense.index');
    Route::post('/expense/store', [DashboardController::class, 'storeExpense'])->name('boss.expense.store');
    Route::get('/till/{id}', [DashboardController::class, 'showTill'])->name('boss.till.show');
    Route::get('/revenue', [DashboardController::class, 'totalRevenue'])->name('boss.revenue');
    Route::get('/shifts', [DashboardController::class, 'shiftLogs'])->name('admin.shifts');
 
    // ↓ NEW export routes
    Route::get('/revenue/export',       [DashboardController::class, 'exportRevenue'])->name('boss.revenue.export');
    Route::get('/shifts/export',        [DashboardController::class, 'exportShifts'])->name('boss.shifts.export');
    Route::get('/till/{id}/export',     [DashboardController::class, 'exportTill'])->name('boss.till.export');
    Route::get('/expense/export',       [DashboardController::class, 'exportExpenses'])->name('boss.expense.export');
});

            // Управление лекарствами
            Route::controller(ProductController::class)->prefix('product')->name('product.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/search', 'search')->name('search');
                Route::get('/{id}', 'show')->name('show');
            });

            // Сотрудники
            Route::controller(EmployeeController::class)->prefix('employees')->name('employees.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{id}', 'show')->name('show');
            });
        });

        // Группа СКЛАД (Admin + Storage + Wholesale для трансфера)
        Route::middleware('role:admin,storage,wholesale')->prefix('admin')->group(function () {
            Route::controller(WholesaleStorageController::class)->prefix('wholesale-storage')->name('wholesale_storage.')->group(function () {
                Route::get('/', 'index')->name('storage_index');
                Route::get('/create', 'create')->name('storage_create');
                Route::post('/store', 'store')->name('storage_store');
                Route::post('/transfer', 'transferToMarket')->name('storage_transfer'); // ТЕПЕРЬ ДОСТУПНО ОПТОВИКУ
                Route::delete('/{id}', 'destroy')->name('storage_destroy');
                 Route::get('/export', 'export')->name('storage_export');
            });

            Route::controller(StorageController::class)->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', 'index')->name('inventory_index');
    Route::post('/', 'store')->name('inventory_store');
    Route::get('/{id}/edit', 'edit')->name('inventory_edit');
    Route::put('/{id}', 'update')->name('inventory_update');
    Route::delete('/{id}', 'destroy')->name('inventory_destroy');
    
    // ИСПРАВЛЕНИЕ: Добавляем метод export
    Route::get('/export-data', 'export')->name('inventory_export');
});

Route::middleware('role:admin,wholesale')->prefix('admin')->group(function () {
    Route::controller(WholesaleController::class)->prefix('wholesale')->name('wholesale.')->group(function () {
        Route::get('/', 'index')->name('wholesale_index');
        Route::get('/create', 'create')->name('wholesale_create');
        Route::post('/', 'store')->name('wholesale_store');
        Route::post('/transfer', 'transfer')->name('wholesale_transfer');
        Route::get('/autocomplete', 'autocomplete')->name('wholesale_autocomplete');
        Route::get('/export', 'exportExcel')->name('wholesale_export');  // BEFORE /{id}
        Route::get('/{id}', 'show')->name('wholesale_show');
        Route::get('/{id}/edit', 'edit')->name('wholesale_edit');
        Route::put('/{id}', 'update')->name('wholesale_update');
        Route::delete('/{id}', 'destroy')->name('wholesale_destroy');
    });
});

        
        Route::middleware('role:admin,salesman')->prefix('admin')->group(function () {
            Route::prefix('sales')->name('sales.')->group(function () {
                Route::controller(SaleController::class)->group(function () {
                    Route::get('/', 'index')->name('sales_index');
                    Route::post('/close-shift', 'closeShift')->name('sales_close_shift');
                    Route::get('/report', 'showReport')->name('sales_report');
                    Route::delete('/delete/{id}', 'destroy')->name('sales_destroy');
                    Route::post('/cart/add', 'addToCart')->name('sales_cart_add');
                    Route::delete('/cart/{id}', 'removeFromCart')->name('sales_cart_remove');
                    Route::patch('/cart/{id}', 'updateCart')->name('sales_cart_update');
                    Route::post('/checkout', 'checkout')->name('sales_checkout');
                    Route::post('/start-shift', 'startShift')->name('sales_start_shift'); 
                    
                });
            });
            });
Route::controller(CustomerController::class)
    ->prefix('customers')          // -> /admin/sales/customers
    ->name('customers.')           // -> sales.customers.*
    ->group(function () {
        Route::get('/', 'index')->name('customers_index'); // sales.customers.index
        Route::get('/view/{transaction_id}', 'show')->name('customers_show'); // sales.customers.show
        Route::get('/export-all', 'exportAll')->name('customers_export_all'); // sales.customers.export.all
        Route::get('/export-single/{transaction_id}', 'exportSingle')->name('customers_export_single'); // sales.customers.export.single
    });
});
            });
        });
