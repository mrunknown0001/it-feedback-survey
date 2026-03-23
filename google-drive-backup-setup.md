# Laravel Spatie Backup → Google Drive Setup Guide

A step-by-step reference for setting up automated database backups to Google Drive using Spatie Laravel Backup and OAuth credentials.

---

## Packages Required

```bash
composer require spatie/laravel-backup
composer require masbug/flysystem-google-drive-ext
composer require google/apiclient
```

---

## 1. Google Cloud Console Setup

1. Go to [console.cloud.google.com](https://console.cloud.google.com)
2. Create a new project (or use existing)
3. Enable the **Google Drive API**
4. Go to **APIs & Services → Credentials**
5. Create **OAuth 2.0 Client ID** → Application type: **Web application**
6. Add authorized redirect URI:
   ```
   https://yourdomain.com/oauth2callback
   ```
7. Copy the **Client ID** and **Client Secret**
8. Go to **OAuth consent screen** → set status to **Production** (important — Testing mode tokens expire after 7 days)

---

## 2. Environment Variables

Add to `.env`:

```env
GOOGLE_DRIVE_CLIENT_ID=your_client_id
GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret
GOOGLE_DRIVE_REFRESH_TOKEN=        # filled in after step 6
GOOGLE_DRIVE_FOLDER_ID=            # the Drive folder ID (from URL)
GOOGLE_DRIVE_FOLDER_PATH=Backup/traymon   # display path inside Drive e.g. "FolderName" or "Parent/Child"
```

---

## 3. Get the Refresh Token

Add these routes to `routes/web.php` temporarily:

```php
use Illuminate\Support\Facades\Route;

Route::get('refresh-token', function () {
    $client = new \Google\Client();
    $client->setClientId(config('filesystems.disks.google.clientId'));
    $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
    $client->setRedirectUri(url('/oauth2callback'));
    $client->addScope(\Google\Service\Drive::DRIVE);
    $client->setAccessType('offline');
    $client->setPrompt('consent');

    if (!request()->has('code')) {
        return redirect($client->createAuthUrl());
    }

    $token = $client->fetchAccessTokenWithAuthCode(request('code'));
    return response()->json($token);
});

Route::get('/oauth2callback', function () {
    $code = request('code');
    if ($code) {
        return redirect('/refresh-token?code=' . $code);
    }
    return response()->json(['error' => 'No authorization code received']);
});
```

Visit `https://yourdomain.com/refresh-token`, authorize with Google, and you'll get a JSON response:

```json
{
  "access_token": "ya29.xxx",
  "refresh_token": "1//0gxxx",   <-- copy this
  "expires_in": 3599,
  "token_type": "Bearer"
}
```

> **Note:** `refresh_token` only appears on first authorization. To force it again, go to [myaccount.google.com/permissions](https://myaccount.google.com/permissions), revoke access, and redo.

Set in `.env`:
```env
GOOGLE_DRIVE_REFRESH_TOKEN=1//0gxxx...
```

Remove or keep the routes — they are safe but no longer needed after setup.

---

## 4. Google Drive Folder

1. Create a folder in Google Drive where backups will be stored
2. Copy the folder ID from the URL:
   ```
   https://drive.google.com/drive/folders/THIS_IS_THE_FOLDER_ID
   ```
3. Set `GOOGLE_DRIVE_FOLDER_ID` and `GOOGLE_DRIVE_FOLDER_PATH` in `.env`

> **Note:** `GOOGLE_DRIVE_FOLDER_PATH` is the human-readable path used by the flysystem adapter (e.g., `Backup/traymon`). It must match the actual folder name(s) in your Drive.

---

## 5. Configure Filesystem Disk

In `config/filesystems.php`, add the `google` disk:

```php
'google' => [
    'driver'       => 'google',
    'clientId'     => env('GOOGLE_DRIVE_CLIENT_ID'),
    'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
    'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
    'folderId'     => env('GOOGLE_DRIVE_FOLDER_ID'),
    'folderPath'   => env('GOOGLE_DRIVE_FOLDER_PATH', '/'),
    'additionalScopes' => [],
],
```

---

## 6. Register the Google Driver

The `masbug/flysystem-google-drive-ext` package does **not** auto-register. You must manually register the driver in `app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        try {
            Storage::extend('google', function ($app, $config) {
                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->addScope(\Google\Service\Drive::DRIVE);
                $client->setAccessToken([
                    'refresh_token' => $config['refreshToken'],
                    'access_token'  => '',
                    'token_type'    => 'Bearer',
                    'expires_in'    => 3600,
                    'created'       => 0,
                ]);
                $client->fetchAccessTokenWithRefreshToken($config['refreshToken']);

                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folderPath'] ?? '/');
                $driver  = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch (\Exception $e) {
            //
        }
    }
}
```

---

## 7. Publish and Configure Spatie Backup

```bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

Edit `config/backup.php`:

```php
'source' => [
    'files' => [
        'include' => [base_path()],
        'exclude' => [base_path('vendor'), base_path('node_modules')],
    ],
    'databases' => [env('DB_CONNECTION', 'mysql')],
],

'destination' => [
    'disks' => [
        'local',
        'google',
    ],
],

'notifications' => [
    'mail' => [
        'to' => 'your@email.com',   // <-- update this
    ],
],
```

---

## 8. Schedule Daily Backups

In `routes/console.php` (Laravel 11+) or `app/Console/Kernel.php` (Laravel 10 and below):

**Laravel 11+** (`routes/console.php`):
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:run --only-db')->dailyAt('02:00');
Schedule::command('backup:clean')->dailyAt('02:30');
```

**Laravel 10 and below** (`app/Console/Kernel.php`):
```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('backup:run --only-db')->dailyAt('02:00');
    $schedule->command('backup:clean')->dailyAt('02:30');
}
```

---

## 9. Add Server Cron

On the production server:
```bash
crontab -e
```

Add:
```
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

---

## 10. Test

```bash
php artisan config:clear
php artisan backup:run --only-db
```

Check your Google Drive folder — a `TrayMon/` (or your `APP_NAME`) subfolder will be created containing the zip backup.

---

## Troubleshooting

| Error | Cause | Fix |
|-------|-------|-----|
| `Driver [google] is not supported` | Driver not registered | Add `Storage::extend('google', ...)` in `AppServiceProvider` |
| `401 UNAUTHENTICATED` | Wrong auth method (service account vs OAuth) | Use OAuth credentials (client_id, client_secret, refresh_token) |
| `403 storageQuotaExceeded` | Using a service account | Service accounts have no Drive quota — use OAuth instead |
| `Invalid token format` | Refresh token expired or wrong | Re-run `/refresh-token` route to get new token |
| `Unable to write file` | Wrong folder path | Set `GOOGLE_DRIVE_FOLDER_PATH` to the exact display name in Drive |
| Blank page on `/refresh-token` | Config cached, `env()` not working | Use `config('filesystems.disks.google.clientId')` instead of `env()` |
| OAuth token expires after 7 days | App in Testing mode | Set OAuth consent screen to **Production** in Google Cloud Console |

---

## Key Gotchas

- **Service accounts cannot upload to personal Google Drive** — they have no storage quota. Always use OAuth (user credentials).
- **`env()` does not work when config is cached** — always use `config()` in routes and controllers.
- **`GOOGLE_DRIVE_FOLDER_PATH` is not the folder ID** — it is the human-readable folder name/path as it appears in Drive.
- **Refresh token only appears once** — save it immediately. Revoke app access to force a new one.
- **OAuth consent screen must be Production** — Testing mode tokens expire after 7 days.
