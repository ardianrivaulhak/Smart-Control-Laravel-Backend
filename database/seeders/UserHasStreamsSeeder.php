<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserHasStreamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('user_has_streams')->insert([
            [
                "id" => Str::uuid()->toString(),
                "user_id" => "5f614952-7e93-4a50-a0f9-3a1c668591c9",
                "stream_id" => "0b77b669-cf65-419a-9e4f-ca1af2480fc9",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "user_id" => "5f614952-7e93-4a50-a0f9-3a1c668591c9",
                "stream_id" => "3e9282d3-9717-4323-a6fd-e9dc38d47704",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "user_id" => "5f614952-7e93-4a50-a0f9-3a1c668591c9",
                "stream_id" => "f500668d-ab2e-4094-bdd1-e12368192d70",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
        ]);
    }
}
