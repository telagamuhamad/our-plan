<?php

namespace App\Http\Controllers;

use App\Http\Requests\TravelRequest;
use App\Services\MeetingService;
use App\Services\TravelService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TravelController extends Controller
{
    protected $service;
    protected $meetingService;

    public function __construct(TravelService $service, MeetingService $meetingService)
    {
        $this->service = $service;
        $this->meetingService = $meetingService;
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
                'completed' => false,
            ];

            $this->service->createTravel($payload);

            DB::commit();

            return redirect()->route('travels.index')->with('success', 'Travel created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('travels.index')->with('error', 'Failed to create travel.');
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

    public function assignToMeeting(Request $request, $meetingId)
    {
        if (empty($request->travel_id)) {
            return redirect()->back()->with('error', 'Travel Planner ID is required.');
        }

        try {
            $visitDate = $request->visit_date;
            $travel = $this->service->findTravelById($request->travel_id);
            $meeting = $this->meetingService->findMeetingById($meetingId);

            DB::beginTransaction();

            $this->service->assignToMeeting($meeting, $travel, $visitDate);

            DB::commit();

            return redirect()->back()->with('success', 'Travel Planner berhasil di-assign ke Meeting.');
            
        } catch (Exception $e) {
            DB::rollBack();
            // return redirect()->back()->with('error', 'Failed to assign Travel Planner to Meeting.');
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function removeFromMeeting($travelId)
    {
        try {
            DB::beginTransaction();

            $this->service->removeFromMeeting($travelId);

            DB::commit();

            return redirect()->back()->with('success', 'Travel Planner berhasil dihapus dari Meeting.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to remove Travel Planner from Meeting.');
        }
    }

    public function completeTravel($travelId)
    {
        try {
            DB::beginTransaction();

            $this->service->completeTravel($travelId);

            DB::commit();

            return redirect()->back()->with('success', 'Travel Planner berhasil diselesaikan.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to complete Travel Planner.');
        }
    }
}
