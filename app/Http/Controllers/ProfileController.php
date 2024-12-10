<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeProfileImageRequest;
use App\Http\Requests\ChangeProfilePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show()
    {
        $user = request()->user();
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfileRequest $request)
    {
        $validatedInputs = $request->validated();
        $profileImage = $validatedInputs['profile_image'] ?? null;
        $updateSlug = $validatedInputs['update_slug'] ??  null;
        unset($validatedInputs['profile_image'], $validatedInputs['update_slug']);

        $user = request()->user();



        DB::beginTransaction();
        if ($user->fill($validatedInputs)->isDirty()) {
            $user->save();
        }

        if ($profileImage) {
            if ($user->hasMedia('profile_images')) {
                $user->clearMediaCollection('profile_images');
            }

            $user->addMedia($profileImage)->toMediaCollection('profile_images');
        }

        if ($updateSlug) {
            $user->generateSlug();
            $user->save();
        }

        DB::commit();

        $user->refresh();
        return new UserResource($user);
    }
    /**
     * Change password
     */
    public function changePassword(ChangeProfilePasswordRequest $request)
    {
        $validatedInputs = $request->validated();
        $user = request()->user();

        $user->update([
            'password' => $validatedInputs['new_password']
        ]);

        return response()->json([
            'message' => 'Password changed'
        ], 200);
    }

    /**
     * Change profile image
     */
    public function changeImage(ChangeProfileImageRequest $request)
    {
        $profileImage = $request->profile_image;
        $user = request()->user();

        if ($user->hasMedia('profile_images')) {
            $user->clearMediaCollection('profile_images');
        }

        $user->addMedia($profileImage)->toMediaCollection('profile_images');

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        $user = request()->user();
        $user->delete();

        return response()->noContent();
    }
}
