<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

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

        if ($this->app->isProduction()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Gate::before(function (User $user, string $ability): ?bool {
            return $user->isAdmin() ? true : null;
        });
    }
}
