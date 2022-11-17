<?php

namespace App\Traits\Moderation;

use Illuminate\Database\Eloquent\Builder;

trait Moderatable
{
    public function markAccepted()
    {
        $this->status = Status::ACCEPTED;
        $this->save();
        $this->moderationHistory()->create(['status' => Status::ACCEPTED]);
    }

    public function markRejected($comment = null, $data = null)
    {
        $this->status = Status::REJECTED;
        $this->save();
        $this->moderationHistory()->create([
            'status' => Status::REJECTED,
            'comment' => $comment,
            'data' => $data,
        ]);
    }

    public function putToModeration()
    {
        $this->status = Status::PENDING;
        $this->save();
        $this->moderationHistory()->create(['status' => Status::PENDING]);
    }

    public function moderationHistory()
    {
        return $this->morphMany(Moderation::class, 'moderatable');
    }

    public function moderationStatus()
    {
        return $this->moderationHistory()->where('is_last', true)->limit(1);
    }

    public function scopeOnlyAccepted(Builder $query)
    {
        return $query->where('status', Status::ACCEPTED);
    }
}
