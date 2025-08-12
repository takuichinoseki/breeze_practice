<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
// 管理者用のダッシュボードを追加
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
// 一般ユーザー用のダッシュボードを追加
use App\Http\Controllers\Users\DashboardController as UserDashboardController;
// Gateを使うために必要
use Illuminate\Support\Facades\Auth;



Route::get('/', function () {
    return view('welcome');
});

// デフォルトの設定
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


// ダッシュボードの切り替えのハブにする
Route::get('/dashboard', function () {
    $user = Auth::user();

    // Gateがある場合（推奨）
    if ($user->can('access-admin')) {
        return redirect()->route('admin.dashboard');
    }

    // Gateを使わずロールで直判定するなら↓
    // if ($user->role === 'admin') {
    //     return redirect()->route('admin.dashboard');
    // }

    return redirect()->route('user.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 管理者ダッシュボード
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'can:access-admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');
    });

// 一般ユーザーダッシュボード
Route::prefix('user')
    ->name('user.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])
            ->name('dashboard');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
