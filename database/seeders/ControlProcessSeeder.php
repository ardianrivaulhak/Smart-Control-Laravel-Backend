<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ControlProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('control_process')->insert([
            [
                "id" => Str::uuid()->toString(),
                "name" => "Painting Tipis/Belang",
                'section_id' => "948fe8af-c321-47d5-b0bc-d4239c07ed4e",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null
            ], [
                "id" => Str::uuid()->toString(),
                "name" => "Pin Hole/Blow Hole",
                'section_id' => "58e91e4d-0dfb-4c17-9889-8427aaefe76c",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null
            ]
        ]);
    }
}
