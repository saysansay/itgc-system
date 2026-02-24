<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\GeneralTroublesExport;
use App\Models\GeneralTrouble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class GeneralTroubleController extends Controller
{
    public function index(Request $request)
    {
        $query = GeneralTrouble::with(['user', 'pic']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('trouble_number', 'like', "%{$search}%")
                    ->orWhere('problem', 'like', "%{$search}%")
                    ->orWhere('analysis', 'like', "%{$search}%")
                    ->orWhere('solution', 'like', "%{$search}%")
                    ->orWhere('partner', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('pic', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('pic_id')) {
            $query->where('pic_id', $request->pic_id);
        }

        $items = $query->orderBy('reported_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($items);
    }

    public function export(Request $request)
    {
        $query = GeneralTrouble::with(['user', 'pic']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('trouble_number', 'like', "%{$search}%")
                    ->orWhere('problem', 'like', "%{$search}%")
                    ->orWhere('analysis', 'like', "%{$search}%")
                    ->orWhere('solution', 'like', "%{$search}%")
                    ->orWhere('partner', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('pic', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('pic_id')) {
            $query->where('pic_id', $request->pic_id);
        }

        $items = $query->orderBy('reported_at', 'desc')->get();

        return Excel::download(new GeneralTroublesExport($items), 'general-troubles.xlsx');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'reported_at' => 'required|date',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:minute,hour',
            'problem' => 'required|string',
            'analysis' => 'required|string',
            'solution' => 'required|string',
            'type' => 'required|in:hardware,software,network,security,others',
            'pic_id' => 'required|exists:users,id',
            'partner' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:open,on_progress,done,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $troubleNumber = GeneralTrouble::generateTroubleNumber();

        $generalTrouble = GeneralTrouble::create([
            'trouble_number' => $troubleNumber,
            'user_id' => $request->user_id,
            'reported_at' => $request->reported_at,
            'duration_value' => $request->duration_value,
            'duration_unit' => $request->duration_unit,
            'problem' => $request->problem,
            'analysis' => $request->analysis,
            'solution' => $request->solution,
            'type' => $request->type,
            'pic_id' => $request->pic_id,
            'partner' => $request->partner,
            'notes' => $request->notes,
            'status' => $request->status ?? 'open',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'General trouble created successfully',
            'data' => $generalTrouble->load(['user', 'pic'])
        ], 201);
    }

    public function show($id)
    {
        $item = GeneralTrouble::with(['user', 'pic'])->findOrFail($id);
        return response()->json(['data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = GeneralTrouble::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'reported_at' => 'required|date',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:minute,hour',
            'problem' => 'required|string',
            'analysis' => 'required|string',
            'solution' => 'required|string',
            'type' => 'required|in:hardware,software,network,security,others',
            'pic_id' => 'required|exists:users,id',
            'partner' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:open,on_progress,done,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $item->update([
            'user_id' => $request->user_id,
            'reported_at' => $request->reported_at,
            'duration_value' => $request->duration_value,
            'duration_unit' => $request->duration_unit,
            'problem' => $request->problem,
            'analysis' => $request->analysis,
            'solution' => $request->solution,
            'type' => $request->type,
            'pic_id' => $request->pic_id,
            'partner' => $request->partner,
            'notes' => $request->notes,
            'status' => $request->status,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'General trouble updated successfully',
            'data' => $item->load(['user', 'pic'])
        ]);
    }

    public function destroy($id)
    {
        $item = GeneralTrouble::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'General trouble deleted successfully'
        ]);
    }
}
