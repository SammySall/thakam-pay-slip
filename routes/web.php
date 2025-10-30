<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SlipController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/showdata', [SlipController::class, 'index'])->name('slips.index');
Route::get('/list_new_slip', [SlipController::class, 'listNewSlip'])->name('slips.new');
Route::post('/slips/store', [SlipController::class, 'store'])->name('slips.store');
Route::get('/slips/{slip}', [SlipController::class, 'show']);
Route::get('/approve-slip', [SlipController::class, 'listApproveSlip'])->name('slips.approve.list');

Route::get('/slip/{id}/detail', [SlipController::class, 'getSlipDetail'])->name('slips.detail');
Route::post('/slip/{id}/approve', [SlipController::class, 'approveSlip'])->name('slips.approve');
Route::get('/slip/{id}/pdf', [SlipController::class, 'generatePdf'])->name('slips.pdf');

