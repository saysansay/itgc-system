<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\AccessRequest;
use App\Models\Approval;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $user = auth()->user();

            // Total open requests
            $openRequests = ChangeRequest::whereIn('status', ['draft', 'pending_approval', 'approved', 'in_progress'])
                ->count();

            // Pending approvals
            $pendingApprovals = Approval::where('status', 'pending')
                ->where('approver_id', $user->id)
                ->count();

            // High risk changes
            $highRiskChanges = ChangeRequest::where('risk_level', 'high')
                ->whereIn('status', ['pending_approval', 'approved', 'in_progress'])
                ->count();

            // Recent activities
            $recentActivities = AuditLog::with('user')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'user' => $log->user ? $log->user->name : 'System',
                        'action' => $log->action,
                        'module' => $log->module,
                        'description' => $log->description,
                        'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            // Change requests per month (current year)
            $changesPerMonth = ChangeRequest::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get()
                ->pluck('count', 'month')
                ->toArray();

            // Fill missing months with 0
            $monthlyData = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthlyData[] = $changesPerMonth[$i] ?? 0;
            }

            // Status distribution
            $statusDistribution = ChangeRequest::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status');

            // Risk level distribution
            $riskDistribution = ChangeRequest::selectRaw('risk_level, COUNT(*) as count')
                ->groupBy('risk_level')
                ->get()
                ->pluck('count', 'risk_level');

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => [
                        'open_requests' => $openRequests,
                        'pending_approvals' => $pendingApprovals,
                        'high_risk_changes' => $highRiskChanges,
                        'total_changes' => ChangeRequest::count(),
                        'total_access_requests' => AccessRequest::count(),
                    ],
                    'recent_activities' => $recentActivities,
                    'charts' => [
                        'changes_per_month' => $monthlyData,
                        'status_distribution' => $statusDistribution,
                        'risk_distribution' => $riskDistribution,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function myApprovals(): JsonResponse
    {
        try {
            $approvals = Approval::with(['approvable', 'approver'])
                ->where('approver_id', auth()->id())
                ->where('status', 'pending')
                ->latest()
                ->get()
                ->map(function ($approval) {
                    return [
                        'id' => $approval->id,
                        'type' => class_basename($approval->approvable_type),
                        'title' => $approval->approvable->title ?? $approval->approvable->request_number,
                        'level' => $approval->level,
                        'status' => $approval->status,
                        'created_at' => $approval->created_at->format('Y-m-d H:i:s'),
                        'approval_link' => route('approval.show', $approval->approval_token),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $approvals
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load approvals',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
