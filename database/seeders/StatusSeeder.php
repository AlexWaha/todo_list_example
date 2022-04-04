<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{

    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $statuses = ['New', 'In Progress', 'Done'];

        foreach ($statuses as $name) {
            Status::create(
                [
                    'name' => $name,
                ]
            );
        }
    }
}
