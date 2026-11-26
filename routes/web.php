<?php

use App\Http\Controllers\AdminSupplierVerificationController;
use App\Http\Controllers\AdminModerationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\SupplierCenterController;
use App\Http\Controllers\SupplierPaymentSettingsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

Route::get('/', [WebController::class, 'index'])->name('home');
Route::get('/catalog', [WebController::class, 'catalog'])->name('catalog');
Route::get('/products/{product:slug}', [WebController::class, 'product'])->name('products.show');
Route::get('/suppliers/{supplier}', [WebController::class, 'supplier'])->name('suppliers.show');
Route::get('/login', [WebController::class, 'login'])->name('login');
Route::post('/login', [WebController::class, 'authenticate'])->middleware('throttle:6,1');
Route::get('/forgot-password', [WebController::class, 'forgotPassword'])->name('password.request');
Route::post('/forgot-password', [WebController::class, 'sendPasswordResetLink'])->middleware('throttle:6,1')->name('password.email');
Route::get('/reset-password/{token}', [WebController::class, 'showPasswordResetForm'])->name('password.reset');
Route::post('/reset-password', [WebController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
Route::get('/register', [WebController::class, 'register'])->name('register');
Route::post('/register', [WebController::class, 'storeRegistration'])->middleware('throttle:6,1');
Route::post('/logout', [WebController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/privacy-policy', [WebController::class, 'privacy'])->name('privacy');
Route::get('/terms', [WebController::class, 'terms'])->name('terms');
Route::get('/regions/provinces', [RegionController::class, 'provinces'])->name('regions.provinces');
Route::get('/regions/regencies/{province}', [RegionController::class, 'regencies'])->where('province', '[0-9]{2}')->name('regions.regencies');
Route::get('/regions/districts/{regency}', [RegionController::class, 'districts'])->where('regency', '[0-9]{2}\.[0-9]{2}')->name('regions.districts');
Route::get('/regions/villages/{district}', [RegionController::class, 'villages'])->where('district', '[0-9]{2}\.[0-9]{2}\.[0-9]{2}')->name('regions.villages');

Route::middleware(['auth', 'active.account'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/suppliers/{supplier}', [ChatController::class, 'startWithSupplier'])->name('chat.suppliers.start');
    Route::post('/chat/orders/{order}', [ChatController::class, 'startForOrder'])->name('chat.orders.start');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/{conversation}/messages', [ChatController::class, 'messages'])->name('chat.messages.index');
    Route::post('/chat/{conversation}/messages', [ChatController::class, 'send'])->name('chat.messages.store');
    Route::get('/chat/{conversation}/messages/{message}/attachment', [ChatController::class, 'attachment'])->name('chat.messages.attachment');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items/{product}', [CartController::class, 'add'])->name('cart.items.store');
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'remove'])->name('cart.items.destroy');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('dashboard');
    Route::get('/supplier/products/create', [WebController::class, 'supplierProductsCreate'])->name('supplier.products.create');
    Route::post('/supplier/products', [WebController::class, 'supplierProductsStore'])->name('supplier.products.store');
    Route::get('/supplier/products/{product}/edit', [WebController::class, 'supplierProductsEdit'])->name('supplier.products.edit');
    Route::put('/supplier/products/{product}', [WebController::class, 'supplierProductsUpdate'])->name('supplier.products.update');
    Route::delete('/supplier/products/{product}', [WebController::class, 'supplierProductsDestroy'])->name('supplier.products.destroy');
    Route::get('/supplier/payment-settings', [SupplierPaymentSettingsController::class, 'edit'])->name('supplier.payment-settings.edit');
    Route::put('/supplier/payment-settings', [SupplierPaymentSettingsController::class, 'update'])->name('supplier.payment-settings.update');
    Route::get('/supplier/nib-document', [SupplierPaymentSettingsController::class, 'downloadNibDocument'])->name('supplier.nib-document');
    Route::get('/supplier/inventory', [SupplierCenterController::class, 'inventory'])->name('supplier.inventory');
    Route::post('/supplier/inventory/{product}', [SupplierCenterController::class, 'updateStock'])->name('supplier.inventory.update');
    Route::get('/supplier/finance', [SupplierCenterController::class, 'finance'])->name('supplier.finance');
    Route::post('/supplier/finance/expenses', [SupplierCenterController::class, 'storeExpense'])->name('supplier.expenses.store');
    Route::delete('/supplier/finance/expenses/{expense}', [SupplierCenterController::class, 'deleteExpense'])->name('supplier.expenses.destroy');
    Route::get('/supplier/finance/export', [SupplierCenterController::class, 'exportFinance'])->name('supplier.finance.export');
    Route::get('/supplier/distribution', [SupplierCenterController::class, 'distribution'])->name('supplier.distribution');
    Route::patch('/supplier/distribution/{order}', [SupplierCenterController::class, 'updateDelivery'])->name('supplier.distribution.update');
    Route::get('/supplier/distribution/{order}/proof', [SupplierCenterController::class, 'downloadDeliveryProof'])->name('supplier.distribution.proof');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{order}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{order}/invoice', [TransactionController::class, 'invoice'])->name('transactions.invoice');
    Route::get('/transactions/{order}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::get('/transactions/{order}/payment-proof', [TransactionController::class, 'paymentProof'])->name('transactions.payment-proof');
    Route::post('/products/{product:slug}/orders', [TransactionController::class, 'store'])->name('products.orders.store');
    Route::post('/transactions/{order}/qris', [TransactionController::class, 'createQris'])->name('transactions.qris.create');
    Route::get('/transactions/{order}/qris-image', [TransactionController::class, 'qrisImage'])->name('transactions.qris.image');
    Route::post('/transactions/{order}/payment', [TransactionController::class, 'submitPayment'])->name('transactions.payment.submit');
    Route::get('/transactions/{order}/delivery-proof', [TransactionController::class, 'deliveryProof'])->name('transactions.delivery-proof');
    Route::patch('/transactions/{order}/payment', [TransactionController::class, 'verifyPayment'])->name('transactions.payment.verify');
    Route::patch('/transactions/{order}/status', [TransactionController::class, 'updateStatus'])->name('transactions.status.update');
    Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store'])->name('products.reviews.store');
    Route::get('/chatbot', [ChatbotController::class, 'index'])
    ->name('chatbot.index');

    Route::post('/chatbot/send', [ChatbotController::class, 'sendMessage'])
    ->middleware('throttle:20,1')
    ->name('chatbot.send');
});

Route::middleware(['auth', 'active.account', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/products', [AdminModerationController::class, 'products'])->name('products.index');
    Route::patch('/products/{product}/visibility', [AdminModerationController::class, 'updateProductVisibility'])->name('products.visibility.update');
    Route::delete('/products/{product}', [AdminModerationController::class, 'destroyProduct'])->name('products.destroy');
    Route::get('/users', [AdminModerationController::class, 'users'])->name('users.index');
    Route::patch('/users/{user}/status', [AdminModerationController::class, 'updateUserStatus'])->name('users.status.update');
    Route::get('/suppliers', [AdminSupplierVerificationController::class, 'index'])->name('suppliers.index');
    Route::patch('/suppliers/{supplier}/verification', [AdminSupplierVerificationController::class, 'update'])->name('suppliers.verification.update');
    Route::get('/suppliers/{supplier}/nib-document', [AdminSupplierVerificationController::class, 'nibDocument'])->name('suppliers.nib-document');
});
