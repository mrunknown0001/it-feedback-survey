<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckGoogleDriveToken extends Command
{
    protected $signature = 'google:check-token';
    protected $description = 'Verify Google Drive OAuth token is valid and alert if not';

    public function handle(): int
    {
        try {
            $config = config('filesystems.disks.google');

            if (empty($config['refreshToken'])) {
                $this->fail('GOOGLE_DRIVE_REFRESH_TOKEN is not set in .env');
            }

            $client = new \Google\Client();
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            $client->addScope(\Google\Service\Drive::DRIVE);

            $newToken = $client->fetchAccessTokenWithRefreshToken($config['refreshToken']);

            if (isset($newToken['error'])) {
                $message = 'Google Drive token is invalid or expired: ' . ($newToken['error_description'] ?? $newToken['error']);
                Log::error('[GoogleDriveToken] ' . $message);
                $this->sendAlert($message);
                $this->error($message);
                return self::FAILURE;
            }

            $this->info('Google Drive token is valid.');
            Log::info('[GoogleDriveToken] Token check passed.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $message = 'Google Drive token check failed: ' . $e->getMessage();
            Log::error('[GoogleDriveToken] ' . $message);
            $this->sendAlert($message);
            $this->error($message);
            return self::FAILURE;
        }
    }

    protected function sendAlert(string $message): void
    {
        $to = config('backup.notifications.mail.to');

        if (empty($to)) {
            return;
        }

        try {
            Mail::raw(
                "IT Feedback Survey Alert: Google Drive authentication failed.\n\n{$message}\n\nAction required: Re-run the OAuth flow at https://yourdomain.com/refresh-token to get a new refresh token.",
                fn ($mail) => $mail->to($to)->subject('[IT Feedback Survey] Google Drive Token Expired — Action Required')
            );
        } catch (\Exception $e) {
            Log::error('[GoogleDriveToken] Failed to send alert email: ' . $e->getMessage());
        }
    }
}
