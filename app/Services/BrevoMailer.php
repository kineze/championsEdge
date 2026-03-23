<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client as GuzzleClient;

class BrevoMailer
{
    protected $apiKey;
    protected $apiInstance;

    public function __construct()
    {
        $this->apiKey = config('services.brevo.key');

        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $this->apiKey);
        $this->apiInstance = new TransactionalEmailsApi(new GuzzleClient(), $config);
    }

    public function sendWelcomeEmail($toEmail, $toName, $password)
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Software Engineering',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Welcome to the Team!',
            'htmlContent' => view('emails.welcome', [
                'name' => $toName,
                'email' => $toEmail,
                'password' => $password,
            ])->render(),
        ])->successful();
    }

    public function sendPasswordResetEmail($toEmail, $toName, $newPassword)
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Software engineering',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Your Password Was Reset',
            'htmlContent' => view('emails.password-reset', [
                'name' => $toName,
                'email' => $toEmail,
                'password' => $newPassword,
            ])->render(),
        ])->successful();
    }

    public function sendReservationReceivedEmail(string $toEmail, string $toName, array $reservationDetails): bool
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Software Engineering',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Reservation Request Received',
            'htmlContent' => view('emails.reservation-request-received', [
                'name' => $toName,
                'details' => $reservationDetails,
            ])->render(),
        ])->successful();
    }

    public function sendReservationConfirmedEmail(string $toEmail, string $toName, array $reservationDetails): bool
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Software Engineering',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Reservation Confirmed',
            'htmlContent' => view('emails.reservation-confirmed', [
                'name' => $toName,
                'details' => $reservationDetails,
            ])->render(),
        ])->successful();
    }

    public function sendReservationPaymentUpdateEmail(string $toEmail, string $toName, array $reservationDetails): bool
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Software Engineering',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Reservation Payment Update',
            'htmlContent' => view('emails.reservation-payment-update', [
                'name' => $toName,
                'details' => $reservationDetails,
            ])->render(),
        ])->successful();
    }

}
