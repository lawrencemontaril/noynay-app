<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\User;
use App\Http\Requests\{StoreUserRequest, UpdateUserRequest};
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $users = User::search(request('q'))
            ->when(request()->filled('role') && request('role') !== 'all', fn ($q) =>
                $q->role(request('role'))
            )
            ->when(request()->filled('is_active') && request('is_active') !== 'all', fn ($q) =>
                $q->where('is_active', request()->boolean('is_active'))
            )
            ->orderBy('last_name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/users/UsersIndex', [
            'users' => $users->toResourceCollection(),
            'filters' => $request->only(['q', 'role', 'is_active'])
        ]);
    }

    /**
     * API: User search endpoint.
     */
    public function search()
    {
        Gate::authorize('viewAny', User::class);

        $users = User::role(request('role'))
            ->search(request('q'))
            ->when(request('withoutRecord'), function ($q) {
                $q->whereDoesntHave('patient');
            })
            ->limit(30)
            ->get();

        return $users->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->safe()->except('role'));

        $user->syncRoles($request->safe()->only('role'));

        return redirect()
            ->back()
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        Gate::authorize('view', $user);

        return Inertia::render('admin/users/UsersShow', [
            'user' => $user->toResource(),
            'activities' => Inertia::optional(
                fn () => ActivityResource::collection($user->activities()->with('causer')->latest()->get())
            )
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->safe()->except('role'));

        $user->syncRoles($request->safe()->only('role'));

        return redirect()
            ->back()
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return back()
            ->with('success', 'User deleted successfully.');
    }
}
