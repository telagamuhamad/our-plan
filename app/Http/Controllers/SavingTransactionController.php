<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavingTransactionRequest;
use App\Mail\SavingTransactionMail;
use App\Services\SavingService;
use App\Services\SavingTransactionService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SavingTransactionController extends Controller
{
    protected $service;
    protected $savingService;
    protected $userService;

    public function __construct(SavingTransactionService $service, SavingService $savingService, UserService $userService)
    {
        $this->service = $service;
        $this->userService = $userService;
        $this->savingService = $savingService;
    }

    public function store(SavingTransactionRequest $request, $savingId)
    {
        $user = Auth::user();
        $payload = [
            'type' => $request->type,
            'amount' => $request->amount,
            'note' => $request->note,
            'actor_user_id' => $user->id
        ];

        $saving = $this->savingService->findSaving($savingId);
        if (empty($saving)) {
            return back()->with('error', 'Saving not found.');
        }

        try {
            DB::beginTransaction();

            $this->service->addTransaction($saving, $payload);

            DB::commit();

            // Send mail
            $users = $this->userService->getAllUser();
            foreach ($users as $user) {
                Mail::to($user->email)->send(new SavingTransactionMail($saving, $payload['type'], $payload['amount'], $payload['note'], $user->name));
            }

            return redirect()->route('savings.index')->with('success', 'Saving transaction created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            // report($e);
            return back()->with('error', 'Something went wrong.');
        }
    }
}
