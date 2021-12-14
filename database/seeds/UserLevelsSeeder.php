<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        DB::table('FC_Enum')->insert([
                                         [
                                             'id' => ENUM_USERLEVEL_DEFAULT,
                                             'group_id' => ENUM_USERLEVEL_GROUP,
                                             'name' => 'ENUM_USERLEVEL_DEFAULT',
                                             'value_int' => 10,
                                             'value_text' => 'Default User',
                                         ],
                                         [
                                             'id' => ENUM_USERLEVEL_PREMIUM,
                                             'group_id' => ENUM_USERLEVEL_GROUP,
                                             'name' => 'ENUM_USERLEVEL_DOCTOR',
                                             'value_int' => 15,
                                             'value_text' => 'Premium Level',
                                         ],
                                         [
                                            'id' => ENUM_USERLEVEL_FHP_PREMIUM,
                                            'group_id' => ENUM_USERLEVEL_GROUP,
                                            'name' => 'ENUM_USERLEVEL_DOCTOR',
                                            'value_int' => 16,
                                            'value_text' => 'FHP Premium Level',
                                        ],
                                         [
                                             'id' => ENUM_USERLEVEL_DOCTOR,
                                             'group_id' => ENUM_USERLEVEL_GROUP,
                                             'name' => 'ENUM_USERLEVEL_DOCTOR',
                                             'value_int' => 20,
                                             'value_text' => 'Doctor Level',
                                         ],
                                         [
                                            'id' => ENUM_USERLEVEL_FHP_DOCTOR,
                                            'group_id' => ENUM_USERLEVEL_GROUP,
                                            'name' => 'ENUM_USERLEVEL_DOCTOR',
                                            'value_int' => 25,
                                            'value_text' => 'FHP Doctor Level',
                                        ],
                                         [
                                             'id' => ENUM_USERLEVEL_ADMIN,
                                             'group_id' => ENUM_USERLEVEL_GROUP,
                                             'name' => 'ENUM_USERLEVEL_ADMIN',
                                             'value_int' => 30,
                                             'value_text' => 'Admin',
                                         ],
                                     ]
        );
        DB::table('FB_User')->update(['userlevel_enum'=>ENUM_USERLEVEL_DEFAULT]);
        DB::table('FB_User')->whereIn('email',explode(',',env('ADMIN_EMAILS')))->update(['userlevel_enum'=>ENUM_USERLEVEL_ADMIN]);
    }
}
