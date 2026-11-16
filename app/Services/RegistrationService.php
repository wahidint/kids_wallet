<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    /**
     * Create a parent or child account.
     * A child is linked to the parent who owns the link code.
     * Everything happens in one database transaction: all or nothing.
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $isChild = $data['role'] === UserRole::Child->value;

            // The User model automatically adds a link code (parent) or a wallet (child)
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'],
                'phone' => $data['phone'] ?? null,
                'date_of_birth' => $isChild ? $data['date_of_birth'] : null,
            ]);

            if ($isChild) {
                $parent = User::where('role', UserRole::Parent)
                    ->where('link_code', $data['link_code'])
                    ->firstOrFail();

                $parent->children()->attach($user);
            }

            return $user;
        });
    }
}