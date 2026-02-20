<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavingRequest;
use App\Mail\SavingTransferMail;
use App\Models\Saving;
use App\Services\SavingService;
use App\Services\SavingCategoryService;
use App\Services\SavingTransactionService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SavingController extends Controller
{
    protected $service;
    protected $userService;
    protected $savingTransactionService;
    protected $categoryService;

    public function __construct(
        SavingService $service,
        UserService $userService,
        SavingTransactionService $savingTransactionService,
        SavingCategoryService $categoryService
    ) {
        $this->service = $service;
        $this->userService = $userService;
        $this->savingTransactionService = $savingTransactionService;
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $selectedCategory = request('category');
        $savings = $this->service->getAllSavings();

        // Filter berdasarkan kategori jika ada
        if ($selectedCategory) {
            $savings = $savings->where('category_id', $selectedCategory);
        }

        // Get all categories for filter
        $allCategories = $this->categoryService->getAllCategories();

        // Hitung total simpanan per kategori
        $categoryData = $savings->filter(function ($saving) {
            return $saving->category_id;
        })->groupBy('category_id')->mapWithKeys(function ($group, $categoryId) use ($allCategories) {
            $category = $allCategories->firstWhere('id', $categoryId);
            $name = $category ? $category->name : 'Uncategorized';
            return [$name => $group->sum('current_amount')];
        });

        // Get upcoming deadlines and overdue savings
        $upcomingDeadlines = $this->service->getUpcomingDeadlines(7);
        $overdueSavings = $this->service->getOverdueSavings();

        return view('savings.index', [
            'savings' => $savings,
            'categoryData' => $categoryData,
            'categories' => $allCategories,
            'selectedCategory' => $selectedCategory,
            'upcomingDeadlines' => $upcomingDeadlines,
            'overdueSavings' => $overdueSavings,
        ]);
    }

    public function show($id)
    {
        $saving = $this->service->findSaving($id);
        if (empty($saving)) {
            return back()->with('error', 'Saving not found.');
        }

        $savingTransactions = $this->savingTransactionService->getTransactionsBySavingId($saving->id);
        return view('savings.show', [
            'saving' => $saving,
            'savingTransactions' => $savingTransactions
        ]);
    }

    public function create()
    {
        $categories = $this->categoryService->getAllCategories();
        return view('savings.create', compact('categories'));
    }

    public function store(SavingRequest $request)
    {
        try {
            DB::beginTransaction();

            $payload = [
                'category_id' => $request->category_id ?: null,
                'name' => $request->name,
                'target_amount' => $request->target_amount,
                'target_date' => $request->target_date,
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

        $categories = $this->categoryService->getAllCategories();
        return view('savings.edit', [
            'saving' => $saving,
            'categories' => $categories
        ]);
    }

    public function update(SavingRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $payload = [
                'category_id' => $request->category_id ?: null,
                'name' => $request->name,
                'target_amount' => $request->target_amount,
                'target_date' => $request->target_date,
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
            // foreach ($users as $user) {
            //     Mail::to($user->email)->send(new SavingTransferMail($sourceSaving, $targetSaving, $amount, $user->name));
            // }

            return redirect()->route('savings.index')->with('success', 'Transfer successful.');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            // return back()->with('error', 'Something went wrong.');
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark saving as completed
     */
    public function markCompleted($id)
    {
        $saving = $this->service->findSaving($id);
        if (empty($saving)) {
            return back()->with('error', 'Saving not found.');
        }

        $user = Auth::user();
        $this->service->markAsCompleted($saving, $user);

        return back()->with('success', 'Saving marked as completed.');
    }
}
