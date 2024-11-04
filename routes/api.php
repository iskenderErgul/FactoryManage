<?php


use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Machines\MachinesController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Production\ProductionController;
use App\Http\Controllers\Sales\SalesController;
use App\Http\Controllers\Sales\SalesProductController;
use App\Http\Controllers\Stock\StockController;
use App\Http\Controllers\Users\UsersController;
use App\Http\Controllers\Worker\WorkerController;
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

    Route::get('/sales', [SalesController::class, 'index']);
    Route::post('/sales', [SalesController::class, 'store']);
    Route::put('/sales/{id}', [SalesController::class, 'update']);
    Route::delete('/sales/{id}', [SalesController::class, 'destroy']);
    Route::get('/getAllSalesLogs', [SalesController::class, 'getAllSalesLogs']);








    Route::get('/getStockMovementsLogs', [StockController::class, 'getStockMovementsLogs']);
    Route::get('/getStockMovements', [StockController::class, 'getStockMovements']);
    Route::get('/getAllWorkers',[WorkerController::class, 'getAllWorkers']);
    Route::get('/getShifts',[WorkerController::class, 'getShifts']);



    Route::get('/logout', [LoginController::class, 'logout']);



});



Route::post('/login',[LoginController::class,'login']);





