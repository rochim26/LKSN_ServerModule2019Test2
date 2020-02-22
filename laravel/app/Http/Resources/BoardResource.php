<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BoardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'creator_id' => $this->creator_id,
            'members' => \App\Http\Resources\BoardMemberResource::collection($this->boardMembers),
            'lists' => \App\Http\Resources\BoardListResource::collection($this->boardLists)
        ];
    }
}
