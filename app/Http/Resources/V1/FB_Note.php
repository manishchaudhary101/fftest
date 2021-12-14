<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class FB_Note extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        return parent::toArray($request);
        return [
          'timestamp_epoch' => $this->created_at->getTimestamp(),
           'workout_id'     => $this->FB_Workout_id,
           'note_id'     => $this->id,
           'note'     => $this->note,
        ];
    }
}
