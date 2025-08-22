<?php

use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\DeliveryLocationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\UserController;
use App\Mail\SubscriptionExpiryReminderMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

Route::post('/vendor/register', [UserAuthController::class, 'register']);

Route::post('/vendor/login', [UserAuthController::class, 'login']);

Route::post('/vendor/verify-email', [UserAuthController::class, 'verifyEmail']);

Route::post('/vendor/resend-email', [UserAuthController::class, 'resendEmailVerification']);

Route::post('/vendor/forgot_password', [UserAuthController::class, 'requestPasswordReset']);

Route::post('/vendor/verify_password_code', [UserAuthController::class, 'verifyResetCode']);

Route::post('/vendor/new_password', [UserAuthController::class, 'resetPassword']);

Route::post('/vendor/resend-password-code', [UserAuthController::class, 'resendPasswordResetCode']);

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user()->load('profile.profileLink');
});

Route::post('/vendor/logout', [UserAuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('/vendor/profilefill', [ProfileController::class, 'store']);

Route::middleware('auth:sanctum')->patch('/vendor/profilefill', [ProfileController::class, 'update']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/vendor/products', [ProductController::class, 'store']);
    Route::put('/vendor/products/{id}', [ProductController::class, 'update']);
    Route::delete('/vendor/products/{id}', [ProductController::class, 'destroy']);
});

Route::get('/vendor/receipts', [ReceiptController::class, 'index'])->middleware('auth:sanctum');

Route::get('/product/{link}', [ProductController::class, 'showByLink']);

Route::get('/categories', [ProductCategoryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/vendor/delivery-locations', [DeliveryLocationController::class, 'store']);
    Route::put('/vendor/delivery-locations/{id}', [DeliveryLocationController::class, 'update']);
    Route::get('/vendor/delivery-locations', [DeliveryLocationController::class, 'index']);
    Route::delete('/vendor/delivery-locations/all', [DeliveryLocationController::class, 'destroyAll']);
    Route::delete('/vendor/delivery-locations/{id}', [DeliveryLocationController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->get('/vendor/products', [ProductController::class, 'myProducts']);
Route::get('/shared-profile/{uuid}', [ProfileController::class, 'showSharedProfile']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/vendors/search', [UserController::class, 'searchVendors']);

Route::get('/vendors/{id}/products', [ProductController::class, 'getByUser']);
Route::get('/single-product/{id}', [ProductController::class, 'showById']);

Route::get('/vendor/{vendorId}/delivery-locations', [DeliveryLocationController::class, 'getVendorLocations']);

Route::post('/orders/{productId}', [OrderController::class, 'store']);

Route::get('/vendors', [UserController::class, 'index']);
Route::get('/vendors/{id}', [UserController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/vendor/receipts', [ReceiptController::class, 'store']);
    Route::get('/vendor/receipts/{id}', [ReceiptController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/vendor/myorders', [OrderController::class, 'myOrders']);
    Route::patch('/order/{id}/status', [OrderController::class, 'updateStatus']);
    Route::get('/vendor/myorders/{id}', [OrderController::class, 'showSingleOrder']);
});

Route::get('/orders/{order}/receipt-download', [OrderController::class, 'downloadReceipt']);

Route::get('/public/products', [ProductController::class, 'getAllPublic']);

Route::middleware('auth:sanctum')->post('/paystack/init', [PaystackController::class, 'initialize'])->name('paystack.init');

Route::get('/paystack/callback', [PaystackController::class, 'callback'])->name('paystack.callback');

Route::middleware('auth:sanctum')->get('/vendor/subscription', [PaystackController::class, 'currentSubscription']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/vendor/deactivate', [UserAuthController::class, 'deactivate']);
    Route::delete('/vendor/delete', [UserAuthController::class, 'deleteAccount']);
});

Route::post('/vendor/reactivate-account', [UserAuthController::class, 'reactivateAccount']);

Route::get('/test-reminder-mail', function () {
    $user = User::first(); // or User::find(1)
    Mail::to($user->email)->send(
        new SubscriptionExpiryReminderMail($user, $user->subscription_type, $user->subscription_expires_at, 7)
    );
    return 'Reminder email sent (or attempted). Check logs/mailtrap.';
});

Route::middleware('auth:sanctum')->post('/vendor/change-password', [UserAuthController::class, 'changePassword']);