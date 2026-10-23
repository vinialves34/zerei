<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Services\GameService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('games:fetch-covers {--delay=250 : Milliseconds to wait between RAWG requests}')]
#[Description('Link games without a cover to RAWG when the title matches exactly')]
class FetchGameCovers extends Command
{
    public function handle(GameService $games): int
    {
        if (blank(config('services.rawg.key'))) {
            $this->components->error('Defina RAWG_API_KEY no .env para buscar as capas.');

            return self::FAILURE;
        }

        $linked = 0;
        $pending = [];

        foreach ($games->withoutCover() as $game) {
            if ($games->attachCoverByTitle($game)) {
                $linked++;
            } else {
                $pending[] = $game;
            }

            usleep((int) $this->option('delay') * 1000);
        }

        $this->components->info("{$linked} jogo(s) vinculado(s) à RAWG.");

        if ($pending !== []) {
            $this->components->warn(count($pending).' jogo(s) sem correspondência exata, escolha a capa manualmente:');
            $this->components->bulletList(array_map(fn (Game $game) => $game->title, $pending));
        }

        return self::SUCCESS;
    }
}
