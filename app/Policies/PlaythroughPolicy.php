<?php

namespace App\Policies;

use App\Models\Playthrough;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlaythroughPolicy
{
    public function update(User $user, Playthrough $playthrough): Response
    {
        return $this->ownedBy($user, $playthrough);
    }

    public function delete(User $user, Playthrough $playthrough): Response
    {
        return $this->ownedBy($user, $playthrough);
    }

    private function ownedBy(User $user, Playthrough $playthrough): Response
    {
        return $playthrough->game->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }
}
