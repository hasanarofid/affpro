<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class AdminAccountController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::role(['superadmin', 'admin'])->with('roles')->select('users.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name_val', function ($user) {
                    return '<div class="fw-medium text-dark">' . $user->name . '</div>';
                })
                ->addColumn('role_badge', function ($user) {
                    $badges = '';
                    foreach ($user->roles as $role) {
                        $color = $role->name === 'superadmin' ? 'danger' : 'primary';
                        $badges .= '<span class="badge bg-' . $color . ' me-1">' . ucfirst($role->name) . '</span>';
                    }
                    return $badges;
                })
                ->addColumn('status_badge', function ($user) {
                    $status = $user->is_active ? 'Aktif' : 'Nonaktif';
                    $color = $user->is_active ? 'success' : 'secondary';
                    return '<span class="badge bg-' . $color . '">' . $status . '</span>';
                })
                ->addColumn('action', function ($user) {
                    if ($user->id === auth()->id())
                        return '<span class="text-muted small">Current User</span>';

                    if (config('app.demo_mode')) {
                        return '<span class="text-muted small">Read Only</span>';
                    }

                    $btn = '<div class="d-flex gap-1">';
                    $btn .= '<button type="button" @click="editAdmin(' . $user->id . ')" class="btn btn-sm btn-light-primary"><i class="bi bi-pencil"></i></button>';
                    $btn .= '<form id="delete-admin-' . $user->id . '" action="' . route('admin.administrators.destroy', $user->id) . '" method="POST">';
                    $btn .= csrf_field() . method_field('DELETE');
                    $btn .= '<button type="button" onclick="confirmDelete(\'delete-admin-' . $user->id . '\')" class="btn btn-sm btn-light-danger"><i class="bi bi-trash"></i></button>';
                    $btn .= '</form></div>';
                    return $btn;
                })
                ->rawColumns(['name_val', 'role_badge', 'status_badge', 'action'])
                ->make(true);
        }

        $roles = Role::whereIn('name', ['superadmin', 'admin'])->get();
        return view('admin.administrators.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $settings = app(\App\Services\SettingService::class);
        if ($request->filled('phone')) {
            $request->merge(['phone' => $settings->formatPhone($request->phone)]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => 'required|in:superadmin,admin',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        return redirect()->back()->with('success', 'Admin berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $settings = app(\App\Services\SettingService::class);
        if ($request->filled('phone')) {
            $request->merge(['phone' => $settings->formatPhone($request->phone)]);
        }

        if ($user->id === auth()->id() && !auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:superadmin,admin',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        return redirect()->back()->with('success', 'Data admin berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus diri sendiri.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'Admin berhasil dihapus.');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->roles->first()?->name,
        ]);
    }
}
