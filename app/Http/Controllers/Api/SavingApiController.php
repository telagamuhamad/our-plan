<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\SavingService;
use App\Mail\SavingTransferMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\SavingRequest;
use Illuminate\Support\Facades\Mail;

class SavingApiController extends Controller
{
    protected $service;
    protected $userService;

    public function __construct(SavingService $service, UserService $userService)
    {
        $this->service = $service;
        $this->userService = $userService;
    }

    public function index()
    {
        $selectedCategory = request('category');
        $savings = $this->service->getAllSavings();

        // Hitung total simpanan per kategori
        $categoryData = $savings->groupBy('name')->map(function ($group) {
            return $group->sum('current_amount');
        });

        // Filter berdasarkan kategori jika ada
        if ($selectedCategory) {
            $savings = $savings->where('name', $selectedCategory);
        }

        // Hitung total simpanan per kategori
        $categoryData = $savings->groupBy('name')->map(function ($group) {
            return $group->sum('current_amount');
        });

        // Ambil daftar semua kategori yang tersedia
        $categories = $this->service->getAllSavings()->pluck('name')->unique();

        return response()->json([
            'success' => true,
            'savings' => $savings,
            'categoryData' => $categoryData,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory
        ], 200);
    }

    public function show($id)
    {
        $saving = $this->service->findSaving($id);
        if (empty($saving)) {
            return response()->json([
                'success' => false,
                'message' => 'Saving not found.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'saving' => $saving
        ], 200);
    }

    public function store(SavingRequest $request)
    {
        try {
            DB::beginTransaction();

            $payload = [
                'name' => $request->name,
                'target_amount' => $request->target_amount,
                'is_shared' => $request->has('is_shared'),
            ];

            $this->service->createSaving($payload);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Saving created successfully.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create saving.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function update(SavingRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $payload = [
                'name' => $request->name,
                'target_amount' => $request->target_amount,
                'is_shared' => $request->has('is_shared'),
            ];

            $this->service->updateSaving($id, $payload);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Saving updated successfully.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update saving.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        $saving = $this->service->findSaving($id);
        if (empty($saving)) {
            return response()->json([
                'success' => false,
                'message' => 'Saving not found.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $this->service->deleteSaving($id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Saving deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete saving.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'source_saving_id' => ['required', 'exists:savings,id'],
            'target_saving_id' => ['required', 'exists:savings,id', 'different:source_saving_id'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $sourceSaving = $this->service->findSaving($request->source_saving_id);
        $targetSaving = $this->service->findSaving($request->target_saving_id);
        $amount = $request->amount;

        if ($sourceSaving->current_amount < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak cukup untuk melakukan transfer.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $this->service->transfer($sourceSaving, $targetSaving, $amount);

            DB::commit();

            // Send mail
            $users = $this->userService->getAllUser();
            foreach ($users as $user) {
                Mail::to($user->email)->send(new SavingTransferMail($sourceSaving, $targetSaving, $amount, $user->name));
            }

            return response()->json([
                'success' => true,
                'message' => 'Transfer successful.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer.',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
