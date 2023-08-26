<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StandardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('control_process_standards')->insert([
            [
                "id" => Str::uuid()->toString(),
                'form_control_process_id' => "fb62857b-0787-4077-9207-46aaac70f65a",
                "name" => "Spary Manual oleh man power khsusu, dilakukan rotasi / 30 menit",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null
            ],
            [
                "id" => Str::uuid()->toString(),
                'form_control_process_id' => "fb62857b-0787-4077-9207-46aaac70f65a",
                "name" => "Thicness paling sesuai standard (min 20p)",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null
            ],
        ]);
    }
}
