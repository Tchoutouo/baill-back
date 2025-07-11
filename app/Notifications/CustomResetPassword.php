<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPassword extends Notification
{
    use Queueable;

    private $url_front;
    public $token;
    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->url_front =  env('RESET_PASSWORD_URL');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        try {
            //** Génère le lien de réinitialisation */
            // $url = url(route('password.reset', [
            //     'token' => $this->token,
            //     'email' => $notifiable->getEmailForPasswordReset(),
            // ]));

            $url = $this->url_front.$this->token.'/'.$notifiable->getEmailForPasswordReset();
            dump($this->token);
            return (new MailMessage)
                        ->view('emails.password_forget', [ 'url'=> $url, 'user'=> $notifiable]);

        } catch (\Exception $e) {
            dd($e,'exece');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
