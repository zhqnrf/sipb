<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    DashboardController, StudentController, FeeTypeController, BillController, ClassroomController, PasswordController, PaymentController, ReceiptController
};


Route::view('/login','auth.login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('doLogin');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Ganti password role (admin only by session)
Route::get('/auth/passwords', [PasswordController::class, 'form'])->name('passwords.form');
Route::post('/auth/passwords', [PasswordController::class, 'update'])->name('passwords.update');
Route::post('classrooms/import', [ClassroomController::class, 'import'])->name('classrooms.import');
Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
Route::get('/', [DashboardController::class,'index'])->name('dashboard');
Route::resource('classrooms', ClassroomController::class)->except('show');
// Master Data
Route::resource('students', StudentController::class)->except('show');
Route::resource('fee-types', FeeTypeController::class)->except('show');
Route::post('fee-types/import', [FeeTypeController::class, 'import'])->name('fee-types.import');
Route::post('bills/import', [BillController::class, 'import'])->name('bills.import');
// Tagihan
Route::get('bills', [BillController::class,'index'])->name('bills.index');
Route::get('bills/generate', [BillController::class,'generateForm'])->name('bills.generate.form');
Route::post('bills/generate', [BillController::class,'generate'])->name('bills.generate');
Route::get('bills/{bill}', [BillController::class,'show'])->name('bills.show');
Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
// Pembayaran
Route::get('bills/{bill}/pay', [PaymentController::class,'create'])->name('payments.create');
Route::post('bills/{bill}/pay', [PaymentController::class,'store'])->name('payments.store');

// Kwitansi
Route::get('receipts/{payment}', [ReceiptController::class,'show'])->name('receipts.show');

// routes/web.php
Route::get('/qr-test', function () {
    return \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(160)->generate('HELLO');
});

