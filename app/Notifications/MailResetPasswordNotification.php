<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class MailResetPasswordNotification extends ResetPassword
{
    use Queueable;
    public $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        // parent::__construct($token);
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */

    public function toMail($notifiable)
    {
        $urlToResetForm = "http://localhost:8080/password-reset/token=" . $this->token;

        return ( new MailMessage )
            ->subject( 'Reset Password Notification' )
            ->line( "Hello! You are receiving this email because we received a password reset request for your account." )
            ->action( 'Reset Password', $urlToResetForm )
            ->line( "This password reset link will expire in ".config('auth.passwords.users.expire')." minutes" )
            ->line( "If you did not request a password reset, no further action is required.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
