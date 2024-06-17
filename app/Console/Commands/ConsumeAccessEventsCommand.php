<?php

namespace App\Console\Commands;

use App\Services\Messaging\AccessEvent;
use App\Services\Messaging\AccessEventProcessor;
use App\Services\Messaging\RabbitMqConnectionFactory;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class ConsumeAccessEventsCommand extends Command
{
    protected $signature = 'rabbitmq:consume-access-events';

    protected $description = 'Consume access-control events from RabbitMQ';

    public function handle(
        RabbitMqConnectionFactory $connectionFactory,
        AccessEventProcessor $processor,
    ): int {
        $this->info('Waiting for access events...');

        $connection = $connectionFactory->make();
        $channel = $connection->channel();

        $exchange = config('rabbitmq.exchange');
        $queue = config('rabbitmq.queue');
        $routingKey = config('rabbitmq.routing_key');

        $channel->exchange_declare($exchange, 'topic', false, true, false);
        $channel->queue_declare($queue, false, true, false, false);
        $channel->queue_bind($queue, $exchange, $routingKey);
        $channel->basic_qos(null, 1, null);

        $channel->basic_consume(
            $queue,
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($processor) {
                try {
                    $payload = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);
                    $event = AccessEvent::fromArray($payload);
                    $processor->handle($event);

                    $this->line(sprintf(
                        '[%s] %s — %s',
                        now()->toTimeString(),
                        $event->eventType,
                        $event->message
                    ));

                    $message->ack();
                } catch (Throwable $exception) {
                    $this->error('Failed to process message: '.$exception->getMessage());
                    $message->nack(false, false);
                }
            }
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

        return self::SUCCESS;
    }
}
