<?php

namespace Vulnerar\Agent\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Address;
use Throwable;
use Vulnerar\Agent\Event;
use Vulnerar\Agent\UserProvider;

final class MailSubscriber
{
    public function handleMessageSent(MessageSent $event): void
    {
        try {
            $envelope = $event->sent->getEnvelope();
            $address = function (Address $address): array {
                return [
                    'name' => $address->getName(),
                    'email' => $address->getAddress(),
                ];
            };

            $event = new Event(
                'mail',
                [
                    'mail' => [
                        'sender' => $address($envelope->getSender()),
                        'recipients' => array_map($address, $envelope->getRecipients()),
                        'subject' => $event->message->getSubject(),
                    ],
                    'user' => UserProvider::details(),
                    'ip_address' => request()?->ip(),
                ]
            );
            $event->ingest();
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            MessageSent::class => 'handleMessageSent',
        ];
    }
}