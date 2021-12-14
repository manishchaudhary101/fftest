<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 27-06-2019
 * Time: 10:51
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class FC_UserOldData extends Model{

    protected  $table = 'FC_UserOldData';

    public $timestamps = false;

    protected $primaryKey = 'user_id_old';

    public function hasUser()
    {
        return $this->belongsTo('App\Models\FB_User','user_id_new','id');
    }
}