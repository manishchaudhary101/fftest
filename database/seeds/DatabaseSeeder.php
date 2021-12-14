<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('FC_Enum')->insert([
            [
                'id' => 1,
                'group_id' => ENUM_STATUS_GROUP,
                'name' => 'ENUM_STATUS_INACTIVE',
                'value_int' => 0,
                'value_text' => 'Inactive value',
            ],

            [
                'id' => 2,
                'group_id' => ENUM_STATUS_GROUP,
                'name' => 'ENUM_STATUS_ACTIVE',
                'value_int' => 1,
                'value_text' => 'Active value',
            ],

            [
                'id' => 3,
                'group_id' => ENUM_GENDER_GROUP,
                'name' => 'ENUM_GENDER_FEMALE',
                'value_int' => 1,
                'value_text' => 'Female',
            ],

            [
                'id' => 4,
                'group_id' => ENUM_GENDER_GROUP,
                'name' => 'ENUM_GENDER_MALE',
                'value_int' => 2,
                'value_text' => 'Male',
            ]


        ]);
    }
}
