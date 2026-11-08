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
        $this->authorize('manageStaff', User::class);

        $category = $request->query('category');

        $users = User::query()
            ->whereIn('user_type', ['admin', 'staff'])
            ->when($category, fn ($q) => $q->where('user_type', $category))
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
            'modules' => Permission::query()->where('slug', 'like', 'module.%')->orderBy('name')->get(),
            'openUserId' => $request->integer('open') ?: null,
        ]);
    }

    public function create(): RedirectResponse
    {
        // Creating happens inline on the index page now, not on this page's
        // own route — links elsewhere in the admin still point here, so keep
        // this route alive as a redirect.
        return redirect()->route('admin.users.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manageStaff', User::class);

        $validated = $this->validated($request);

        // A staff account granted the "users" module may create other staff,
        // but creating a new admin account is owner-only — otherwise that
        // module grant would be a privilege-escalation path to full access.
        abort_if($validated['user_type'] === 'admin' && ! $request->user()->isAdmin(), 403);

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

        $redirectTo = \App\Support\SafeRedirect::resolve($request->input('redirect'), route('admin.users.index'));

        return redirect($redirectTo)->with('status', 'User created.');
    }

    public function edit(User $user): RedirectResponse
    {
        $this->authorize('editStaffAccount', $user);
        abort_unless(in_array($user->user_type, ['admin', 'staff'], true), 404);

        // Editing happens inline on the index page now, not on this page's
        // own route — keep this route alive as a redirect for old links.
        return redirect()->route('admin.users.index', ['open' => $user->id]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('editStaffAccount', $user);
        abort_unless(in_array($user->user_type, ['admin', 'staff'], true), 404);

        $validated = $this->validated($request, $user);
        $actor = $request->user();

        // A non-admin editing their own account (allowed, so staff can fix
        // their own name/email here) must not be able to change their own
        // role — only an admin may promote/demote anyone, including via
        // their own account, which can't happen here since isAdmin() is
        // already true and this branch is skipped.
        if (! $actor->isAdmin() && $actor->is($user)) {
            $validated['user_type'] = $user->user_type;
        }

        // Promoting anyone to admin, by anyone other than an admin, is the
        // same escalation path blocked on create — close it here too.
        abort_if($validated['user_type'] === 'admin' && ! $actor->isAdmin(), 403);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->user_type = $validated['user_type'];
        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }
        $user->save();

        // Same self-escalation guard for module grants: a non-admin editing
        // their own account keeps whatever permissions they already had.
        if (! $actor->isAdmin() && $actor->is($user)) {
            $validated['permissions'] = $user->permissions()->pluck('permissions.id')->all();
        }

        $user->permissions()->sync($validated['user_type'] === 'staff' ? ($validated['permissions'] ?? []) : []);

        AuditLogger::log('admin.users.updated', $user, [], ['user_type' => $user->user_type]);

        $redirectTo = \App\Support\SafeRedirect::resolve($request->input('redirect'), route('admin.users.index', ['open' => $user->id]));

        return redirect($redirectTo)->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('editStaffAccount', $user);
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
