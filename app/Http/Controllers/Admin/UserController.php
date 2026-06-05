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
        $auth = auth()->user();

        if ($auth->hasRole('circle')) {
            $divisionIds    = Division::where('circle_id', $auth->jurisdiction_id)->pluck('id');
            $subDivisionIds = SubDivision::whereIn('division_id', $divisionIds)->pluck('id');

            $users = User::with('roles')
                ->where(function ($q) use ($divisionIds, $subDivisionIds) {
                    $q->where(function ($q2) use ($divisionIds) {
                        $q2->where('jurisdiction_type', 'division')
                           ->whereIn('jurisdiction_id', $divisionIds);
                    })->orWhere(function ($q2) use ($subDivisionIds) {
                        $q2->where('jurisdiction_type', 'sub_division')
                           ->whereIn('jurisdiction_id', $subDivisionIds);
                    });
                })
                ->orderBy('name')
                ->paginate(20);
        } else {
            $users = User::with('roles')->orderBy('name')->paginate(20);
        }

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $auth      = auth()->user();
        $isCircle  = $auth->hasRole('circle');

        [$allowedRoles, $allowedJurisdictionTypes] = $this->allowedOptions($isCircle);

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'employee_id'       => ['nullable', 'string', 'max:50', 'unique:users'],
            'email'             => ['required', 'email', 'max:191', 'unique:users'],
            'phone'             => ['nullable', 'string', 'max:15'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'role'              => ['required', Rule::in($allowedRoles)],
            'jurisdiction_type' => ['required', Rule::in($allowedJurisdictionTypes)],
            'jurisdiction_id'   => ['nullable', 'integer'],
        ]);

        if ($isCircle) {
            $this->assertInCircleScope($data['jurisdiction_type'], $data['jurisdiction_id'], $auth->jurisdiction_id);
        }

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
        $this->authorizeCircleAccess($user);

        return view('admin.users.edit', array_merge($this->formData(), compact('user')));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeCircleAccess($user);

        $auth     = auth()->user();
        $isCircle = $auth->hasRole('circle');

        [$allowedRoles, $allowedJurisdictionTypes] = $this->allowedOptions($isCircle);

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'employee_id'       => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email'             => ['required', 'email', 'max:191', Rule::unique('users')->ignore($user->id)],
            'phone'             => ['nullable', 'string', 'max:15'],
            'password'          => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'              => ['required', Rule::in($allowedRoles)],
            'jurisdiction_type' => ['required', Rule::in($allowedJurisdictionTypes)],
            'jurisdiction_id'   => ['nullable', 'integer'],
        ]);

        if ($isCircle) {
            $this->assertInCircleScope($data['jurisdiction_type'], $data['jurisdiction_id'], $auth->jurisdiction_id);
        }

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

        $this->authorizeCircleAccess($user);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted.');
    }

    private function formData(): array
    {
        $auth = auth()->user();

        if ($auth->hasRole('circle')) {
            $circleId    = $auth->jurisdiction_id;
            $divisionIds = Division::where('circle_id', $circleId)->pluck('id');

            return [
                'roles'        => ['division_manager', 'sub_division_manager'],
                'circles'      => collect(),
                'divisions'    => Division::with('circle')->where('circle_id', $circleId)->orderBy('name')->get(),
                'subDivisions' => SubDivision::with('division.circle')->whereIn('division_id', $divisionIds)->orderBy('name')->get(),
            ];
        }

        return [
            'roles'        => ['admin', 'circle', 'division_manager', 'sub_division_manager'],
            'circles'      => Circle::orderBy('name')->get(),
            'divisions'    => Division::with('circle')->orderBy('name')->get(),
            'subDivisions' => SubDivision::with('division.circle')->orderBy('name')->get(),
        ];
    }

    private function allowedOptions(bool $isCircle): array
    {
        if ($isCircle) {
            return [
                ['division_manager', 'sub_division_manager'],
                ['division', 'sub_division'],
            ];
        }

        return [
            ['admin', 'circle', 'division_manager', 'sub_division_manager'],
            ['global', 'circle', 'division', 'sub_division'],
        ];
    }

    private function assertInCircleScope(string $type, ?int $id, int $circleId): void
    {
        if ($type === 'division') {
            abort_unless(
                Division::where('id', $id)->where('circle_id', $circleId)->exists(),
                403,
                'Division does not belong to your circle.'
            );
        } elseif ($type === 'sub_division') {
            $divisionIds = Division::where('circle_id', $circleId)->pluck('id');
            abort_unless(
                SubDivision::where('id', $id)->whereIn('division_id', $divisionIds)->exists(),
                403,
                'Sub-division does not belong to your circle.'
            );
        }
    }

    private function authorizeCircleAccess(User $target): void
    {
        $auth = auth()->user();

        if (! $auth->hasRole('circle')) {
            return;
        }

        // Circle cannot manage admin or circle users
        if ($target->hasAnyRole(['admin', 'circle'])) {
            abort(403, 'You cannot manage admin or circle users.');
        }

        // Circle can only manage users within own jurisdiction
        $divisionIds    = Division::where('circle_id', $auth->jurisdiction_id)->pluck('id');
        $subDivisionIds = SubDivision::whereIn('division_id', $divisionIds)->pluck('id');

        $inScope = match ($target->jurisdiction_type) {
            'division'     => $divisionIds->contains($target->jurisdiction_id),
            'sub_division' => $subDivisionIds->contains($target->jurisdiction_id),
            default        => false,
        };

        abort_unless($inScope, 403, 'User is outside your circle jurisdiction.');
    }
}
