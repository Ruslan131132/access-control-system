<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class AccessEventPublisher
{
    public function __construct(
        private readonly RabbitMqConnectionFactory $connectionFactory,
        private readonly AccessEventProcessor $processor,
    ) {}

    public function publish(AccessEvent $event): void
    {
        try {
            $connection = $this->connectionFactory->make();
            $channel = $connection->channel();

            $exchange = config('rabbitmq.exchange');
            $queue = config('rabbitmq.queue');
            $routingKey = config('rabbitmq.routing_key');

            $channel->exchange_declare($exchange, 'topic', false, true, false);
            $channel->queue_declare($queue, false, true, false, false);
            $channel->queue_bind($queue, $exchange, $routingKey);

            $message = new AMQPMessage(
                json_encode($event->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]
            );

            $channel->basic_publish($message, $exchange, $routingKey);

            $channel->close();
            $connection->close();
        } catch (Throwable $exception) {
            Log::warning('RabbitMQ unavailable, processing access event locally', [
                'error' => $exception->getMessage(),
            ]);

            $this->processor->handle($event);
        }
    }
}
