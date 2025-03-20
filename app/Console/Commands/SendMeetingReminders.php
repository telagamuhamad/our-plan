<?php

namespace App\Console\Commands;

use App\Mail\MeetingReminderMail;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMeetingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-meeting-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Meeting Reminder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $allUsers = User::all();
        $meetings = Meeting::whereDate('start_date', $now->copy()->addDays(3))
                        ->orWhereDate('start_date', $now->copy()->addDay())
                        ->get();

        if (!empty($meetings)) {
            foreach ($meetings as $meeting) {
                foreach ($allUsers as $user) {
                    Mail::to($user->email)->send(new MeetingReminderMail($meeting, $user->name));
                }
            }
            $this->info('Pengingat meeting telah dikirim.');   
        }
    }
}
