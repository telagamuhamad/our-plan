<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\SavingService;
use Illuminate\Support\Facades\DB;
use App\Mail\SavingTransactionMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Services\SavingTransactionService;
use App\Http\Requests\SavingTransactionRequest;
use Exception;

class SavingTransactionApiController extends Controller
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
        $payload = [
            'type' => $request->type,
            'amount' => $request->amount,
            'note' => $request->note,
        ];

        $saving = $this->savingService->findSaving($savingId);
        if (empty($saving)) {
            return response()->json([
                'success' => false,
                'message' => 'Saving not found.'
            ], 400);
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

            return response()->json([
                'success' => true,
                'message' => 'Saving transaction created successfully.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create saving transaction.',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
