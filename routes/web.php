<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

Route::view('/', 'layouts.index');
Route::view('/employees', 'employees');
Route::get('/profile', [ProfileController::class, 'show'])->middleware('auth');
Route::get('/users', [UserController::class, 'index'])->middleware('auth');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister']);
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');

// API Routes
Route::post('/api/register', [AuthController::class, 'register']);
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/logout', [AuthController::class, 'logout']);
Route::get('/api/users', [UserController::class, 'list'])->middleware('auth');
Route::get('/api/users/{user}', [UserController::class, 'show'])->middleware('auth');
Route::post('/api/users', [UserController::class, 'store']);
Route::put('/api/users/{user}', [UserController::class, 'update'])->middleware('auth');
Route::delete('/api/users/{user}', [UserController::class, 'destroy'])->middleware('auth');

Route::get('/api/employees', [EmployeeController::class, 'list'])->middleware('auth');
Route::get('/api/employees/{employee}', [EmployeeController::class, 'show'])->middleware('auth');
Route::post('/api/employees', [EmployeeController::class, 'store'])->middleware('auth');
Route::put('/api/employees/{employee}', [EmployeeController::class, 'update'])->middleware('auth');
Route::delete('/api/employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('auth');

Route::put('/api/profile', [ProfileController::class, 'update'])->middleware('auth');

Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])->middleware('auth');
Route::get('/api/dashboard/employees-by-department', [DashboardController::class, 'employeesByDepartment'])->middleware('auth');
Route::get('/api/dashboard/recent-employees', [DashboardController::class, 'recentEmployees'])->middleware('auth');
Route::get('/api/dashboard/recent-users', [DashboardController::class, 'recentUsers'])->middleware('auth');
