<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function stats()
    {
        $totalUsers = User::count();
        $totalEmployees = Employee::count();
        $totalDepartments = Employee::distinct('department')->count('department');
        $newThisMonth = Employee::where('hire_date', '>=', Carbon::now()->startOfMonth())->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_users' => $totalUsers,
                'total_employees' => $totalEmployees,
                'total_departments' => $totalDepartments,
                'new_this_month' => $newThisMonth,
            ]
        ]);
    }

    /**
     * Get employees by department for chart.
     */
    public function employeesByDepartment()
    {
        $data = Employee::select('department', DB::raw('count(*) as count'))
            ->groupBy('department')
            ->orderByDesc('count')
            ->get();

        $labels = $data->pluck('department')->toArray();
        $counts = $data->pluck('count')->toArray();

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'counts' => $counts,
        ]);
    }

    /**
     * Get recent employees.
     */
    public function recentEmployees()
    {
        $employees = Employee::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['employee_id', 'position', 'department', 'hire_date']);

        return response()->json([
            'success' => true,
            'employees' => $employees,
        ]);
    }

    /**
     * Get recent users.
     */
    public function recentUsers()
    {
        $users = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at']);

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }
}
