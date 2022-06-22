<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\Message;

/**
 * Event emiited when a message is sent to another user.
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public User $user;
    public User $receiver;
    public $message;


    /**
     * Create a new event instance.
     *
     * @param user the writter
     * @param receiver the receiver
     * @param message the message to be sent
     *
     * @return void
     */
    public function __construct(User $user, User $receiver, $message)
    {
        $this -> user = $user;
        $this -> receiver = $receiver;
        $this -> message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('encrypted-user_chat');
    }
}
