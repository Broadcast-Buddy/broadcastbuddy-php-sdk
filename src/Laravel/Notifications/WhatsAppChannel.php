<?php

namespace BroadcastBuddy\Laravel\Notifications;

use BroadcastBuddy\Client;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification via WhatsApp.
     */
    public function send($notifiable, Notification $notification): ?array
    {
        if (!method_exists($notification, 'toWhatsApp')) {
            return null;
        }

        $message = $notification->toWhatsApp($notifiable);

        if (is_string($message)) {
            $message = WhatsAppMessage::create($message);
        }

        if (!$message instanceof WhatsAppMessage) {
            return null;
        }

        $to = $message->recipient ?: $notifiable->routeNotificationFor('whatsapp', $notification) ?: $notifiable->routeNotificationFor('WhatsApp', $notification) ?: $notifiable->phone_number ?? $notifiable->phone ?? null;

        if (!$to) {
            return null;
        }

        // 1. If sending a poll
        if (!empty($message->pollQuestion) && !empty($message->pollOptions)) {
            return $this->client->sendPoll($to, $message->pollQuestion, $message->pollOptions);
        }

        // 2. If sending an image
        if (!empty($message->image)) {
            return $this->client->sendImage($to, $message->image, $message->content);
        }

        // 3. If sending a document
        if (!empty($message->document)) {
            return $this->client->sendDocument($to, $message->document, $message->documentName ?? 'document.pdf', $message->content);
        }

        // 4. Standard text message
        return $this->client->sendMessage($to, $message->content);
    }
}
