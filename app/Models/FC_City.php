<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 21-05-2019
 * Time: 09:58
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class FC_City extends Model{

    protected $table = 'FC_City';

    public function hasState()
    {
        return $this->belongsTo('App\Models\TFC_State', 'FC_State_id', 'id');
    }
}