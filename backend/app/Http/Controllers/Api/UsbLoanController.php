<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\UsbLoansExport;
use App\Models\UsbLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UsbLoanController extends Controller
{
    public function index(Request $request)
    {
        $query = UsbLoan::with(['requestor', 'department', 'pic', 'approver']);

        // Search by loan number, requestor name, purpose
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('requestor', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by requestor
        if ($request->has('requestor_id')) {
            $query->where('requestor_id', $request->requestor_id);
        }

        // Filter by PIC
        if ($request->has('pic_id')) {
            $query->where('pic_id', $request->pic_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('loan_datetime', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('loan_datetime', '<=', $request->end_date);
        }

        $usbLoans = $query->orderBy('loan_datetime', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json($usbLoans);
    }

    public function export(Request $request)
    {
        $query = UsbLoan::with(['requestor', 'department', 'pic', 'approver']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('requestor', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('requestor_id')) {
            $query->where('requestor_id', $request->requestor_id);
        }

        if ($request->filled('pic_id')) {
            $query->where('pic_id', $request->pic_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('loan_datetime', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('loan_datetime', '<=', $request->end_date);
        }

        $items = $query->orderBy('loan_datetime', 'desc')->get();

        return Excel::download(new UsbLoansExport($items), 'usb-loans.xlsx');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requestor_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'purpose' => 'required|string',
            'loan_datetime' => 'required|date',
            'return_datetime' => 'nullable|date|after:loan_datetime',
            'pic_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $loanNumber = UsbLoan::generateLoanNumber();

            $usbLoan = UsbLoan::create([
                'loan_number' => $loanNumber,
                'requestor_id' => $request->requestor_id,
                'department_id' => $request->department_id,
                'purpose' => $request->purpose,
                'loan_datetime' => $request->loan_datetime,
                'return_datetime' => $request->return_datetime,
                'pic_id' => $request->pic_id,
                'notes' => $request->notes,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'USB loan request created successfully',
                'data' => $usbLoan->load(['requestor', 'department', 'pic'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create USB loan request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $usbLoan = UsbLoan::with(['requestor', 'department', 'pic', 'approver'])
            ->findOrFail($id);
        return response()->json(['data' => $usbLoan]);
    }

    public function update(Request $request, $id)
    {
        $usbLoan = UsbLoan::findOrFail($id);

        // Only allow update if status is pending or rejected
        if (!in_array($usbLoan->status, ['pending', 'rejected'])) {
            return response()->json([
                'message' => 'Cannot update USB loan request with status: ' . $usbLoan->status
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'requestor_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'purpose' => 'required|string',
            'loan_datetime' => 'required|date',
            'return_datetime' => 'nullable|date|after:loan_datetime',
            'pic_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $usbLoan->update([
            'requestor_id' => $request->requestor_id,
            'department_id' => $request->department_id,
            'purpose' => $request->purpose,
            'loan_datetime' => $request->loan_datetime,
            'return_datetime' => $request->return_datetime,
            'pic_id' => $request->pic_id,
            'notes' => $request->notes,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'USB loan request updated successfully',
            'data' => $usbLoan->load(['requestor', 'department', 'pic'])
        ]);
    }

    public function destroy($id)
    {
        $usbLoan = UsbLoan::findOrFail($id);

        // Only allow delete if status is pending or rejected
        if (!in_array($usbLoan->status, ['pending', 'rejected'])) {
            return response()->json([
                'message' => 'Cannot delete USB loan request with status: ' . $usbLoan->status
            ], 422);
        }

        $usbLoan->delete();

        return response()->json([
            'message' => 'USB loan request deleted successfully'
        ]);
    }

    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $usbLoan = UsbLoan::findOrFail($id);

        if ($usbLoan->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending requests can be approved'
            ], 422);
        }

        $usbLoan->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $request->notes ?? $usbLoan->notes,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'USB loan request approved successfully',
            'data' => $usbLoan->load(['requestor', 'department', 'pic', 'approver'])
        ]);
    }

    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $usbLoan = UsbLoan::findOrFail($id);

        if ($usbLoan->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending requests can be rejected'
            ], 422);
        }

        $usbLoan->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'USB loan request rejected successfully',
            'data' => $usbLoan->load(['requestor', 'department', 'pic', 'approver'])
        ]);
    }

    public function returnUsb(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'actual_return_datetime' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $usbLoan = UsbLoan::findOrFail($id);

        if ($usbLoan->status !== 'approved') {
            return response()->json([
                'message' => 'Only approved USB loans can be returned'
            ], 422);
        }

        $usbLoan->update([
            'status' => 'returned',
            'actual_return_datetime' => $request->actual_return_datetime,
            'notes' => $request->notes ?? $usbLoan->notes,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'USB returned successfully',
            'data' => $usbLoan->load(['requestor', 'department', 'pic', 'approver'])
        ]);
    }

    public function getItAdminUsers()
    {
        $users = \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['IT Staff', 'Admin', 'IT Manager'])
              ->orWhere('name', 'like', '%IT%')
              ->orWhere('name', 'like', '%Admin%');
        })->get(['id', 'name', 'email']);

        return response()->json(['data' => $users]);
    }
}
