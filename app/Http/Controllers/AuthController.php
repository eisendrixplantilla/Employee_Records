<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegister()
    {
        return view('register');
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'position' => 'required|string|max:255',
                'department' => 'required|string|in:HR,IT,Finance,Sales,Marketing,Operations',
                'gender' => 'required|string|in:Male,Female,Other',
                'salary' => 'required|numeric|min:0',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'position' => $validated['position'],
                'department' => $validated['department'],
                'gender' => $validated['gender'],
                'salary' => $validated['salary'],
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully!',
                'user' => $user
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Show the login form
     */
    public function showLogin()
    {
        return view('login');
    }

    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
                'remember' => 'nullable|boolean',
            ]);

            $credentials = $request->only('email', 'password');
            $remember = $request->boolean('remember');

            if (!Auth::attempt($credentials, $remember)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Logged in successfully!',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully!'
        ]);
    }
}
