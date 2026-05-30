<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Show the employees page.
     */
    public function index()
    {
        return view('employees');
    }

    /**
     * Get list of employees for the UI.
     */
    public function list(Request $request)
    {
        $query = Employee::query();

        if ($search = $request->query('q')) {
            $query->where(function ($sub) use ($search) {
                $sub->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('created_at', 'desc')
            ->get(['id', 'employee_id', 'position', 'department', 'gender', 'salary', 'hire_date', 'employment_status', 'phone']);

        return response()->json(['success' => true, 'employees' => $employees]);
    }

    /**
     * Create a new employee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|unique:employees,employee_id',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other',
            'salary' => 'required|numeric|min:0',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'hire_date' => 'required|date',
            'employment_status' => 'nullable|string|in:Active,Inactive,On Leave,Terminated',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();
        $employee = Employee::create($validated);

        return response()->json(['success' => true, 'message' => 'Employee created successfully.', 'employee' => $employee], 201);
    }

    /**
     * Show a single employee.
     */
    public function show(Employee $employee)
    {
        return response()->json(['success' => true, 'employee' => $employee]);
    }

    /**
     * Update an existing employee.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|unique:employees,employee_id,' . $employee->id,
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other',
            'salary' => 'required|numeric|min:0',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'hire_date' => 'required|date',
            'employment_status' => 'nullable|string|in:Active,Inactive,On Leave,Terminated',
            'notes' => 'nullable|string|max:1000',
        ]);

        $employee->update($validated);

        return response()->json(['success' => true, 'message' => 'Employee updated successfully.', 'employee' => $employee]);
    }

    /**
     * Delete an employee.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return response()->json(['success' => true, 'message' => 'Employee deleted successfully.']);
    }
}
