<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'chat_id',
        'sender_id',
        'receiver_id',
        'content',
        'is_read',
        'read_at'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function scopeUnreadCount($query, $userId)
    {
        return $query->where('is_read', false)
            ->where('receiver_id', $userId)
            ->count();
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId);
        });
    }

    public function scopeByChat($query, $chatId)
    {
        return $query->where('chat_id', $chatId);
    }

    public function scopeReadMessages($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeUnreadMessages($query, $userId)
    {
        return $query->where('is_read', false)
            ->where('receiver_id', $userId);
    }

    public function scopeOrderLatestMessage($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
