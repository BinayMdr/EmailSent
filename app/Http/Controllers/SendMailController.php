<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Carbon\Carbon;
use Spatie\IcalendarGenerator\Components\Timezone;
use Spatie\IcalendarGenerator\Enums\EventStatus;
use Spatie\IcalendarGenerator\Enums\ParticipationStatus;

class SendMailController extends Controller
{
    public function index()
    {
        $data = DB::table('training_events')->where('id','8e5f90b1-fa11-42cd-bdc7-fe96a0a89f98')->first();
           
        try
        {
            Mail::send('mail.invitation', array(), function($message) use ($data){
                
                $mainEvent = Event::create()
                    ->name('Test')
                    ->uniqueIdentifier($data->id)
                    ->address($data->location)
                    ->startsAt(Carbon::parse($data->training_date . ' ' . $data->training_start_time)->shiftTimeZone('Asia/Kathmandu'))
                    ->endsAt(Carbon::parse($data->training_date . ' ' . $data->training_end_time)->shiftTimeZone('Asia/Kathmandu'))
                    ->withoutTimezone()
                    ->attendee(env('ADMIN_MAIL'),'Test User',ParticipationStatus::needs_action(), requiresResponse: true)
                    ->status(EventStatus::confirmed())
                    ->transparent();
            
                $timezone = Timezone::create('Asia/Kathmandu');
                $calendar = Calendar::create()
                    ->event($mainEvent)
                    ->timezone($timezone);
            
                $mail = $calendar->get();

                $message->subject('Training Event Approved');

                $message->to(env('ADMIN_MAIL'));

                $message->attachData($mail, "test.ics", [
                    'Content-Type' => 'text/calendar; charset=utf-8',
                    'Content-Disposition' => 'attachment; filename="test.ics"',
                ]);
            });
        } catch(Exception $e)
        {
            Log::error('Error');
        }

        dd('done');
    }
}
