<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\Sales\CustomerController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\WholesaleController;
use App\Http\Controllers\WholesaleStorageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;
use App\Models\Till;
use Illuminate\Http\Request;

// 1. Лицензия и Язык (Публичные)
Route::get('/license', [LicenseController::class, 'show'])->name('license.show');
Route::post('/license/activate', [LicenseController::class, 'activate'])->name('license.activate');
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ru', 'tm'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

// 2. Группа под проверкой лицензии
Route::middleware(['license'])->group(function () {

    Route::post('/api/setup-device', function (Request $request) {
        try {
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

    // Главная — редирект по роли
    Route::get('/', function () {
        if (!auth()->check()) return redirect()->route('login');
        $role = auth()->user()->role ?? 'guest';
        return match($role) {
            'admin'     => redirect()->route('welcome'),
            'salesman'  => redirect()->route('sales.index'),
            'storage'   => redirect()->route('storage.index'),
            'wholesale' => redirect()->route('wholesale.index'),
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

        Route::redirect('/sales', '/admin/sales')->name('sales.redirect');
        Route::redirect('/storage', '/admin/inventory')->name('storage.redirect');
        Route::redirect('/wholesale', '/admin/wholesale')->name('wholesale.redirect');

        // ── ВСЕ РОЛИ ПОД prefix('admin') ──────────────────────
        Route::prefix('admin')->group(function () {

            // ── ТОЛЬКО ADMIN ───────────────────────────────────
            Route::middleware('role:admin')->group(function () {

                Route::get('/welcome', fn() => view('welcome'))->name('welcome');

                Route::prefix('boss')->group(function () {
                    Route::get('/', [DashboardController::class, 'index'])->name('boss.dashboard');
                    Route::get('/expense', [DashboardController::class, 'expensesIndex'])->name('boss.expense.index');
                    Route::post('/expense/store', [DashboardController::class, 'storeExpense'])->name('boss.expense.store');
                    Route::get('/till/{id}', [DashboardController::class, 'showTill'])->name('boss.till.show');
                    Route::get('/revenue', [DashboardController::class, 'totalRevenue'])->name('boss.revenue');
                    Route::get('/shifts', [DashboardController::class, 'shiftLogs'])->name('admin.shifts');
                    Route::get('/revenue/export', [DashboardController::class, 'exportRevenue'])->name('boss.revenue.export');
                    Route::get('/shifts/export', [DashboardController::class, 'exportShifts'])->name('boss.shifts.export');
                    Route::get('/till/{id}/export', [DashboardController::class, 'exportTill'])->name('boss.till.export');
                    Route::get('/expense/export', [DashboardController::class, 'exportExpenses'])->name('boss.expense.export');
                });

                Route::controller(ProductController::class)->prefix('product')->name('product.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('/search', 'search')->name('search');
                    Route::get('/{id}', 'show')->name('show');
                });

                Route::controller(EmployeeController::class)->prefix('employees')->name('employees.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('/{id}', 'show')->name('show');
                });
            });

            // ── СКЛАД (admin + storage + wholesale) ───────────
            Route::middleware('role:admin,storage,wholesale')->group(function () {

                Route::controller(WholesaleStorageController::class)->prefix('wholesale-storage')->name('wholesale_storage.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/create', 'create')->name('create');
                    Route::post('/store', 'store')->name('store');
                    Route::post('/transfer', 'transferToMarket')->name('transfer');
                    Route::delete('/{id}', 'destroy')->name('destroy');
                    Route::get('/export', 'export')->name('export');
                });

                Route::controller(StorageController::class)->prefix('inventory')->name('storage.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/', 'store')->name('store');
                    Route::get('/{id}/edit', 'edit')->name('edit');
                    Route::put('/{id}', 'update')->name('update');
                    Route::delete('/{id}', 'destroy')->name('destroy');
                    Route::get('/export-data', 'export')->name('export');
                });
            });

            // ── ОПТОВИК (admin + wholesale) ───────────────────
            Route::middleware('role:admin,wholesale')->group(function () {

                Route::controller(WholesaleController::class)->prefix('wholesale')->name('wholesale.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::post('/transfer', 'transfer')->name('transfer');
                    Route::get('/autocomplete', 'autocomplete')->name('autocomplete');
                    Route::get('/export', 'exportExcel')->name('export');
                    Route::get('/{id}', 'show')->name('show');
                    Route::get('/{id}/edit', 'edit')->name('edit');
                    Route::put('/{id}', 'update')->name('update');
                    Route::delete('/{id}', 'destroy')->name('destroy');
                });
            });

            // ── ПРОДАЖИ (admin + salesman) ────────────────────
            Route::middleware('role:admin,salesman')->group(function () {

                Route::prefix('sales')->name('sales.')->group(function () {

                    Route::controller(SaleController::class)->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/close-shift', 'closeShift')->name('close');
                        Route::get('/report', 'showReport')->name('report');
                        Route::delete('/delete/{id}', 'destroy')->name('destroy');
                        Route::post('/cart/add', 'addToCart')->name('cart.add');
                        Route::patch('/cart/{id}', 'updateCart')->name('cart.update');
                        Route::delete('/cart/{id}', 'removeFromCart')->name('cart.remove');
                        Route::post('/checkout', 'checkout')->name('cart.checkout');
                        Route::post('/start-shift', 'startShift')->name('start_shift');
                    });

                    Route::controller(CustomerController::class)->prefix('customers')->name('customers.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/view/{transaction_id}', 'show')->name('show');
                        Route::get('/export-all', 'exportAll')->name('export.all');
                        Route::get('/export-single/{transaction_id}', 'exportSingle')->name('export.single');
                    });
                });
            });

            // Thermal Receipt Routes (admin + salesman access)
            Route::middleware('role:admin,salesman')->group(function () {
                Route::controller(SaleController::class)->prefix('receipt')->name('receipt.')->group(function () {
                    Route::get('/thermal/print/{saleId}', 'printThermalReceipt')->name('thermal.print');
                    Route::get('/thermal/test', 'testThermalPrint')->name('thermal.test');
                });
            });

        }); // end prefix admin

    }); // end auth

}); // end license