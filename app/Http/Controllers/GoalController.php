<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGoalRequest;
use App\Services\GoalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function __construct(private GoalService $goals) {}

    public function update(UpdateGoalRequest $request, int $year): RedirectResponse
    {
        $this->goals->set($request->user(), $request->integer('year'), $request->integer('target'));

        return redirect()
            ->route('dashboard')
            ->with('success', 'Meta atualizada.');
    }

    public function destroy(Request $request, int $year): RedirectResponse
    {
        $this->goals->remove($request->user(), $year);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Meta removida.');
    }
}
