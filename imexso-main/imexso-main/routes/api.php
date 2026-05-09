<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\ApiClientController;
use App\Http\Controllers\Api\CarCartController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\CarImportController;
use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SaleHistoryController;
use App\Http\Controllers\Api\SavedSearchController;
use App\Http\Controllers\Api\SegmentEventController;
use App\Http\Controllers\Api\SitePageController;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\VatValidationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('translations/{locale}', [TranslationController::class, 'index'])->name('api.translations');

Route::post('vat/validate', [VatValidationController::class, 'validate'])
    ->middleware('throttle:10,1')
    ->name('api.vat.validate');

Route::get('site-pages/{slug}', [SitePageController::class, 'show'])->name('api.site-pages.show');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    })->name('api.user');

    Route::post('segment-events', [SegmentEventController::class, 'store'])->name('api.segment-events.store');
    Route::post('profile', [ProfileController::class, 'update'])->name('api.profile.update');

    Route::middleware('validated')->group(function () {
        Route::get('cars/filters', [CarController::class, 'filters'])->name('api.cars.filters');
        Route::get('cars', [CarController::class, 'index'])->name('api.cars.index');
        Route::get('cars/{car:id_produit}', [CarController::class, 'show'])->name('api.cars.show');

        Route::get('favourites', [FavouriteController::class, 'index'])->name('api.favourites.index');
        Route::get('favourites/car-ids', [FavouriteController::class, 'carIds'])->name('api.favourites.car-ids');
        Route::post('favourites', [FavouriteController::class, 'store'])->name('api.favourites.store');
        Route::delete('favourites/{car}', [FavouriteController::class, 'destroy'])->name('api.favourites.destroy');

        Route::get('cart/car-ids', [CarCartController::class, 'carIds'])->name('api.cart.car-ids');
        Route::get('cart', [CarCartController::class, 'index'])->name('api.cart.index');
        Route::post('cart', [CarCartController::class, 'store'])->name('api.cart.store');
        Route::delete('cart/{car}', [CarCartController::class, 'destroy'])->name('api.cart.destroy');

        Route::get('saved-searches', [SavedSearchController::class, 'index'])->name('api.saved-searches.index');
        Route::post('saved-searches', [SavedSearchController::class, 'store'])->name('api.saved-searches.store');
        Route::delete('saved-searches/{savedSearch}', [SavedSearchController::class, 'destroy'])->name('api.saved-searches.destroy');

        Route::get('sale-histories', [SaleHistoryController::class, 'index'])->name('api.sale-histories.index');
        Route::get('sale-histories/{saleHistory}', [SaleHistoryController::class, 'show'])->name('api.sale-histories.show');

        Route::post('cars/{car:id_produit}/offers/pdf', [OfferController::class, 'generatePdf'])->name('api.offers.pdf');
        Route::post('cars/{car:id_produit}/offers/email', [OfferController::class, 'sendEmail'])->name('api.offers.email');

        Route::get('orders', [OrderController::class, 'index'])->name('api.orders.index');
        Route::post('orders', [OrderController::class, 'store'])->name('api.orders.store');

        Route::get('announcements/active', [AnnouncementController::class, 'active'])->name('api.announcements.active');
    });

    Route::middleware('admin')->group(function () {
        Route::post('cars/import', [CarImportController::class, 'store'])->name('api.cars.import');

        Route::get('segment-events', [SegmentEventController::class, 'index'])->name('api.segment-events.index');

        Route::get('api-clients', [ApiClientController::class, 'index'])->name('api.api-clients.index');
        Route::post('api-clients', [ApiClientController::class, 'store'])->name('api.api-clients.store');
        Route::get('api-clients/{apiClient}', [ApiClientController::class, 'show'])->name('api.api-clients.show');
        Route::post('api-clients/{apiClient}/tokens', [ApiClientController::class, 'generateToken'])->name('api.api-clients.generate-token');
        Route::delete('api-clients/{apiClient}/tokens', [ApiClientController::class, 'revokeTokens'])->name('api.api-clients.revoke-tokens');
    });
});
