<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeetingRequest;
use App\Mail\MeetingCancellationMail;
use App\Mail\MeetingConfirmationMail;
use App\Mail\MeetingUpdatedMail;
use App\Services\MeetingService;
use App\Services\TravelService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MeetingController extends Controller
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

        return view('meetings.index', [
            'meetings' => $meetings,
            'user' => $user
        ]);
    }

    public function show($meetingId)
    {
        $meeting = $this->service->findMeetingById($meetingId);
        $availableTravels = $this->travelService->findWithoutMeeting($meetingId);

        return view('meetings.show', [
            'meeting' => $meeting,
            'availableTravels' => $availableTravels
        ]);
    }

    public function create()
    {
        return view('meetings.create');
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
                'is_departure_transport_ready' => $request->has('is_departure_transport_ready'),
                'is_return_transport_ready' => $request->has('is_return_transport_ready'),
                'is_rest_place_ready' => $request->has('is_rest_place_ready'),
            ];
    
            $meeting = $this->service->createMeeting($payload);
    
            DB::commit();

            // Send mail
            $allUsers = $this->userService->getAllUser();
            foreach ($allUsers as $user) {
                Mail::to($user->email)->send(new MeetingConfirmationMail($meeting, $user->name));
            }
    
            return redirect()->route('meetings.index')->with('success', 'Meeting created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            // report($e);
            return redirect()->route('meetings.index')->with('error', 'Failed to create meeting.');
        }
    }    

    public function edit($meetingId)
    {
        $meeting = $this->service->findMeetingById($meetingId);
        if (empty($meeting)) {
            return redirect()->route('meetings.index')->with('error', 'Meeting not found.');
        }

        return view('meetings.edit', [
            'meeting' => $meeting
        ]);
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
                'is_departure_transport_ready' => $request->has('is_departure_transport_ready'),
                'is_return_transport_ready' => $request->has('is_return_transport_ready'),
                'is_rest_place_ready' => $request->has('is_rest_place_ready'),
                'note' => $request->note,
            ];

            $this->service->updateMeeting($meetingId, $payload);
            $meeting = $this->service->findMeetingById($meetingId);

            DB::commit();

            $allUsers = $this->userService->getAllUser();
            foreach ($allUsers as $user) {
                Mail::to($user->email)->send(new MeetingUpdatedMail($meeting, $user->name));
            }

            return redirect()->route('meetings.index')->with('success', 'Meeting updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            // report($e);
            return redirect()->route('meetings.index')->with('error', 'Failed to update meeting. '. $e->getMessage() . 'message');
            // return redirect()->route('meetings.index')->with('error', $e->getMessage());
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

            throw new \Exception('Test error message from production');
            
        //     return redirect()->route('meetings.index')->with('success', 'Meeting deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);
            Log::error($e->getMessage());
            return redirect()->route('meetings.index')->with('error', 'Error: ' . $e->getMessage());
            // report($e);
            // return redirect()->route('meetings.index')->with('error', $e->getMessage());
        }
    }
}
