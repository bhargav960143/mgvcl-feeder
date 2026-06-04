<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Circle;
use App\Models\Division;
use App\Models\SubDivision;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')->orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'employee_id'       => ['required', 'string', 'max:50', 'unique:users'],
            'email'             => ['required', 'email', 'max:191', 'unique:users'],
            'phone'             => ['nullable', 'string', 'max:15'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'role'              => ['required', Rule::in(['admin', 'circle', 'division_manager', 'sub_division_manager'])],
            'jurisdiction_type' => ['required', Rule::in(['global', 'circle', 'division', 'sub_division'])],
            'jurisdiction_id'   => ['nullable', 'integer'],
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'employee_id'       => $data['employee_id'],
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'password'          => Hash::make($data['password']),
            'jurisdiction_type' => $data['jurisdiction_type'],
            'jurisdiction_id'   => $data['jurisdiction_id'] ?? null,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('admin.users.index')
            ->with('success', "User [{$user->name}] created successfully.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', array_merge($this->formData(), compact('user')));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'employee_id'       => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email'             => ['required', 'email', 'max:191', Rule::unique('users')->ignore($user->id)],
            'phone'             => ['nullable', 'string', 'max:15'],
            'password'          => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'              => ['required', Rule::in(['admin', 'circle', 'division_manager', 'sub_division_manager'])],
            'jurisdiction_type' => ['required', Rule::in(['global', 'circle', 'division', 'sub_division'])],
            'jurisdiction_id'   => ['nullable', 'integer'],
        ]);

        $updateData = [
            'name'              => $data['name'],
            'employee_id'       => $data['employee_id'],
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'jurisdiction_type' => $data['jurisdiction_type'],
            'jurisdiction_id'   => $data['jurisdiction_id'] ?? null,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$data['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', "User [{$user->name}] updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted.');
    }

    private function formData(): array
    {
        return [
            'roles'        => ['admin', 'circle', 'division_manager', 'sub_division_manager'],
            'circles'      => Circle::orderBy('name')->get(),
            'divisions'    => Division::with('circle')->orderBy('name')->get(),
            'subDivisions' => SubDivision::with('division.circle')->orderBy('name')->get(),
        ];
    }
}
