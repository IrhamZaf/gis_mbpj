<?php

namespace App\Http\Controllers\gis;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\SurveyData;
use App\Models\SurveyUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = $this->buildStats();
        $recentIncidents = Incident::query()
            ->with('reporter')
            ->orderByDesc('date_reported')
            ->limit(8)
            ->get();

        $notifications = $request->user()->notifications()->limit(10)->get();

        $surveyForEngineer = ['files' => 0, 'pending_review' => 0];
        if ($request->user()->isEngineer() || $request->user()->isAdmin()) {
            $surveyForEngineer['files'] = SurveyUpload::query()->count();
            $surveyForEngineer['pending_review'] = SurveyData::query()
                ->where('review_status', SurveyData::REVIEW_PENDING)
                ->count();
        }

        return view('dashboard', compact('stats', 'recentIncidents', 'notifications', 'surveyForEngineer'));
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->buildStats());
    }

    protected function buildStats(): array
    {
        $sinkholes = Incident::query()->where('category', Incident::CATEGORY_SINKHOLE)->count();
        $slopes = Incident::query()->where('category', Incident::CATEGORY_SLOPE)->count();
        $critical = Incident::query()->where('risk_level', Incident::RISK_CRITICAL)->count();
        $pending = Incident::query()->whereIn('status', ['baru_dilaporkan', 'dalam_siasatan'])->count();

        $monthly = Incident::query()
            ->where('date_reported', '>=', Carbon::now()->subMonths(12))
            ->get()
            ->groupBy(fn (Incident $i) => $i->date_reported->format('Y-m'))
            ->map(fn ($group) => $group->count())
            ->sortKeys()
            ->map(fn (int $count, string $ym) => ['ym' => $ym, 'c' => $count])
            ->values()
            ->all();

        return [
            'sinkholes' => $sinkholes,
            'active_slopes' => $slopes,
            'critical_locations' => $critical,
            'pending_reports' => $pending,
            'monthly' => $monthly,
        ];
    }
}
