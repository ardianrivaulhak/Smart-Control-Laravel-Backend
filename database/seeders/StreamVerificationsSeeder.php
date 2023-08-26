<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StreamVerificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('stream_verifications')->insert([
            [
                "id" => Str::uuid()->toString(),
                "stream_id" => "3baee2cd-33f0-4f02-9744-5fef51f2409a",
                "type" => "verification_1",
                "name" => "Dept Olahraga",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null
            ],
            [
                "id" => Str::uuid()->toString(),
                "stream_id" => "3baee2cd-33f0-4f02-9744-5fef51f2409a",
                "type" => "verification_2",
                "name" => "Dept Kesenian",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null
            ]
        ]);
    }
}
