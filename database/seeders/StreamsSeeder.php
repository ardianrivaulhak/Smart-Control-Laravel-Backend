<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StreamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('streams')->insert([
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Aluminium Product',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ], [
                'id' => Str::uuid()->toString(),
                'name' => 'Steel Product',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Iron Product',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Plastic Product',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]
        ]);
    }
}
