<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function getDashboard(): JsonResponse
    {
        try {
            $now = Carbon::now();

            $totalActivity = Activity::count();

            $totalIsProcessTrue = User::withoutTrashed()
                ->where('is_process', true)
                ->count();

            $totalIsProcessFalse = User::withoutTrashed()
                ->where('is_process', false)
                ->count();

            $totalActivityThisYear = Activity::with('project')
                ->whereYear('start_date', $now->year)
                ->count();

            $activitiesThisMonth = Activity::with('project')
                ->whereYear('start_date', $now->year)
                ->whereMonth('start_date', $now->month)
                ->orderBy('start_date', 'desc')
                ->get()
                ->map(function ($activity) {
                    return [
                        'title' => $activity->title,
                        'start_date' => $activity->start_date,
                        'end_date' => $activity->end_date,
                        'project' => optional($activity->project)->name,
                    ];
                });

            $data = [
                'total_activity' => $totalActivity,
                'total_is_process_true' => $totalIsProcessTrue,
                'total_is_process_false' => $totalIsProcessFalse,
                'total_activity_this_year' => $totalActivityThisYear,
                'activities_this_month' => $activitiesThisMonth
            ];

            return Response::handler(
                200,
                'Berhasil mengambil data dashboard',
                [$data]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data dashboard',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
