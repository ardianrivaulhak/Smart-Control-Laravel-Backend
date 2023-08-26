<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('form_control_processes')->insert([
            [
                "id" => Str::uuid()->toString(),
                "document_id" => "4fdcc20b-a461-48d8-a3cc-c9400097697c",
                "section_id" => "1a1e5d58-1d6b-4795-a6a9-7bda863c0d9a",
                "control_process_name" => "Painting Tipis/Belang",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null
            ],
        ]);
    }
}
