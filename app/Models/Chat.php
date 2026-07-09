<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'is_group_chat',
        'chat_name',
    ];

    public function participants()
    {
        return $this->belongsToMany(User::class, 'chat_participants', 'chat_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc')->with('sender');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Get users in the chat (for both 1-on-1 and group)
    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_participants', 'chat_id', 'user_id')
            ->withPivot('joined_at');
    }

    // For group chats: get admin
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getParticipantIdsAttribute()
    {
        return $this->users()->pluck('user_id')->toArray();
    }

    public function getLatestMessageAttribute()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function getUnreadCountAttribute()
    {
        return $this->messages()
            ->where('is_read', false)
            ->where('receiver_id', auth()->id())
            ->count();
    }
}
