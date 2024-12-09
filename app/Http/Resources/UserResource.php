<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'slug'                      => $this->slug,
            'name'                      => $this->name,
            'email'                     => $this->email,
            'email_verified_at'         => $this->email_verified_at,
            'profile_image'             => $this->getFirstMediaUrl('profile_images'),
            'profile_image_placeholder' => $this->getFirstMediaUrl('profile_images', 'blurred'),
            'roles'                     => $this->roles()->pluck('name'),
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at,
        ];
    }
}
