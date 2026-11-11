<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Columns that may be filled from a form in one go.
     * link_code is NOT here: it is generated automatically, never typed in.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'date_of_birth',
    ];

    /**
     * Never include these when a user is converted to an array/JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',         // passwords are hashed automatically
            'role' => UserRole::class,      // "parent" becomes UserRole::Parent
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Automatic actions when users are created.
     */
    protected static function booted(): void
    {
        // Before a parent is saved: give them a unique link code
        static::creating(function (User $user) {
            if ($user->role === UserRole::Parent && empty($user->link_code)) {
                $user->link_code = static::generateUniqueLinkCode();
            }
        });

        // After a child is saved: give them an empty wallet
        static::created(function (User $user) {
            if ($user->role === UserRole::Child) {
                $user->wallet()->create();
            }
        });
    }

    public static function generateUniqueLinkCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (static::where('link_code', $code)->exists());

        return $code;
    }

    /* ---------------- Relationships ---------------- */

    /** For a parent: the children linked to them. */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_child_links', 'parent_id', 'child_id')
                    ->withTimestamps();
    }

    /** For a child: the parents linked to them. */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_child_links', 'child_id', 'parent_id')
                    ->withTimestamps();
    }

    /** For a child: their wallet. */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /* ---------------- Helpers ---------------- */

    public function isParent(): bool
    {
        return $this->role === UserRole::Parent;
    }

    public function isChild(): bool
    {
        return $this->role === UserRole::Child;
    }

    /**
     * Security check used everywhere a parent acts on a child:
     * a parent may only manage children linked to THEM.
     */
    public function isParentOf(User $child): bool
    {
        return $this->children()->whereKey($child->getKey())->exists();
    }
}