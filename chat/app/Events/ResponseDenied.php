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

/**
 * Event emitted when a user denies a contact request.
 */
class ResponseDenied implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sender;
    public $receiver_id;

    /**
     * Create a new event instance.
     *
     * @param sender the request's sender
     * @param receiver_id the id of request's receiver
     * 
     * @return void
     */
    public function __construct(User $sender, int $receiver_id)
    {
        $this -> sender = $sender;
        $this -> receiver_id = $receiver_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('encrypted-user_room');
    }
}
