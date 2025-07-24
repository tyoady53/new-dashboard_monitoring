<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MonitoringController;
use App\Models\Customer;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('dashboard')->group(function (){
    Route::get('/get_branch/{cust_id}', [CustomerController::class, 'get_branch']);
    Route::get('/get_data/{email}', [MonitoringController::class, 'get_data']);
    Route::get('/random_data', [MonitoringController::class, 'create']);
    Route::post('/store', [MonitoringController::class, 'store']);

    // Route::get('/get_stat_test_group', [MonitoringController::class, 'get_stat_test_group']);
    // Route::get('/get_stat_nilai_kritis', [MonitoringController::class, 'get_stat_nilai_kritis']);
    // Route::get('/get_stat_asal_pasien', [MonitoringController::class, 'get_stat_asal_pasien']);
    // Route::get('/get_kunj_perjam', [MonitoringController::class, 'get_kunj_perjam']);
    // Route::get('/get_monitoring_tat', [MonitoringController::class, 'get_monitoring_tat']);
    // Route::get('/get_nilai_ktitis', [MonitoringController::class, 'get_nilai_ktitis']);
    // Route::get('/get_statbox', [MonitoringController::class, 'get_statbox']);

    Route::get('/get_setup', [MonitoringController::class, 'get_setup']);

    Route::get('/get_last_update', [MonitoringController::class, 'get_last_update']);
    // Route::get('/get_data', [MonitoringController::class, 'get_data']);
    // Route::post('/store', [MonitoringController::class, 'store']);
});
