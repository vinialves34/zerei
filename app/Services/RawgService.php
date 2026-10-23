<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class RawgService
{
    /**
     * Search games on RAWG. Returns an empty list when the API key is missing or RAWG fails,
     * so callers never break because of the integration.
     *
     * @return list<array{id: int, name: string, released: ?string, cover_url: ?string}>
     */
    public function search(string $query, int $limit = 6): array
    {
        $term = Str::lower(trim($query));

        if ($term === '' || blank(config('services.rawg.key'))) {
            return [];
        }

        $cacheKey = 'rawg:search:'.$limit.':'.md5($term);

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $results = $this->fetch($term, $limit);

        if ($results === null) {
            return [];
        }

        Cache::put($cacheKey, $results, now()->addDay());

        return $results;
    }

    /**
     * @return list<array{id: int, name: string, released: ?string, cover_url: ?string}>|null
     */
    private function fetch(string $term, int $limit): ?array
    {
        try {
            $response = Http::baseUrl(config('services.rawg.base_url'))
                ->acceptJson()
                ->timeout(5)
                ->retry(2, 200, throw: false)
                ->get('games', [
                    'key' => config('services.rawg.key'),
                    'search' => $term,
                    'page_size' => $limit,
                ]);
        } catch (Throwable $exception) {
            Log::warning('RAWG request failed.', ['message' => $exception->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('RAWG responded with an error.', ['status' => $response->status()]);

            return null;
        }

        return collect($response->json('results', []))
            ->filter(fn (mixed $game) => is_array($game) && isset($game['id'], $game['name']))
            ->map(fn (array $game) => [
                'id' => (int) $game['id'],
                'name' => (string) $game['name'],
                'released' => $game['released'] ?? null,
                'cover_url' => $game['background_image'] ?? null,
            ])
            ->values()
            ->all();
    }
}
