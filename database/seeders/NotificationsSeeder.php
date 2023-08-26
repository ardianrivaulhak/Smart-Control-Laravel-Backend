<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('notifications')->insert([
            [
                'id' => Str::uuid()->toString(),
                'cpi_order_id' => '42ec97dc-3086-4f71-97ca-2a2928b1d8b1',
                'user_id' => '6b27dd35-c84d-41ed-8f06-361a358a96eb',
                'status' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'cpi_order_id' => '51f18f45-f023-46de-ba34-17fa0b4731a9',
                'user_id' => '6b27dd35-c84d-41ed-8f06-361a358a96eb',
                'status' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'cpi_order_id' => 'dbe944bb-8f7d-4354-ac6b-c1c7af9aae69',
                'user_id' => '6b27dd35-c84d-41ed-8f06-361a358a96eb',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'cpi_order_id' => 'ac678a7a-03df-421b-b45c-69ce3fab74fb',
                'user_id' => '6b27dd35-c84d-41ed-8f06-361a358a96eb',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
    }
}
