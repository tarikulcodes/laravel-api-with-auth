<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\EmailVerifyNotification;
use App\Notifications\PasswordResetLinkNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasSlug, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    /**
     * Send a password reset notification to the user.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new PasswordResetLinkNotification($token));
    }

    /**
     * Send email verification notification
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerifyNotification());
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * The roles that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Has role of user
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        if ($role instanceof Role) {
            return $this->roles->contains('id', $role->id);
        }

        return false;
    }

    /**
     * Assign roles to the user by IDs or names (overwrites current roles).
     *
     * @param  array|string|int  $roles
     * @return void
     */
    public function syncRoles($roles): void
    {
        // Convert roles to a collection of IDs
        $roleIds = collect();

        if (is_array($roles)) {
            foreach ($roles as $role) {
                $roleIds = $roleIds->merge($this->resolveRoleIds($role));
            }
        } else {
            $roleIds = $this->resolveRoleIds($roles);
        }

        // Sync the roles (replaces current roles)
        $this->roles()->sync($roleIds->unique());
    }

    protected function resolveRoleIds($role)
    {
        if (is_numeric($role)) {
            return Role::where('id', $role)->pluck('id');
        }

        if (is_string($role)) {
            return Role::where('name', $role)->pluck('id');
        }

        return collect();
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $searchTerm = null)
    {
        if (!$searchTerm) return $query;

        $searchTerm = trim($searchTerm);
        return $query->where("name", "like", "%$searchTerm%");
    }

    /**
     * Scope to filter users by role.
     */
    public function scopeWithRole($query, $roleName = null)
    {
        if ($roleName) {
            return $query->whereHas('roles', function ($query) use ($roleName) {
                $query->where('name', $roleName);
            });
        }

        return $query;
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('blurred')
            ->blur(50)
            ->performOnCollections('profile_images')
            ->nonQueued();
    }
}
