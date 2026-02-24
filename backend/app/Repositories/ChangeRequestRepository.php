<?php

namespace App\Repositories;

use App\Models\ChangeRequest;

class ChangeRequestRepository extends BaseRepository
{
    public function __construct(ChangeRequest $model)
    {
        parent::__construct($model);
    }

    public function getDataTable($request)
    {
        $query = $this->model->with(['system', 'requester', 'implementer', 'approver']);

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by risk level
        if ($request->has('risk_level') && $request->risk_level) {
            $query->where('risk_level', $request->risk_level);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    public function getPendingApprovals()
    {
        return $this->model->where('status', 'pending_approval')
            ->with(['system', 'requester'])
            ->get();
    }

    public function getHighRiskChanges()
    {
        return $this->model->where('risk_level', 'high')
            ->whereIn('status', ['pending_approval', 'approved', 'in_progress'])
            ->with(['system', 'requester'])
            ->get();
    }

    public function getChangesPerMonth($year = null)
    {
        $year = $year ?? date('Y');
        return $this->model->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
