<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|in:HR,IT,Finance,Sales,Marketing,Operations',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'salary' => 'nullable|numeric|min:0',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
        ]);

        if ($request->hasFile('profile_picture')) {
            $path = Storage::disk('public')->putFile('profile_pictures', $request->file('profile_picture'));
            $user->profile_picture = Storage::url($path);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->position = $validated['position'] ?? null;
        $user->department = $validated['department'] ?? null;
        $user->gender = $validated['gender'] ?? null;
        $user->salary = $validated['salary'] ?? null;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.', 'user' => $user]);
    }
}
