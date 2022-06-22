<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Message model represented by two main attributes: 'message' and 'receiver_id'.
 *
 * Each message is sent by a user and each user can send multiple 
 * messages (one to many relationship).
 *
 * Moreover, each message are composed of a content (the message attribute) and 
 * a receiver (the receiver_id attribute).
 */
class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['message', 'receiver_id'];

    /**
     * A message belongs to a user.
     */
    public function user(){
        return $this -> belongsTo(User::class);
    }
}
