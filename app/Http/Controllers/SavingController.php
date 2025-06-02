<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavingRequest;
use App\Mail\SavingTransferMail;
use App\Services\SavingService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SavingController extends Controller
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

        return view('savings.index', [
            'savings' => $savings,
            'categoryData' => $categoryData,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory
        ]);
    }

    public function show($id)
    {
        $saving = $this->service->findSaving($id);
        if (empty($saving)) {
            return back()->with('error', 'Saving not found.');
        }

        return view('savings.show', [
            'saving' => $saving
        ]);
    }

    public function create()
    {
        return view('savings.create');
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

            return redirect()->route('savings.index')->with('success', 'Saving created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Something went wrong.');
        }
    }

    public function edit($id)
    {
        $saving = $this->service->findSaving($id);
        if (empty($saving)) {
            return back()->with('error', 'Saving not found.');
        }

        return view('savings.edit', [
            'saving' => $saving
        ]);
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

            return redirect()->route('savings.index')->with('success', 'Saving updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Something went wrong.');
        }
    }

    public function destroy($id)
    {
        $saving = $this->service->findSaving($id);
        if (empty($saving)) {
            return back()->with('error', 'Saving not found.');
        }

        $this->service->deleteSaving($id);

        return back()->with('success', 'Saving deleted successfully.');
    }

    public function showTransferForm()
    {
        $savings = $this->service->getAllSavings();

        return view('savings.transfer', [
            'savings' => $savings
        ]);
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
            return back()->with('error', 'Saldo tidak cukup untuk melakukan transfer.');
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

            return redirect()->route('savings.index')->with('success', 'Transfer successful.');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            // return back()->with('error', 'Something went wrong.');
            return back()->with('error', $e->getMessage());
        }
    }
}
