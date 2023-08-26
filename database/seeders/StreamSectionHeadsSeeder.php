<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StreamSectionHeadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('stream_section_head')->insert([
            [
                "id" => Str::uuid()->toString(),
                "stream_id" => "3baee2cd-33f0-4f02-9744-5fef51f2409a",
                "section_id" => "1a1e5d58-1d6b-4795-a6a9-7bda863c0d9a",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null
            ]
        ]);
    }
}
