<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavingTransactionRequest;
use App\Services\SavingTransactionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavingTransactionController extends Controller
{
    protected $service;

    public function __construct(SavingTransactionService $service)
    {
        $this->service = $service;
    }

    public function store(SavingTransactionRequest $request, $savingId)
    {
        $payload = [
            'type' => $request->type,
            'amount' => $request->amount,
            'note' => $request->note,
        ];

        try {
            DB::beginTransaction();

            $this->service->addTransaction($savingId, $payload);

            DB::commit();

            return redirect()->back()->with('success', 'Saving transaction created successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Something went wrong.');
        }
    }
}
