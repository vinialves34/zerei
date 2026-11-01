<?php

namespace App\Http\Middleware;

use App\Services\LevelService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class AnnounceLevelUp
{
    public function __construct(private LevelService $levels) {}

    /**
     * Share the level of the signed-in user with the views and, on page loads, tell the user
     * once when they went up a level.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $request->isMethod('GET') || $request->expectsJson()) {
            return $next($request);
        }

        $level = $this->levels->progress($user);

        View::share('level', $level);

        if ($newLevel = $this->levels->claimLevelUp($user, $level['level'])) {
            $request->session()->now('level_up', ['level' => $newLevel, 'title' => $level['title']->label()]);
        }

        return $next($request);
    }
}
