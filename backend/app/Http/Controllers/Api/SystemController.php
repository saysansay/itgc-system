<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\System;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SystemController extends Controller
{
    public function index(Request $request)
    {
        $query = System::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $systems = $query->paginate($request->per_page ?? 15);

        return response()->json($systems);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:systems,code|max:50',
            'description' => 'nullable|string',
            'category' => 'required|in:erp,crm,hrms,financial,inventory,other',
            'version' => 'nullable|string|max:50',
            'vendor' => 'nullable|string|max:255',
            'owner' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $system = System::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'category' => $request->category,
            'version' => $request->version,
            'vendor' => $request->vendor,
            'owner' => $request->owner,
            'url' => $request->url,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'System created successfully',
            'data' => $system
        ], 201);
    }

    public function show($id)
    {
        $system = System::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $system
        ]);
    }

    public function update(Request $request, $id)
    {
        $system = System::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('systems')->ignore($system->id)],
            'description' => 'nullable|string',
            'category' => 'required|in:erp,crm,hrms,financial,inventory,other',
            'version' => 'nullable|string|max:50',
            'vendor' => 'nullable|string|max:255',
            'owner' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $system->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'category' => $request->category,
            'version' => $request->version,
            'vendor' => $request->vendor,
            'owner' => $request->owner,
            'url' => $request->url,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'System updated successfully',
            'data' => $system
        ]);
    }

    public function destroy($id)
    {
        $system = System::findOrFail($id);
        $system->delete();

        return response()->json([
            'success' => true,
            'message' => 'System deleted successfully'
        ]);
    }
}
