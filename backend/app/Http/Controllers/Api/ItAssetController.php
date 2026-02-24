<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\ItAssetsExport;
use App\Models\ItAsset;
use App\Models\AssetAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ItAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = ItAsset::with(['assignedUser', 'department', 'currentAssignment']);

        // Search by asset tag, name, serial number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by assignment status
        if ($request->has('assignment_status')) {
            if ($request->assignment_status === 'assigned') {
                $query->whereNotNull('assigned_to');
            } elseif ($request->assignment_status === 'unassigned') {
                $query->whereNull('assigned_to');
            }
        }

        $assets = $query->orderBy('asset_tag', 'asc')
            ->paginate($request->get('per_page', 10));

        return response()->json($assets);
    }

    public function export(Request $request)
    {
        $query = ItAsset::with(['assignedUser', 'department', 'currentAssignment']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('assignment_status')) {
            if ($request->assignment_status === 'assigned') {
                $query->whereNotNull('assigned_to');
            } elseif ($request->assignment_status === 'unassigned') {
                $query->whereNull('assigned_to');
            }
        }

        $items = $query->orderBy('asset_tag', 'asc')->get();

        return Excel::download(new ItAssetsExport($items), 'it-assets.xlsx');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset_tag' => 'required|string|max:50|unique:it_assets,asset_tag',
            'name' => 'required|string|max:200',
            'category' => 'required|in:Server,Laptop,Desktop,Network,Printer,Phone,Tablet,Storage,Other',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'specifications' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,repair,retired,disposed',
            'department_id' => 'nullable|exists:departments,id',
            'location' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $asset = ItAsset::create([
            'asset_tag' => $request->asset_tag,
            'name' => $request->name,
            'category' => $request->category,
            'brand' => $request->brand,
            'model' => $request->model,
            'serial_number' => $request->serial_number,
            'specifications' => $request->specifications,
            'purchase_date' => $request->purchase_date,
            'purchase_price' => $request->purchase_price,
            'warranty_expiry' => $request->warranty_expiry,
            'status' => $request->status ?? 'active',
            'department_id' => $request->department_id,
            'location' => $request->location,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'IT Asset created successfully',
            'data' => $asset->load(['assignedUser', 'department'])
        ], 201);
    }

    public function show($id)
    {
        $asset = ItAsset::with(['assignedUser', 'department', 'assignments.user', 'assignments.assignedByUser', 'assignments.receivedByUser'])
            ->findOrFail($id);
        return response()->json(['data' => $asset]);
    }

    public function update(Request $request, $id)
    {
        $asset = ItAsset::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'asset_tag' => 'required|string|max:50|unique:it_assets,asset_tag,' . $id,
            'name' => 'required|string|max:200',
            'category' => 'required|in:Server,Laptop,Desktop,Network,Printer,Phone,Tablet,Storage,Other',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'specifications' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,repair,retired,disposed',
            'department_id' => 'nullable|exists:departments,id',
            'location' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $asset->update([
            'asset_tag' => $request->asset_tag,
            'name' => $request->name,
            'category' => $request->category,
            'brand' => $request->brand,
            'model' => $request->model,
            'serial_number' => $request->serial_number,
            'specifications' => $request->specifications,
            'purchase_date' => $request->purchase_date,
            'purchase_price' => $request->purchase_price,
            'warranty_expiry' => $request->warranty_expiry,
            'status' => $request->status ?? $asset->status,
            'department_id' => $request->department_id,
            'location' => $request->location,
            'notes' => $request->notes,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'IT Asset updated successfully',
            'data' => $asset->load(['assignedUser', 'department'])
        ]);
    }

    public function destroy($id)
    {
        $asset = ItAsset::findOrFail($id);
        
        // Check if asset is currently assigned
        if ($asset->assigned_to) {
            return response()->json([
                'message' => 'Cannot delete asset that is currently assigned. Please return it first.'
            ], 422);
        }

        $asset->delete();

        return response()->json([
            'message' => 'IT Asset deleted successfully'
        ]);
    }

    public function assign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date',
            'assignment_notes' => 'nullable|string',
            'condition_on_assign' => 'required|in:excellent,good,fair,poor',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $asset = ItAsset::findOrFail($id);

        // Check if asset is already assigned
        if ($asset->assigned_to) {
            return response()->json([
                'message' => 'Asset is already assigned. Please return it first.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create assignment record
            AssetAssignment::create([
                'it_asset_id' => $asset->id,
                'user_id' => $request->user_id,
                'assigned_date' => $request->assigned_date,
                'assignment_notes' => $request->assignment_notes,
                'condition_on_assign' => $request->condition_on_assign,
                'assigned_by' => auth()->id(),
            ]);

            // Update asset
            $asset->update([
                'assigned_to' => $request->user_id,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Asset assigned successfully',
                'data' => $asset->load(['assignedUser', 'department'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to assign asset',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function return(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'returned_date' => 'required|date',
            'return_notes' => 'nullable|string',
            'condition_on_return' => 'required|in:excellent,good,fair,poor',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $asset = ItAsset::findOrFail($id);

        // Check if asset is assigned
        if (!$asset->assigned_to) {
            return response()->json([
                'message' => 'Asset is not currently assigned.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Find the current active assignment
            $assignment = AssetAssignment::where('it_asset_id', $asset->id)
                ->whereNull('returned_date')
                ->latest()
                ->first();

            if ($assignment) {
                // Update assignment record
                $assignment->update([
                    'returned_date' => $request->returned_date,
                    'return_notes' => $request->return_notes,
                    'condition_on_return' => $request->condition_on_return,
                    'received_by' => auth()->id(),
                ]);
            }

            // Update asset
            $asset->update([
                'assigned_to' => null,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Asset returned successfully',
                'data' => $asset->load(['assignedUser', 'department'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to return asset',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
