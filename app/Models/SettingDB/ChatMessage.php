<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $connection = 'settings';
    protected $table = 'chatmessages';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
    ];
}
