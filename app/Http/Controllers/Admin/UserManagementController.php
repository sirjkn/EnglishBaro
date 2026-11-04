<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        $users = User::query()
            ->whereIn('user_type', ['admin', 'staff'])
            ->when($category, fn ($q) => $q->where('user_type', $category))
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['user_type' => 'staff', 'is_active' => true]),
            'modules' => Permission::query()->where('slug', 'like', 'module.%')->orderBy('name')->get(),
            'grantedIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'user_type' => $validated['user_type'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        if ($validated['user_type'] === 'staff') {
            $user->permissions()->sync($validated['permissions'] ?? []);
        }

        AuditLogger::log('admin.users.created', $user, [], ['user_type' => $user->user_type]);

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        abort_unless(in_array($user->user_type, ['admin', 'staff'], true), 404);

        return view('admin.users.form', [
            'user' => $user,
            'modules' => Permission::query()->where('slug', 'like', 'module.%')->orderBy('name')->get(),
            'grantedIds' => $user->permissions->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->user_type, ['admin', 'staff'], true), 404);

        $validated = $this->validated($request, $user);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->user_type = $validated['user_type'];
        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }
        $user->save();

        $user->permissions()->sync($validated['user_type'] === 'staff' ? ($validated['permissions'] ?? []) : []);

        AuditLogger::log('admin.users.updated', $user, [], ['user_type' => $user->user_type]);

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->user_type, ['admin', 'staff'], true), 404);
        abort_if($user->is($request->user()), 422, "You can't delete your own account.");

        $user->delete();

        AuditLogger::log('admin.users.deleted', null, [], ['user_id' => $user->id, 'email' => $user->email]);

        return redirect()->route('admin.users.index')->with('status', 'User removed.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'.($user ? ','.$user->id : '')],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'user_type' => ['required', 'in:admin,staff'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        return $validated;
    }
}
