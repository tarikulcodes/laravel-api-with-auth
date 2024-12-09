<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $queryParams = request()->only(['sort', 'order', 'per_page', 'search']) + [
            'sort'     => 'id',
            'order'    => 'desc',
            'per_page' => 10,
        ];

        $query = User::query()
            ->search(request('search'))
            ->withRole(request('role'))
            ->orderBy($queryParams['sort'], $queryParams['order']);

        $users = $query->paginate($queryParams['per_page'])->withQueryString();

        return UserResource::collection($users)->additional(['queryParams' =>  $queryParams]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $inputs = $request->validated();
        $profileImage = $inputs['profile_image'] ??  null;
        $roles = $inputs['roles'] ??  [];
        unset($inputs['profile_image'], $inputs['roles']);

        DB::beginTransaction();
        $user = User::create($inputs);

        if ($profileImage) {
            $user
                ->addMedia($profileImage)
                ->toMediaCollection('profile_images');
        }

        $user->syncRoles($roles);
        DB::commit();

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $inputs = $request->validated();
        $profileImage = $inputs['profile_image'] ?? null;
        $roles = $inputs['roles'] ??  [];
        $updateSlug = $inputs['update_slug'] ??  null;
        unset($inputs['profile_image'], $inputs['roles'], $inputs['update_slug']);

        DB::beginTransaction();
        if ($user->fill($inputs)->isDirty()) {
            $user->save();
        }

        if ($profileImage) {
            if ($user->hasMedia('profile_images')) {
                $user->clearMediaCollection('profile_images');
            }

            $user->addMedia($profileImage)->toMediaCollection('profile_images');
        }

        $user->syncRoles($roles);

        if ($updateSlug) {
            $user->generateSlug();
            $user->save();
        }

        DB::commit();

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent();
    }
}
