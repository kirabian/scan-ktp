<?php

use App\Http\Controllers\OcrController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecureImageController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\UserManage;
use App\Livewire\Admin\WargaList;
use App\Livewire\Admin\SedekahList;
use App\Livewire\Data\WargaForm;
use App\Livewire\Security\ScanKtp;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    
    // Default dashboard redirect based on role
    Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isSecurity()) {
            return redirect()->route('security.scan');
        } elseif ($user->isData()) {
            return redirect()->route('data.warga');
        }
        return redirect('/');
    })->name('dashboard');

    // Profile routes (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', Dashboard::class)->name('admin.dashboard');
        Route::get('/admin/users', UserManage::class)->name('admin.users');
        Route::get('/admin/warga', WargaList::class)->name('admin.warga-list');
        Route::get('/admin/sedekah', SedekahList::class)->name('admin.sedekah-list');
    });

    // Security routes
    Route::middleware('role:admin,security,data')->group(function () {
        Route::get('/security/scan', ScanKtp::class)->name('security.scan');
    });

    // Data routes
    Route::middleware('role:admin,data,security')->group(function () {
        Route::get('/data/warga', WargaForm::class)->name('data.warga');
    });

    // OCR Route (server-side Tesseract)
    Route::post('/ocr/ktp', [OcrController::class, 'processKtp'])->name('ocr.ktp');

    // Secure Image Route
    Route::get('/secure/{folder}/{filename}', [SecureImageController::class, 'show'])->name('secure.image');
});

require __DIR__.'/auth.php';
