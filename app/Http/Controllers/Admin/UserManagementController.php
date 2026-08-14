<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInternalUserRequest;
use App\Http\Requests\Admin\UpdateInternalUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    /**
     * Menampilkan daftar seluruh pengguna internal yang masih aktif secara data.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $role = (string) $request->query('role', '');
        $status = (string) $request->query('status', '');

        if (! in_array($role, User::internalRoles(), true)) {
            $role = '';
        }

        if (! in_array($status, ['active', 'inactive'], true)) {
            $status = '';
        }

        $baseQuery = User::query()
            ->where('account_type', User::ACCOUNT_INTERNAL)
            ->whereIn('role', User::internalRoles());

        $statistics = [
            'total' => (clone $baseQuery)->count(),
            'owners' => (clone $baseQuery)->where('role', User::ROLE_OWNER)->count(),
            'admins' => (clone $baseQuery)->where('role', User::ROLE_ADMIN)->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
        ];

        $users = $baseQuery
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderByRaw("CASE WHEN role = 'owner' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'statistics' => $statistics,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Menampilkan formulir pembuatan pengguna internal.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => User::internalRoles(),
        ]);
    }

    /**
     * Menyimpan pengguna internal baru.
     */
    public function store(StoreInternalUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'account_type' => User::ACCOUNT_INTERNAL,
            'role' => $validated['role'],
            'is_active' => (bool) $validated['is_active'],
        ]);

        $this->activityLogger->record(
            $request->user(),
            ActivityLog::ACTION_CREATE,
            ActivityLog::MODULE_USER_MANAGEMENT,
            "Membuat akun {$user->name} sebagai {$user->roleLabel()}.",
            null,
            $this->auditableUserValues($user),
            $request,
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil dibuat sebagai {$user->roleLabel()}.");
    }

    /**
     * Menampilkan formulir perubahan pengguna internal.
     */
    public function edit(User $user): View
    {
        $this->ensureInternalUser($user);

        return view('admin.users.edit', [
            'managedUser' => $user,
            'roles' => User::internalRoles(),
        ]);
    }

    /**
     * Memperbarui data pengguna internal.
     */
    public function update(UpdateInternalUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureInternalUser($user);

        $validated = $request->validated();
        $authenticatedUser = $request->user();
        $requestedRole = $validated['role'];
        $requestedActiveState = (bool) $validated['is_active'];

        if ($authenticatedUser?->is($user) && $requestedRole !== User::ROLE_OWNER) {
            return back()
                ->withInput()
                ->with('error', 'Anda tidak dapat menurunkan role akun Owner yang sedang digunakan.');
        }

        if ($authenticatedUser?->is($user) && ! $requestedActiveState) {
            return back()
                ->withInput()
                ->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang digunakan untuk masuk ke sistem.');
        }

        if ($this->wouldRemoveLastActiveOwner($user, $requestedRole, $requestedActiveState)) {
            return back()
                ->withInput()
                ->with('error', 'Perubahan dibatalkan karena sistem wajib memiliki minimal satu akun Owner aktif.');
        }

        $oldValues = $this->auditableUserValues($user);
        $oldRole = $user->role;
        $passwordChanged = ! empty($validated['password']);

        $payload = [
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'role' => $requestedRole,
            'is_active' => $requestedActiveState,
        ];

        if ($passwordChanged) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);
        $user->refresh();

        $newValues = $this->auditableUserValues($user);

        $this->activityLogger->record(
            $authenticatedUser,
            ActivityLog::ACTION_UPDATE,
            ActivityLog::MODULE_USER_MANAGEMENT,
            "Memperbarui data akun {$user->name}.",
            $oldValues,
            $newValues,
            $request,
        );

        if ($oldRole !== $user->role) {
            $this->activityLogger->record(
                $authenticatedUser,
                ActivityLog::ACTION_ROLE_CHANGE,
                ActivityLog::MODULE_USER_MANAGEMENT,
                "Mengubah role {$user->name} dari ".Str::title($oldRole).' menjadi '.$user->roleLabel().'.',
                ['role' => $oldRole],
                ['role' => $user->role],
                $request,
            );
        }

        if ($passwordChanged) {
            $this->activityLogger->record(
                $authenticatedUser,
                ActivityLog::ACTION_PASSWORD_CHANGE,
                ActivityLog::MODULE_USER_MANAGEMENT,
                "Mengubah kata sandi akun {$user->name} melalui User Management.",
                null,
                ['password_changed' => true],
                $request,
            );
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Mengaktifkan atau menonaktifkan akun pengguna internal.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $this->ensureInternalUser($user);

        if ($request->user()?->is($user)) {
            return back()->with('error', 'Status akun yang sedang Anda gunakan tidak dapat diubah dari halaman ini.');
        }

        $newState = ! $user->is_active;

        if ($this->wouldRemoveLastActiveOwner($user, $user->role, $newState)) {
            return back()->with('error', 'Owner terakhir yang masih aktif tidak dapat dinonaktifkan.');
        }

        $oldValues = ['is_active' => (bool) $user->is_active];

        $user->forceFill(['is_active' => $newState])->save();

        $this->activityLogger->record(
            $request->user(),
            $newState ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
            ActivityLog::MODULE_USER_MANAGEMENT,
            $newState
                ? "Mengaktifkan akun {$user->name}."
                : "Menonaktifkan akun {$user->name}.",
            $oldValues,
            ['is_active' => $newState],
            $request,
        );

        return back()->with(
            'success',
            $newState
                ? "Akun {$user->name} berhasil diaktifkan."
                : "Akun {$user->name} berhasil dinonaktifkan."
        );
    }

    /**
     * Menghapus pengguna internal menggunakan soft delete.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureInternalUser($user);

        if ($request->user()?->is($user)) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan untuk masuk ke sistem.');
        }

        if ($user->isOwner() && $user->is_active && $this->activeOwnerCount() <= 1) {
            return back()->with('error', 'Owner terakhir yang masih aktif tidak dapat dihapus.');
        }

        $name = $user->name;
        $oldValues = $this->auditableUserValues($user);

        $user->delete();

        $this->activityLogger->record(
            $request->user(),
            ActivityLog::ACTION_DELETE,
            ActivityLog::MODULE_USER_MANAGEMENT,
            "Menghapus akun {$name} dari daftar pengguna aktif menggunakan soft delete.",
            $oldValues,
            ['deleted_at' => $user->deleted_at?->toDateTimeString()],
            $request,
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$name} berhasil dihapus dari pengguna aktif.");
    }

    /**
     * Memastikan route model binding hanya memproses akun internal Owner/Admin.
     */
    private function ensureInternalUser(User $user): void
    {
        if (! $user->isInternalUser()) {
            abort(404);
        }
    }

    /**
     * Menghitung jumlah Owner aktif yang belum dihapus.
     */
    private function activeOwnerCount(): int
    {
        return User::query()
            ->where('account_type', User::ACCOUNT_INTERNAL)
            ->where('role', User::ROLE_OWNER)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Mencegah perubahan role/status yang membuat sistem kehilangan Owner aktif.
     */
    private function wouldRemoveLastActiveOwner(User $user, string $newRole, bool $newActiveState): bool
    {
        if (! $user->isOwner() || ! $user->is_active) {
            return false;
        }

        $removesOwnerAccess = $newRole !== User::ROLE_OWNER || ! $newActiveState;

        return $removesOwnerAccess && $this->activeOwnerCount() <= 1;
    }

    /**
     * Menentukan atribut pengguna yang aman disimpan pada audit trail.
     *
     * @return array<string, mixed>
     */
    private function auditableUserValues(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'is_active' => (bool) $user->is_active,
        ];
    }
}
