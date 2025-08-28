<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UsersSummaryQuery
{
    public function __invoke()
    {
        $summary = User::select([
            DB::raw('COUNT(*) as total_users'),
            DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users'),
            DB::raw('SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users'),
            DB::raw('SUM(CASE WHEN is_admin = 1 THEN 1 ELSE 0 END) as admin_users'),
            DB::raw('SUM(CASE WHEN created_at >= "' . Carbon::now()->subDays(30)->toDateString() . '" THEN 1 ELSE 0 END) as recent_registrations')
        ])->first();

        return [
            'totalUsers' => (int) $summary->total_users,
            'activeUsers' => (int) $summary->active_users,
            'inactiveUsers' => (int) $summary->inactive_users,
            'adminUsers' => (int) $summary->admin_users,
            'recentRegistrations' => (int) $summary->recent_registrations,
        ];
    }
}
