<?php

namespace App\Http\Controllers;

use App\Http\Requests\TravelRequest;
use App\Mail\TravelAssignedMail;
use App\Mail\TravelCompletedMail;
use App\Mail\TravelUnassignedMail;
use App\Services\MeetingService;
use App\Services\TravelService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TravelController extends Controller
{
    protected $service;
    protected $meetingService;
    protected $userService;

    public function __construct(TravelService $service, MeetingService $meetingService, UserService $userService)
    {
        $this->service = $service;
        $this->meetingService = $meetingService;
        $this->userService = $userService;
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
                'completed' => $request->completed ?? false,
            ];

            $this->service->update($travelId, $payload);

            DB::commit();

            return redirect()->route('travels.index')->with('success', 'Travel updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('travels.index')->with('error', 'Failed to update travel.');
            // return redirect()->route('travels.index')->with('error', $e->getMessage());
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

            // send assignation mail
            $allUsers = $this->userService->getAllUser();
            foreach ($allUsers as $user) {
                Mail::to($user->email)->send(new TravelAssignedMail($travel, $meeting, $user->name));
            }

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

            $travel = $this->service->findTravelById($travelId);
            $meeting = $this->meetingService->findMeetingById($travel->meeting_id);
            $this->service->removeFromMeeting($travelId);

            DB::commit();

            // send cancellation mail
            $allUsers = $this->userService->getAllUser();
            foreach ($allUsers as $user) {
                Mail::to($user->email)->send(new TravelUnassignedMail($travel, $meeting, $user->name));
            }

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

            $travel = $this->service->findTravelById($travelId);
            $meeting = $this->meetingService->findMeetingById($travel->meeting_id);

            $this->service->completeTravel($travelId);

            DB::commit();

            // send completion mail
            $allUsers = $this->userService->getAllUser();
            foreach ($allUsers as $user) {
                Mail::to($user->email)->send(new TravelCompletedMail($travel, $meeting, $user->name));
            }

            return redirect()->back()->with('success', 'Travel Planner berhasil diselesaikan.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to complete Travel Planner.');
        }
    }
}
