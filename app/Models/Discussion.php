<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\ReplyMarkedAsBestReply;

class Discussion extends Model
{
    use HasFactory;

    public function author()
    {
      return $this->belongsTo(User::class, 'user_id');
    }

    public function replies()
    {
      return $this->hasMany(Reply::class);
    }

    public function getRouteKeyName()
    {
      return 'slug';
    }

    public function getBestReply()
    {
      return Reply::find($this->reply_id);
    }

    public function bestReply()
    {
      return $this->belongsTo(Reply::class, 'reply_id');
    }

    public function scopeFilterByChannels($builder)
    {
      if (request()->query('channel')) {
        $channel = Channel::where('slug', request()->query('channel'))->first();

        if($channel) {
          return $channel->id;
        }
        return $builder;
      }
      return $builder;
    }

    public function markAsBestReply(Reply $reply)
    {
      $this->update([
        'reply_id' => $reply->id
      ]);

      if($reply->owner->id === $this->author->id) {
        return;
      }
      $reply->owner->notify(new ReplyMarkedAsBestReply($reply->discussion));
    }
}
