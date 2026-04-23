<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Auth::routes();

// Dashboard routes
Route::get('/dashboard', 'DashboardController@index')->name('dashboard');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // AJAX Routes untuk dashboard user
    Route::get('/api/waste-types', 'DashboardController@getWasteTypes')->name('api.waste-types');
    Route::post('/api/waste-reports', 'DashboardController@storeWasteReport')->name('api.waste-reports.store');
    Route::get('/api/user/reports', 'DashboardController@getUserReports')->name('api.user.reports');
    Route::get('/api/reports/{id}', 'DashboardController@getReportDetail')->name('api.reports.detail');
    Route::put('/api/profile', 'DashboardController@updateProfile')->name('api.profile.update');
    Route::get('/api/reports-for-map', 'DashboardController@getAllReportsForMap')->name('api.reports.for-map');

    // Admin API Routes
    Route::middleware(['auth', 'role:admin|petugas'])->group(function () {
        Route::get('/api/admin/reports', 'AdminController@getAllReports')->name('api.admin.reports');
        Route::get('/api/admin/statistics', 'AdminController@getStatistics')->name('api.admin.statistics');
        Route::get('/api/admin/users', 'AdminController@getAllUsers')->name('api.admin.users');
        Route::post('/api/admin/users', 'AdminController@storeUser')->name('api.admin.users.store');
        Route::get('/api/admin/users/{id}', 'AdminController@getUser')->name('api.admin.users.show');
        Route::put('/api/admin/users/{id}', 'AdminController@updateUser')->name('api.admin.users.update');
        Route::delete('/api/admin/users/{id}', 'AdminController@deleteUser')->name('api.admin.users.delete');
        Route::put('/api/admin/users/{id}/toggle-status', 'AdminController@toggleUserStatus')->name('api.admin.users.toggle-status');
        Route::get('/api/admin/waste-types', 'AdminController@getAllWasteTypes')->name('api.admin.waste-types');
        Route::put('/api/admin/waste-types/{id}/toggle-status', 'AdminController@toggleWasteTypeStatus')->name('api.admin.waste-types.toggle-status');
        Route::post('/api/admin/reports/{id}/update-status', 'AdminController@updateReportStatus')->name('api.admin.reports.update-status');
        Route::post('/api/admin/reports/{id}/feedback', 'AdminController@addFeedback')->name('api.admin.reports.feedback');
        Route::get('/admin/reports/print', 'AdminController@printReports')->name('admin.reports.print');

        // District routes
        Route::get('/api/admin/districts', 'DistrictController@getAllDistricts')->name('api.admin.districts');
        Route::get('/api/admin/districts/{id}', 'DistrictController@getDistrict')->name('api.admin.districts.show');
        Route::post('/api/admin/districts', 'DistrictController@storeDistrict')->name('api.admin.districts.store');
        Route::put('/api/admin/districts/{id}', 'DistrictController@updateDistrict')->name('api.admin.districts.update');
        Route::delete('/api/admin/districts/{id}', 'DistrictController@deleteDistrict')->name('api.admin.districts.delete');
        Route::put('/api/admin/districts/{id}/toggle-status', 'DistrictController@toggleDistrictStatus')->name('api.admin.districts.toggle-status');
        Route::get('/api/admin/districts/{id}/statistics', 'DistrictController@getDistrictStatistics')->name('api.admin.districts.statistics');
        Route::post('/api/admin/districts/detect', 'DistrictController@detectDistrict')->name('api.admin.districts.detect');
    });

    // Placeholder routes (akan dihandle oleh modal)
    Route::get('/waste-reports/create', function () {
        return redirect()->route('dashboard');
    })->name('waste-reports.create');
    Route::get('/map', function () {
        return redirect()->route('dashboard');
    })->name('map');
    Route::get('/profile', function () {
        return redirect()->route('dashboard');
    })->name('profile');
});

// Remove the old home route since we're using dashboard now
// Route::get('/home', 'HomeController@index')->name('home');
