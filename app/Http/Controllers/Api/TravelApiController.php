<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest;
use App\Mail\TravelAssignedMail;
use App\Mail\TravelCompletedMail;
use App\Mail\TravelUnassignedMail;
use App\Services\Api\MeetingService;
use App\Services\Api\TravelService;
use App\Services\Api\UserService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TravelApiController extends Controller
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

        return response()->json([
            'success' => true,
            'travels' => $travels
        ], 200);
    }

    public function show($travelId)
    {
        $travel = $this->service->findTravelById($travelId);
        if (empty($travel)) {
            return response()->json([
                'success' => false,
                'message' => 'Travel not found.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'travel' => $travel
        ], 200);
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

            return response()->json([
                'success' => true,
                'message' => 'Travel created successfully.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create travel.',
                'error' => $e->getMessage()
            ], 400);
        }
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

            return response()->json([
                'success' => true,
                'message' => 'Travel updated successfully.'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update travel.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy($travelId)
    {
        try {
            DB::beginTransaction();

            $this->service->delete($travelId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Travel deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete travel.',
                'error' => $e->getMessage()
            ]);
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

            $validateVisitDate = $this->service->validateVisitDate($visitDate, $meeting->start_date, $meeting->end_date);
            if (!$validateVisitDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Visit Date harus dalam rentang tanggal Meeting.' 
                ], 400);
            }
            $this->service->assignToMeeting($meeting, $travel, $visitDate);

            DB::commit();

            $updatedTravel = $this->service->findTravelById($request->travel_id);

            // send assignation mail
            $allUsers = $this->userService->getAllUser();
            foreach ($allUsers as $user) {
                Mail::to($user->email)->send(new TravelAssignedMail($updatedTravel, $meeting, $user->name));
            }

            return response()->json([
                'success' => true,
                'message' => 'Travel Planner berhasil di-assign ke Meeting.'
            ], 200);
            
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign Travel Planner to Meeting.',
                'error' => $e->getMessage()
            ]);
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

            return response()->json([
                'success' => true,
                'message' => 'Travel Planner berhasil dihapus dari Meeting.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove Travel Planner from Meeting.',
                'error' => $e->getMessage()
            ]);
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

            return response()->json([
                'success' => true,
                'message' => 'Travel Planner berhasil diselesaikan.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete Travel Planner.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getUnassignedTravels()
    {
        $travels = $this->service->findWithoutMeeting();

        return response()->json([
            'success' => true,
            'data' => $travels  
        ], 200);
    }

    public function updateVisitDate(Request $request, $travelId)
    {
        $visitDate = $request->visit_date;
        $travel = $this->service->findTravelById($travelId);
        if (empty($travel)) {
            return response()->json([
                'success' => false,
                'message' => 'Travel not found.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $validateVisitDate = $this->service->validateVisitDate($visitDate, $travel->meeting->start_date, $travel->meeting->end_date);
            if (!$validateVisitDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Visit Date harus dalam rentang tanggal Meeting.' 
                ], 400);
            }

            $this->service->updateVisitDate($travelId, $visitDate);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Visit Date berhasil diubah.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Visit Date.',
                'reference' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 400);
        }
    }
}
