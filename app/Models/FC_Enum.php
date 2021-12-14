<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 21-05-2019
 * Time: 09:52
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class FC_Enum
 * @property int id
 * @property int group_id
 * @property string name
 * @property string value_text
 * @property float value_int
 */

class FC_Enum extends Model{

    protected $table = 'FC_Enum';
    public $timestamps = false;

    public function getAPIObj($public = true)
    {

        if ($public == true) {
            $array = array(
                'id' => $this->id,
                'name' => $this->name,
                'group_id' => $this->group_id,
                'value_int' => $this->value_int,
                'value_text' => $this->value_text,
            );

        } else {
            //for future use
            $array = array(
                'id' => $this->id,
                'name' => $this->name,
                'group_id' => $this->group_id,
                'value_int' => $this->value_int,
                'value_text' => $this->value_text,
            );
        }


        return $array;
    }
}