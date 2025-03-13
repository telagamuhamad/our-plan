<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeetingRequest;
use App\Services\MeetingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MeetingController extends Controller
{
    protected $service;

    public function __construct(MeetingService $service)
    {
        $this->service = $service;
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
                'meeting_date' => $request->meeting_date,
                'note' => $request->note,
                'is_departure_transport_ready' => $request->has('is_departure_transport_ready'),
                'is_return_transport_ready' => $request->has('is_return_transport_ready'),
                'is_rest_place_ready' => $request->has('is_rest_place_ready'),
            ];
    
            $this->service->createMeeting($payload);
    
            DB::commit();
    
            return redirect()->route('meetings.index')->with('success', 'Meeting created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
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
                'meeting_date' => $request->meeting_date,
                'is_departure_transport_ready' => $request->has('is_departure_transport_ready'),
                'is_return_transport_ready' => $request->has('is_return_transport_ready'),
                'is_rest_place_ready' => $request->has('is_rest_place_ready'),
                'note' => $request->note,
            ];

            $this->service->updateMeeting($meetingId, $payload);

            DB::commit();

            return redirect()->route('meetings.index')->with('success', 'Meeting updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('meetings.index')->with('error', 'Failed to update meeting.');
        }
    }

    public function destroy($meetingId)
    {
        try {
            DB::beginTransaction();

            $this->service->findMeetingById($meetingId)->delete();
            
            DB::commit();
            
            return redirect()->route('meetings.index')->with('success', 'Meeting deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('meetings.index')->with('error', 'Failed to delete meeting.');
        }
    }
}
