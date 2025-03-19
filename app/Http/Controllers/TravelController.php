<?php

namespace App\Http\Controllers;

use App\Http\Requests\TravelRequest;
use App\Services\TravelService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TravelController extends Controller
{
    protected $service;

    public function __construct(TravelService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request) 
    {
        $user = Auth::user();
        $searchTerms = [
            'destination' => $request->destination,
            'visit_date' => $request->visit_date,
            'completed' => $request->completed,
        ];

        $travels = $this->service->getAllTravels($searchTerms);

        return view('travels.index', [
            'travels' => $travels,
            'user' => $user
        ]);
    }

    public function show($travelId)
    {
        $travel = $this->service->findTravelById($travelId);
        if (empty($travel)) {
            return redirect()->route('travels.index')->with('error', 'Travel not found.');
        }

        return view('travels.show', [
            'travel' => $travel
        ]);
    }

    public function create()
    {
        return view('travels.create');
    }

    public function store(TravelRequest $request)
    {
        try {
            DB::beginTransaction();

            $payload = [
                'meeting_id' => $request->meeting_id,
                'destination' => $request->destination,
                'visit_date' => $request->visit_date,
                'completed' => false,
            ];

            $this->service->createTravel($payload);

            DB::commit();

            return redirect()->route('travels.index')->with('success', 'Travel created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            // return redirect()->route('travels.index')->with('error', 'Failed to create travel.');
            return redirect()->route('travels.index')->with('error', $e->getMessage());
        }
    }

    public function edit($travelId)
    {
        $travel = $this->service->findTravelById($travelId);
        if (empty($travel)) {
            return redirect()->route('travels.index')->with('error', 'Travel not found.');
        }

        return view('travels.edit', [
            'travel' => $travel
        ]);
    }

    public function update(TravelRequest $request, $travelId)
    {
        try {
            DB::beginTransaction();

            $payload = [
                'meeting_id' => $request->meeting_id,
                'destination' => $request->destination,
                'visit_date' => $request->visit_date,
                'completed' => $request->completed,
            ];

            $this->service->update($travelId, $payload);

            DB::commit();

            return redirect()->route('travels.index')->with('success', 'Travel updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('travels.index')->with('error', 'Failed to update travel.');
        }
    }

    public function destroy($travelId)
    {
        try {
            DB::beginTransaction();

            $this->service->delete($travelId);

            DB::commit();

            return redirect()->route('travels.index')->with('success', 'Travel deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('travels.index')->with('error', 'Failed to delete travel.');
        }
    }
}
