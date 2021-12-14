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
 * @property int id
 * @property int FB_User_id
 * @property string reset_token
 * @property \Datetime token_expiry
 */
class FE_PasswordReset extends Model {

    protected $table = 'FE_PasswordReset';

    public  $timestamps = false;

    public function hasUser()
    {
        return $this->belongsTo('App\Models\FB_User','FB_User_id','id');
    }
}