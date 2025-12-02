<?php


use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Costs\CostsController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Customer\TransactionPdfController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Exports\ExportController;
use App\Http\Controllers\Machines\MachinesController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\PacsEntry\PacsEntryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Production\ProductionController;
use App\Http\Controllers\Recyclings\RecyclingsController;
use App\Http\Controllers\Sales\SalesController;
use App\Http\Controllers\Shift\ShiftController;
use App\Http\Controllers\ShiftAssignment\ShiftAssignmentController;
use App\Http\Controllers\Stock\StockController;
use App\Http\Controllers\Suppliers\SuppliersController;
use App\Http\Controllers\Suppliers\SuppliesController;
use App\Http\Controllers\Users\UsersController;
use App\Http\Controllers\Worker\WorkerController;
use App\Http\Controllers\ContactRequestController;
use App\Http\Controllers\GalleryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/getAllUsers', [UsersController::class, 'getAllUsers']);
    Route::get('/get-all-workers', [UsersController::class, 'getAllWorkers']);
    Route::put('/users/{id}', [UsersController::class, 'update']);
    Route::delete('/users/{id}', [UsersController::class, 'destroy']);
    Route::post('/createUsers', [UsersController::class, 'store']);
    Route::get('/getAllUserLogs',[UsersController::class, 'getAllUserLogs']);


    Route::get('/machines', [MachinesController::class, 'index']);
    Route::post('/machines', [MachinesController::class, 'store']);
    Route::get('/machines/{id}', [MachinesController::class, 'show']);
    Route::put('/machines/{id}', [MachinesController::class, 'update']);
    Route::delete('/machines/{id}', [MachinesController::class, 'destroy']);
    Route::post('/machines/delete', [MachinesController::class, 'destroySelected']);

    Route::get('/recyclings', [RecyclingsController::class, 'index']);
    Route::post('/recyclings', [RecyclingsController::class, 'store']);
    Route::get('/recyclings/{id}', [RecyclingsController::class, 'show']);
    Route::put('/recyclings/{id}', [RecyclingsController::class, 'update']);
    Route::delete('/recyclings/{id}', [RecyclingsController::class, 'destroy']);
    Route::post('/recyclings/delete', [RecyclingsController::class, 'destroySelected']);

    // Suppliers (Tedarikçiler)
    Route::get('/suppliers', [SuppliersController::class, 'index']);
    Route::post('/suppliers', [SuppliersController::class, 'store']);
    Route::get('/suppliers/{id}', [SuppliersController::class, 'show']);
    Route::put('/suppliers/{id}', [SuppliersController::class, 'update']);
    Route::delete('/suppliers/{id}', [SuppliersController::class, 'destroy']);
    Route::post('/suppliers/delete', [SuppliersController::class, 'destroySelected']);
    Route::post('/suppliers/transactions', [SuppliersController::class, 'addTransaction']);
    Route::put('/suppliers/transactions/bulk', [SuppliersController::class, 'bulkUpdateTransactions']);
    Route::get('/suppliers/{id}/periodic-debt', [SuppliersController::class, 'getPeriodicDebt']);

    // Supplies (Tedarikler)
    Route::get('/supplies', [SuppliesController::class, 'index']);
    Route::post('/supplies', [SuppliesController::class, 'store']);
    Route::get('/supplies/{id}', [SuppliesController::class, 'show']);
    Route::put('/supplies/{id}', [SuppliesController::class, 'update']);
    Route::delete('/supplies/{id}', [SuppliesController::class, 'destroy']);
    Route::post('/supplies/delete', [SuppliesController::class, 'destroySelected']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::delete('/products/delete-selected', [ProductController::class, 'destroySelected']);

    Route::get('/productions', [ProductionController::class, 'getAllProductions']);
    Route::post('/productions/worker', [ProductionController::class, 'storeByWorker']);
    Route::post('/productions/admin', [ProductionController::class, 'storeByAdmin']);
    Route::get('/productions/{id}', [ProductionController::class, 'show']);
    Route::put('/productions/{id}', [ProductionController::class, 'update']);
    Route::delete('/productions/{id}', [ProductionController::class, 'destroy']);
    Route::get('/getAllProductionLogs',[ProductionController::class, 'getAllProductionLogs']);

    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::put('/customers/{id}', [CustomerController::class, 'update']);
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);
    Route::delete('/customers', [CustomerController::class, 'deleteSelected']);
    Route::post('/transactions', [CustomerController::class, 'addTransaction']);
    Route::post('/transactions/bulk-update', [CustomerController::class, 'bulkUpdateTransactions']);
    Route::get('/customers/{id}/periodic-debt', [CustomerController::class, 'getPeriodicDebt']);
    
    // Transaction PDF Generation (Rate Limited)
    Route::post('/customers/{customer}/transactions/pdf', [TransactionPdfController::class, 'generate'])
        ->middleware('throttle:pdf')
        ->name('customers.transactions.pdf');

    // Supplier PDF Generation (Rate Limited)
    Route::post('/suppliers/{supplier}/transactions/pdf', [App\Http\Controllers\Supplier\SupplierPdfController::class, 'generate'])
        ->middleware('throttle:pdf')
        ->name('suppliers.transactions.pdf');



    Route::get('/sales', [SalesController::class, 'index']);
    Route::post('/sales', [SalesController::class, 'store']);
    Route::put('/sales/{id}', [SalesController::class, 'update']);
    Route::delete('/sales/{id}', [SalesController::class, 'destroy']);
    Route::get('/getAllSalesLogs', [SalesController::class, 'getAllSalesLogs']);

    Route::get('/getAllPacsEntries', [PacsEntryController::class, 'getAllPacsEntries']);
    Route::get('/getAllPacsEntriesLogs', [PacsEntryController::class, 'getAllPacsEntriesLogs']);
    Route::post('/createPacsEntry', [PacsEntryController::class, 'createPacsEntry']);
    Route::get('/getAllWorkers',[WorkerController::class, 'getAllWorkers']);
    Route::get('/getShifts',[WorkerController::class, 'getShifts']);

    Route::get('/getStockMovementsLogs', [StockController::class, 'getStockMovementsLogs']);
    Route::get('/getStockMovements', [StockController::class, 'getStockMovements']);

    Route::get('/costs', [CostsController::class, 'index']);
    Route::get('/costs/periodic/report', [CostsController::class, 'getPeriodicCosts']);
    Route::post('/costs', [CostsController::class, 'store']);
    Route::get('/costs/{id}', [CostsController::class, 'show']);
    Route::put('/costs/{id}', [CostsController::class, 'update']);
    Route::delete('/costs/{id}', [CostsController::class, 'destroy']);
    Route::delete('/costs/delete-selected', [CostsController::class, 'destroySelected']);

    Route::get('/costs-export',[ExportController::class,'costsExport']);
    Route::get('/production-export',[ExportController::class,'productionExport']);
    Route::get('/sales-export',[ExportController::class,'salesExport']);
    Route::get('/sales-product-export',[ExportController::class,'salesProductExport']);
    Route::get('/pacs-export',[ExportController::class,'pacsExport']);
    Route::get('/stock-movement-export',[ExportController::class,'stockMovementExport']);
    //LogExport
    Route::get('/production-log-export',[ExportController::class,'productionLogExport']);
    Route::get('/sales-log-export',[ExportController::class,'salesLogExport']);
    Route::get('/pacs-log-export',[ExportController::class,'pacsLogExport']);
    Route::get('/stock-movement-log-export',[ExportController::class,'stockMovementLogExport']);

    Route::get('/shift/shift-templates',[ShiftController::class, 'getShiftTemplates']);
    Route::post('/shift/shift-templates',[ShiftController::class, 'addShiftTemplates']);
    Route::put('/shift/shift-templates/{id}',[ShiftController::class, 'updateShiftTemplates']);
    Route::delete('/shift/shift-templates/{id}',[ShiftController::class, 'destroyShiftTemplates']);
    Route::get('/shift/shifts',[ShiftController::class,'getAllShifts']);
    Route::get('/shift/user-shift-templates/{userId}',[ShiftController::class,'getUserShiftTemplates']);

    Route::get('/shift/shift-assignments',[ShiftAssignmentController::class, 'getShiftAssignments']);
    Route::post('/shift/shift-assignments',[ShiftAssignmentController::class, 'addShiftAssignments']);
    Route::delete('/shift/shift-assignments/{id}',[ShiftAssignmentController::class, 'destroyShiftAssignments']);
    Route::put('/shift/shift-assignments/{id}',[ShiftAssignmentController::class, 'updateShiftAssignments']);

    Route::get('/shift/four-week-view/{centerDate?}', [ShiftController::class, 'getFourWeekView']);
    Route::get('/shift/shifts/{startDate}/{endDate}', [ShiftController::class, 'getShiftsByDateRange']);
    Route::post('/shift/auto-assign-week/{weekStartDate}', [ShiftController::class, 'assignAllUsersToWeek']);
    Route::post('/shift/rotate-current', [ShiftController::class, 'rotateCurrentAssignments']);

    Route::get('/current-shift', [ProductionController::class, 'getCurrentShift']);
    Route::get('/user-today-shift/{userId}', [ShiftController::class, 'getUserTodayShift']);



    Route::get('/orders',[OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{id}', [OrderController::class, 'update']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

    Route::get('/logout', [LoginController::class, 'logout']);

    // Contact Requests (İletişim İstekleri)
    Route::get('/contact-requests', [ContactRequestController::class, 'index']);
    Route::get('/contact-requests/{id}', [ContactRequestController::class, 'show']);
    Route::put('/contact-requests/{id}', [ContactRequestController::class, 'update']);
    Route::delete('/contact-requests/{id}', [ContactRequestController::class, 'destroy']);
    Route::delete('/contact-requests', [ContactRequestController::class, 'destroySelected']);

    // Dashboard API Routes
    Route::prefix('dashboard')->group(function () {

        // Üretim verileri
        Route::get('/production/daily', [DashboardController::class, 'getDailyProduction']);
        Route::get('/production/products', [DashboardController::class, 'getProductDistribution']);
        Route::post('/production/filtered', [DashboardController::class, 'getFilteredProduction']);

        // İşçi üretim verileri
        Route::get('/production/workers', [DashboardController::class, 'getWorkerProduction']);
        Route::post('/production/workers/filtered', [DashboardController::class, 'getFilteredWorkerProduction']);
        Route::get('/production/workers/detail', [DashboardController::class, 'getWorkerDetailProduction']);

        // YENİ - İşçi üretim matrisi
        Route::get('/production/workers/matrix', [DashboardController::class, 'getWorkerProductionMatrix']);

        // Filtreleme için listeler
        Route::get('/machines', [DashboardController::class, 'getMachines']);
        Route::get('/workers', [DashboardController::class, 'getWorkers']);
        Route::get('/products', [DashboardController::class, 'getProducts']);

        // Genel istatistikler
        Route::get('/stats', [DashboardController::class, 'getDashboardStats']);

    });
});



Route::post('/login',[LoginController::class,'login']);

// Public API - Contact Requests (Authentication gerekmez)
Route::post('/public/contact-requests', [ContactRequestController::class, 'store']);

// Public API - Gallery (Authentication gerekmez)
Route::get('/public/gallery', [GalleryController::class, 'index']);
Route::get('/public/gallery/product/{productSlug}', [GalleryController::class, 'getProductImages']);
Route::get('/public/gallery/homepage-slider', [GalleryController::class, 'getHomepageSlider']);





