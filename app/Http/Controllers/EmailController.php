<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    //
    public function sendEmail($html, $email, $subject)
    {
        // Mail::send([], [], function ($message) use ($html) {
        //     $message->to($user->cli_email)
        //         ->subject('Activate your account')
        //         ->from('my@email.com')
        //         ->html($html, 'text/html');
        // });
        var_dump(env('MAIL_FROM_ADDRESS'));
        var_dump(env('DB_DATABASE'));

        Mail::send( [], [],function ($message) use ($html, $email, $subject) {
            $message->subject($subject)
            ->from(env('MAIL_FROM_ADDRESS'))
                ->setTo($email)
                ->setBody($html, 'text/html');
        });
    
        // Mail::to($email)
        // ->subject($subject)
        // ->html($html)
        // ->send();
    }
    
}
