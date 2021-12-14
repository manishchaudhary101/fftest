<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/***
 * Class FE_UserPolicyAcceptance
 *
 * @package App\Models
 * @property integer id
 * @property \DateTime created_at
 * @property \DateTime updated_at
 * @property integer FB_User_id
 * @property integer FC_Policy_id
 * @property integer policy_id
 */
class FE_UserPolicyAcceptance extends Model
{

    protected $table = "FE_UserPolicyAcceptance";
    protected $fillable = ['FB_User_id','FC_Policy_id','policy_id'];
    function policy()
    {
        return $this->belongsTo('\App\Models\FC_Policy','FC_Policy_id','id');
    }
    function user()
    {
        return $this->belongsTo('\App\Models\FB_User','FB_User_id','id');
    }
}
