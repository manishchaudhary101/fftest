<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 21-05-2019
 * Time: 11:28
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
/**
 * Class FE_PasswordReset
 * @property int FB_User_id
 * @property int FB_Authorized_User_id
 */
class FR_User_has_Authorized_Users extends Model {

    public $table = 'FR_User_has_Authorized_Users';
    public $timestamps = false;

}