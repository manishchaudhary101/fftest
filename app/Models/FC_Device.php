<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/***
 * Class FC_Device
 *
 * @package App
 * @property integer id
 * @property \DateTime created_at
 * @property \DateTime modified_at
 * @property \DateTime deleted_at
 * @property string biostrip_macid
 */
class FC_Device extends Model
{
    protected $table = "FC_Device";
    protected $casts = [
        'created_at' => 'datetime',
        'modified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'modified_at',
        'deleted_at',
    ];
}
