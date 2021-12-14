<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class FB_Insight
 * @property int id
 * @property int created_by
 * @property int category
 * @property string title
 * @property string content
 * @property string graph_data
 */

class FB_Insight extends Model{

    protected $table = 'FB_Insight';
    public $timestamps = true;
    protected $hidden = ['pivot'];

    public function hasManyWorkouts()
    {
        return $this->belongsToMany('\App\Models\FB_Workout','FR_Insight_has_workouts','FB_Insight_id','FB_Workout_id');
    }
}
