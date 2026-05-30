<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Show the users page.
     */
    public function index()
    {
        return view('users');
    }

    /**
     * Return user list for the UI.
     */
    public function list(Request $request)
    {
        $query = User::query();

        if ($search = $request->query('q')) {
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'email', 'position', 'department', 'gender', 'salary', 'created_at']);

        return response()->json(['success' => true, 'users' => $users]);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|in:HR,IT,Finance,Sales,Marketing,Operations',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'salary' => 'nullable|numeric|min:0',
            'password' => 'required|string|min:6',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        if (!empty($validated['position'])) {
            $userData['position'] = $validated['position'];
        }
        if (!empty($validated['department'])) {
            $userData['department'] = $validated['department'];
        }
        if (!empty($validated['gender'])) {
            $userData['gender'] = $validated['gender'];
        }
        if (isset($validated['salary'])) {
            $userData['salary'] = $validated['salary'];
        }

        $user = User::create($userData);

        return response()->json(['success' => true, 'message' => 'User created successfully.', 'user' => $user], 201);
    }

    /**
     * Show a single user.
     */
    public function show(User $user)
    {
        return response()->json(['success' => true, 'user' => $user->only(['id','name','email','position','department','gender','salary'])]);
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ];

        if ($request->filled('password') || $request->filled('password_confirmation') || $request->filled('current_password')) {
            $rules['current_password'] = 'required|string';
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($request->filled('password')) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['current_password' => ['Current password is incorrect.']],
                ], 422);
            }

            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'User updated successfully.', 'user' => $user]);
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
    }
}
