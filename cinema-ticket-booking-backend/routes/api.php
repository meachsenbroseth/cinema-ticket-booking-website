<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CinemaController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\ShowtimeController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;


Route::get('/users', function(){
    $user = User::all();

    return response()->json($user);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('/movies')->group(function () {
    Route::get('/', [MovieController::class, 'index']);
    Route::get('/{slug}', [MovieController::class, 'show']);
    Route::get('/{slug}/showtime', [MovieController::class, 'showtimes']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [MovieController::class, 'store']);
        Route::put('/{id}', [MovieController::class, 'update']);
        Route::delete('/{id}', [MovieController::class, 'destroy']);

    });
});

Route::prefix('showtimes')->group(function () {
    Route::get('/', [ShowtimeController::class, 'index']);
    Route::get('/{id}', [ShowtimeController::class, 'show']);
    Route::get('/movie/{slug}', [ShowtimeController::class, 'byMovie']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [ShowtimeController::class, 'store']);
        Route::put('/{id}', [ShowtimeController::class, 'update']);
        Route::delete('/{id}', [ShowtimeController::class, 'destroy']);
    });
});

Route::middleware('auth:sanctum')->prefix('halls')->group(function () {
    Route::get('/', [HallController::class, 'index']);
    Route::get('/{id}', [HallController::class, 'show']);
    Route::post('/', [HallController::class, 'store']);
    Route::put('/{id}', [HallController::class, 'update']);
    Route::delete('/{id}', [HallController::class, 'destroy']);
    Route::patch('/{id}/toggle-active', [HallController::class, 'toggleActive']);
    Route::post('/{id}/regenerate-seats', [HallController::class, 'regenerateSeats']);
});

Route::prefix('/cinemas')->group(function () {
    Route::get('/dropdown', [CinemaController::class, 'dropdown']);

    Route::get('/', [CinemaController::class, 'index']);
    Route::get('/{slug}', [CinemaController::class, 'show']);
    Route::get('/{id}/showtimes', [CinemaController::class, 'showtimes']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [CinemaController::class, 'store']);
        Route::put('/{id}', [CinemaController::class, 'update']);
        Route::delete('/{id}', [CinemaController::class, 'destroy']);
        Route::patch('/{id}/toggle', [CinemaController::class, 'toggleActive']);

    });
});

Route::prefix('/seats')->group(function () {
    Route::get('/availability', [SeatController::class, 'availability']);
    Route::get('/{id}', [SeatController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [SeatController::class, 'index']);
        Route::put('/{id}', [SeatController::class, 'update']);
        Route::post('/bulk-update', [SeatController::class, 'bulkUpdate']);
        Route::patch('/{id}/toggle', [SeatController::class, 'toggleActive']);
    });
});

Route::middleware('auth:sanctum')->prefix('bookings')->group(function () {
    Route::get('/', [BookingController::class, 'index']);
    Route::post('/', [BookingController::class, 'store']);
    Route::get('/{id}', [BookingController::class, 'show']);
    Route::patch('/{id}/cancel', [BookingController::class, 'cancel']);

    // Admin only
    Route::patch('/{id}/used', [BookingController::class, 'markUsed']);
    Route::post('/expire-pending', [BookingController::class, 'expirePending']);

});

Route::middleware('auth:sanctum')->prefix('payments')->group(function () {
    // User routes
    Route::get('/', [PaymentController::class, 'index']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::get('/{id}', [PaymentController::class, 'show']);

    // Admin only
    Route::post('/verify', [PaymentController::class, 'verify']);
    Route::patch('/{id}/refund', [PaymentController::class, 'refund']);
    Route::get('/admin/stats', [PaymentController::class, 'stats']);
});

Route::middleware('auth:sanctum')->group(function () {

    // Authenticated user routes
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('/profile', [UserController::class, 'updateProfile']);
        Route::patch('/email', [UserController::class, 'updateEmail']);
        Route::patch('/change-password', [UserController::class, 'changePassword']);
        Route::delete('/avatar', [UserController::class, 'deleteAvatar']);
        Route::get('/bookings', [UserController::class, 'bookings']);
    });

    // Admin only routes
    Route::prefix('admin/users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/stats', [UserController::class, 'stats']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::patch('/{id}/role', [UserController::class, 'updateRole']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
});
