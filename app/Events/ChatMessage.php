<?php
namespace Coyote\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ChatMessage implements ShouldBroadcast {
    use SerializesModels;

    public function __construct(
        public string $message,
        public int    $authorUserId,
        public string $authorUsername,
    ) {}

    public function broadcastOn(): Channel {
        return new Channel('global-chat');
    }

    public function broadcastAs(): string {
        return 'chat-message';
    }
}
