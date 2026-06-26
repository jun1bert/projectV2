<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppointmentCompletionNotifier
{
    public function notify(Appointment $appointment): void
    {
        if ($appointment->completion_notified_at) {
            return;
        }

        $appointment->loadMissing('service');

        $message = $this->message($appointment);
        $sent = false;

        if ($appointment->email) {
            $sent = $this->sendEmail($appointment, $message) || $sent;
        }

        if ($appointment->contact_number) {
            $sent = $this->sendSms($appointment, $message) || $sent;
        }

        if ($sent) {
            $appointment->forceFill([
                'completion_notified_at' => now(),
            ])->save();
        }
    }

    private function sendEmail(Appointment $appointment, string $message): bool
    {
        try {
            Mail::raw($message, function ($mail) use ($appointment) {
                $mail->to($appointment->email, $appointment->full_name)
                    ->subject('Thank you for visiting Martinis & Manicures');
            });

            return true;
        } catch (\Throwable $e) {
            Log::warning('Appointment completion email failed', [
                'appointment_id' => $appointment->id,
                'email' => $appointment->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function sendSms(Appointment $appointment, string $message): bool
    {
        $apiKey = config('services.semaphore.key');
        $senderName = config('services.semaphore.sender_name');

        if (!$apiKey) {
            Log::info('Appointment completion SMS skipped; Semaphore is not configured.', [
                'appointment_id' => $appointment->id,
                'contact_number' => $appointment->contact_number,
                'message' => $message,
            ]);

            return false;
        }

        try {
            $payload = [
                'apikey' => $apiKey,
                'number' => $this->normalizePhilippineNumber($appointment->contact_number),
                'message' => $message,
            ];

            if ($senderName) {
                $payload['sendername'] = $senderName;
            }

            $response = Http::asForm()
                ->timeout(10)
                ->post('https://api.semaphore.co/api/v4/messages', $payload);

            if (!$response->successful()) {
                Log::warning('Appointment completion SMS failed', [
                    'appointment_id' => $appointment->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Appointment completion SMS exception', [
                'appointment_id' => $appointment->id,
                'contact_number' => $appointment->contact_number,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function message(Appointment $appointment): string
    {
        $service = $appointment->service->name ?? 'your service';

        return "Hi {$appointment->full_name}, thank you for visiting Martinis & Manicures. Your {$service} appointment has been completed. We hope to see you again soon.";
    }

    private function normalizePhilippineNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);

        if (str_starts_with($digits, '09')) {
            return '63' . substr($digits, 1);
        }

        if (str_starts_with($digits, '639')) {
            return $digits;
        }

        return $digits;
    }
}
