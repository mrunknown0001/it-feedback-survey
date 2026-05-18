<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\Branding;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate(
            ['key' => 'brand_name'],
            ['value' => Branding::DEFAULT_BRAND_NAME]
        );

        Setting::firstOrCreate(
            ['key' => 'primary_color'],
            ['value' => Branding::DEFAULT_PRIMARY]
        );
    }
}
