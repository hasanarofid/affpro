<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::role('customer')->with('roles')->select('users.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name_val', function ($user) {
                    return '<div class="fw-medium text-dark">' . $user->name . '</div>';
                })
                ->addColumn('role_badge', function ($user) {
                    $badges = '';
                    foreach ($user->roles as $role) {
                        $color = $role->name === 'admin' ? 'primary' : 'info';
                        $badges .= '<span class="badge bg-' . $color . ' me-1">' . ucfirst($role->name) . '</span>';
                    }
                    return $badges;
                })
                ->addColumn('status_badge', function ($user) {
                    $status = $user->is_active ? 'Aktif' : 'Nonaktif';
                    $color = $user->is_active ? 'success' : 'secondary';
                    return '<span class="badge badge-status bg-' . $color . '">' . $status . '</span>';
                })
                ->addColumn('joined_date', function ($user) {
                    return '<td class="text-muted small">' . $user->created_at->format('d M Y') . '</td>';
                })
                ->rawColumns(['name_val', 'role_badge', 'status_badge', 'joined_date'])
                ->make(true);
        }

        return view('admin.users.index');
    }
}
