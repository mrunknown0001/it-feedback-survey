<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Brookside Farms Corporation',           'code' => 'BFC',     'sort_order' => 1],
            ['name' => 'Brookdale Farms Corporation',           'code' => 'BDL',     'sort_order' => 2],
            ['name' => 'Poultrypure Farms Corporation',         'code' => 'PFC',     'sort_order' => 3],
            ['name' => 'RH Farms',                              'code' => 'RH',      'sort_order' => 4],
            ['name' => 'Brookside Breeding and Genetics Corporation', 'code' => 'BBGC', 'sort_order' => 5],
            ['name' => 'Hatchery',                              'code' => 'Hatchery','sort_order' => 6],
            ['name' => 'Accounting',                            'code' => 'ACCTG',   'sort_order' => 7],
            ['name' => 'HR',                                    'code' => 'HR',      'sort_order' => 8],
            ['name' => 'Purchasing',                            'code' => 'PURCH',   'sort_order' => 9],
            ['name' => 'Audit',                                 'code' => 'AUDIT',   'sort_order' => 10],
            ['name' => 'Finance/Treasury',                      'code' => 'FIN',     'sort_order' => 11],
            ['name' => 'Sales and Marketing',                   'code' => 'S&M',     'sort_order' => 12],
            ['name' => 'Feed Mill',                             'code' => 'FM',      'sort_order' => 13],
        ];

        foreach ($locations as $location) {
            Location::create([...$location, 'is_active' => true]);
        }
    }
}
