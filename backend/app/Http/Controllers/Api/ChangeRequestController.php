<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\ChangeRequestsExport;
use App\Http\Requests\ChangeRequestRequest;
use App\Http\Resources\ChangeRequestResource;
use App\Models\ChangeRequest;
use App\Services\ChangeRequestService;
use App\Repositories\ChangeRequestRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

class ChangeRequestController extends Controller
{
    protected $service;
    protected $repository;

    public function __construct(
        ChangeRequestService $service,
        ChangeRequestRepository $repository
    ) {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->repository->getDataTable($request);
            $changeRequests = $query->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => ChangeRequestResource::collection($changeRequests),
                'meta' => [
                    'current_page' => $changeRequests->currentPage(),
                    'last_page' => $changeRequests->lastPage(),
                    'per_page' => $changeRequests->perPage(),
                    'total' => $changeRequests->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve change requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $query = ChangeRequest::with(['system', 'requester', 'implementer', 'approver']);

        if ($request->filled('search')) {
            $search = $request->search;
            if (is_array($search) && isset($search['value'])) {
                $search = $search['value'];
            }
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(new ChangeRequestsExport($items), 'change-requests.xlsx');
    }

    public function store(ChangeRequestRequest $request): JsonResponse
    {
        try {
            $changeRequest = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Change request created successfully',
                'data' => new ChangeRequestResource($changeRequest)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create change request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $changeRequest = $this->repository->with(['system', 'requester', 'implementer', 'approver', 'evidences'])->find($id);

            if (!$changeRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Change request not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new ChangeRequestResource($changeRequest)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve change request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(ChangeRequestRequest $request, $id): JsonResponse
    {
        try {
            $changeRequest = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Change request updated successfully',
                'data' => new ChangeRequestResource($changeRequest)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update change request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Change request deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete change request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitForApproval($id): JsonResponse
    {
        try {
            $changeRequest = $this->service->submitForApproval($id);

            return response()->json([
                'success' => true,
                'message' => 'Change request submitted for approval',
                'data' => new ChangeRequestResource($changeRequest)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit change request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadEvidence(Request $request, $id): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
            'description' => 'nullable|string',
        ]);

        try {
            $evidence = $this->service->uploadEvidence(
                $id,
                $request->file('file'),
                $request->description
            );

            return response()->json([
                'success' => true,
                'message' => 'Evidence uploaded successfully',
                'data' => $evidence
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload evidence',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
