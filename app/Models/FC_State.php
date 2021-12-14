<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 21-05-2019
 * Time: 09:57
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class FC_State extends Model {

    public function hasCountry()
    {
        return $this->belongsTo('App\Models\TFC_Country', 'TFC_Country_id', 'id');
    }
}