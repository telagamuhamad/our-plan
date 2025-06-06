<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MeetingRequest;
use App\Mail\MeetingCancellationMail;
use App\Mail\MeetingConfirmationMail;
use App\Mail\MeetingUpdatedMail;
use App\Services\Api\MeetingService;
use App\Services\Api\TravelService;
use App\Services\Api\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MeetingApiController extends Controller
{
    protected $service;
    protected $travelService;
    protected $userService;

    public function __construct(MeetingService $service, TravelService $travelService, UserService $userService)
    {
        $this->service = $service;
        $this->travelService = $travelService;
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $searchTerms = [
            'traveler_name' => $request->traveler_name,
            'location' => $request->location,
            'meeting_date' => $request->meeting_date,
        ];

        $meetings = $this->service->getAllMeetings($searchTerms);
        
        // Loop meeting->travels to check travel visit date
        foreach ($meetings as $meeting) {
            if (!empty($meeting->travels)) {
                $isNeedUpdate = false;
                $countDataNeedUpdate = 0;
                foreach ($meeting->travels as $travel) {
                    if ($travel->visit_date < $meeting->start_date || $travel->visit_date > $meeting->end_date) {
                        $isNeedUpdate = true;
                        $countDataNeedUpdate++;
                    }
                    $travel['is_need_update'] = $isNeedUpdate;
                }
                $meeting['count_data_need_update'] = $countDataNeedUpdate;
            }
        }

        return response()->json([
            'meetings' => $meetings,
            'user' => $user
        ], 200);
    }

    public function show($meetingId)
    {
        $meeting = $this->service->findMeetingById($meetingId);
        $userData = $this->userService->findUserById($meeting->travelling_user_id);
        $assignedTravels = $this->travelService->findWithMeeting($meetingId);

        return response()->json([
            'meeting' => $meeting,
            'travels' => $assignedTravels,
            'travelling_user' => $userData
        ], 200);
    }

    public function store(MeetingRequest $request) 
    {
        $user = Auth::user();
    
        try {
            DB::beginTransaction();
    
            $payload = [
                'travelling_user_id' => $user->id,
                'location' => $request->location,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'note' => $request->note,
                'is_departure_transport_ready' => $request->is_departure_transport_ready,
                'is_return_transport_ready' => $request->is_return_transport_ready,
                'is_rest_place_ready' => $request->is_rest_place_ready
            ];
    
            $meeting = $this->service->createMeeting($payload);
    
            DB::commit();

            // Send mail
            $allUsers = $this->userService->getAllUser();
            foreach ($allUsers as $user) {
                Mail::to($user->email)->send(new MeetingConfirmationMail($meeting, $user->name));
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Meeting created successfully.',
                'data' => $meeting
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create meeting.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(MeetingRequest $request, $meetingId)
    {
        try {
            DB::beginTransaction();

            $payload = [
                'travelling_user_id' => $request->travelling_user_id,
                'location' => $request->location,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'note' => $request->note,
                'is_departure_transport_ready' => $request->is_departure_transport_ready,
                'is_return_transport_ready' => $request->is_return_transport_ready,
                'is_rest_place_ready' => $request->is_rest_place_ready,
            ];

            $this->service->updateMeeting($meetingId, $payload);
            $meeting = $this->service->findMeetingById($meetingId);

            DB::commit();

            $allUsers = $this->userService->getAllUser();
            foreach ($allUsers as $user) {
                Mail::to($user->email)->send(new MeetingUpdatedMail($meeting, $user->name));
            }

            return response()->json([
                'success' => true,
                'message' => 'Meeting updated successfully.',
                'data' => $meeting
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update meeting.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy($meetingId)
    {
        try {
            DB::beginTransaction();

            $meeting = $this->service->findMeetingById($meetingId);

            $this->service->deleteMeeting($meetingId);
            
            DB::commit();

            $allUsers = $this->userService->getAllUser();
            foreach ($allUsers as $user) {
                Mail::to($user->email)->send(new MeetingCancellationMail($meeting, $user->name));
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Meeting deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete meeting.',
                'error' => $e->getMessage()
            ]);
        }
    }
}
