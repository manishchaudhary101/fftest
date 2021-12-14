<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property integer $display_order
 */
class FB_Community_tag extends Model
{
    protected  $table = 'FB_Community_tag';

    public function hasManyTags()
    {
        return $this->belongsToMany('App\Models\FB_Community_post','FR_Community_posts_has_tags');
    }
}
