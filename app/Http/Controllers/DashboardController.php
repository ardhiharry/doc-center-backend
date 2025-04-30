<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function getTotalActivity(): JsonResponse
    {
        try {
            $activity = Activity::count();

            return Response::handler(
                200,
                'Berhasil mengambil total aktivitas',
                [['total_activity' => $activity]]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil total aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getActivityThisMonth(): JsonResponse
    {
        try {
            $now = Carbon::now();

            $activities = Activity::with('project')
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

            return Response::handler(
                200,
                'Berhasil mengambil aktivitas bulan ini',
                $activities
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil aktivitas bulan ini',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
