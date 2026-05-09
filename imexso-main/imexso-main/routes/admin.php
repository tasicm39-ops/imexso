<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FavouriteController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\SavedSearchController;
use App\Http\Controllers\Admin\SitePageController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('admin.dashboard');

Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
Route::post('users/{user}/approve', [UserController::class, 'approve'])->name('admin.users.approve');
Route::post('users/{user}/reject', [UserController::class, 'reject'])->name('admin.users.reject');
Route::post('users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('admin.users.toggle-admin');
Route::post('users/{user}/client-id', [UserController::class, 'updateClientId'])->name('admin.users.update-client-id');

Route::get('cars', [CarController::class, 'index'])->name('admin.cars.index');
Route::get('cars/import', [CarController::class, 'create'])->name('admin.cars.import');
Route::post('cars/import', [CarController::class, 'store'])->name('admin.cars.import.store');
Route::get('cars/{car}/marketing', [CarController::class, 'marketing'])->name('admin.cars.marketing');
Route::post('cars/{car}/marketing', [CarController::class, 'updateMarketing'])->name('admin.cars.marketing.update');

Route::get('favourites', [FavouriteController::class, 'index'])->name('admin.favourites.index');
Route::get('offers', [OfferController::class, 'index'])->name('admin.offers.index');

Route::get('saved-searches', [SavedSearchController::class, 'index'])->name('admin.saved-searches.index');

Route::get('events', [EventController::class, 'index'])->name('admin.events.index');

Route::get('site-pages', [SitePageController::class, 'index'])->name('admin.site-pages.index');
Route::get('site-pages/{site_page}/edit', [SitePageController::class, 'edit'])->name('admin.site-pages.edit');
Route::put('site-pages/{site_page}', [SitePageController::class, 'update'])->name('admin.site-pages.update');

Route::get('announcements', [AnnouncementController::class, 'index'])->name('admin.announcements.index');
Route::get('announcements/create', [AnnouncementController::class, 'create'])->name('admin.announcements.create');
Route::post('announcements', [AnnouncementController::class, 'store'])->name('admin.announcements.store');
Route::get('announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('admin.announcements.edit');
Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('admin.announcements.update');
Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('admin.announcements.destroy');
