<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TablesController;

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

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'processLogin']);
Route::get('/logout', [AuthController::class, 'logout']);

// Protected Routes (require login)
Route::middleware(['auth.custom'])->group(function () {
    // Dashboard Routes
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/index.php', [DashboardController::class, 'index']);
    Route::get('/index-2.php', [DashboardController::class, 'crm']);
    Route::get('/index-3.php', [DashboardController::class, 'ecommerce']);
    Route::get('/index-4.php', [DashboardController::class, 'cryptocurrency']);
    Route::get('/index-5.php', [DashboardController::class, 'investment']);
    Route::get('/index-6.php', [DashboardController::class, 'lms']);
    Route::get('/index-7.php', [DashboardController::class, 'nftGaming']);
    Route::get('/index-8.php', [DashboardController::class, 'medical']);
    Route::get('/index-9.php', [DashboardController::class, 'analytics']);
    Route::get('/index-10.php', [DashboardController::class, 'posInventory']);
    
    // User Management
    Route::get('/users.php', [UserController::class, 'index']);
    Route::get('/add-user.php', [UserController::class, 'create']);
    Route::post('/add-user.php', [UserController::class, 'store']);
    
    // Project Management
    Route::get('/projects.php', [ProjectController::class, 'index']);
    Route::get('/project_new.php', [ProjectController::class, 'create']);
    Route::post('/project_new.php', [ProjectController::class, 'store']);
    
    // Activity Management
    Route::get('/activity.php', [ActivityController::class, 'index']);
    Route::get('/activity_crud_new.php', [ActivityController::class, 'create']);
    Route::post('/activity_crud_new.php', [ActivityController::class, 'store']);
    
    // Customer Management
    Route::get('/customer.php', [CustomerController::class, 'index']);
    Route::get('/customer_crud_new.php', [CustomerController::class, 'create']);
    Route::post('/customer_crud_new.php', [CustomerController::class, 'store']);
    
    // Tables Routes
    Route::get('/group.php', [TablesController::class, 'group']);
    Route::post('/group.php', [TablesController::class, 'storeGroup']);
    Route::put('/group.php', [TablesController::class, 'updateGroup']);
    Route::get('/typography.php', [TablesController::class, 'typography']);
    Route::get('/colors.php', [TablesController::class, 'colors']);
    Route::get('/button.php', [TablesController::class, 'button']);
    Route::get('/dropdown.php', [TablesController::class, 'dropdown']);
    Route::get('/alert.php', [TablesController::class, 'alert']);
    Route::get('/card.php', [TablesController::class, 'card']);
    Route::get('/carousel.php', [TablesController::class, 'carousel']);
    Route::get('/avatar.php', [TablesController::class, 'avatar']);
    Route::get('/progress.php', [TablesController::class, 'progress']);
    Route::get('/tabs.php', [TablesController::class, 'tabs']);
    Route::get('/pagination.php', [TablesController::class, 'pagination']);
    Route::get('/badges.php', [TablesController::class, 'badges']);
    
    // Other Pages
    Route::get('/view-profile.php', [UserController::class, 'profile']);
    Route::get('/settings.php', [UserController::class, 'settings']);
});


