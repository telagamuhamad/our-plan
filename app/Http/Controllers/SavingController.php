<?php

namespace App\Http\Controllers;

use App\Services\SavingService;
use Illuminate\Http\Request;

class SavingController extends Controller
{
    protected $service;

    public function __construct(SavingService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $savings = $this->service->getAllSavings();

        return view('savings.index', [
            'savings' => $savings
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

    public function store(Request $request)
    {
        
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

    public function update(Request $request)
    {

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
}
