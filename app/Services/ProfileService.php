<?php

namespace App\Services;

use App\Models\User;

class ProfileService
{
    /**
     * Update the profile, requiring the new email to be verified again when it changes.
     *
     * @param  array{name: string, email: string}  $attributes
     */
    public function update(User $user, array $attributes): User
    {
        $user->fill($attributes);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }

    /**
     * Delete the account together with all of its data.
     */
    public function delete(User $user): void
    {
        $user->delete();
    }
}
