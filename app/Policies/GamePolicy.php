<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GamePolicy
{
    public function view(User $user, Game $game): Response
    {
        return $this->ownedBy($user, $game);
    }

    public function update(User $user, Game $game): Response
    {
        return $this->ownedBy($user, $game);
    }

    public function delete(User $user, Game $game): Response
    {
        return $this->ownedBy($user, $game);
    }

    private function ownedBy(User $user, Game $game): Response
    {
        return $game->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }
}
