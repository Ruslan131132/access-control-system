<?php

namespace App\Console\Commands;

use App\Models\Log;
use App\Models\User;
use App\Services\Messaging\AccessEvent;
use App\Services\Messaging\AccessEventPublisher;
use Illuminate\Console\Command;

class SimulateAccessEventsCommand extends Command
{
    protected $signature = 'rabbitmq:simulate-access-events
                            {--interval=8 : Seconds between published events}
                            {--once : Publish a single batch and exit}';

    protected $description = 'Publish realistic IoT / ACS events into RabbitMQ (demo data source)';

    private array $scenarios = [
        [
            'event_type' => 'sensor.door_ajar',
            'message' => 'Датчик двери: створка оставлена открытой более 30 секунд',
            'log_type' => Log::WARNING,
            'grant_door' => false,
            'deny_door' => false,
        ],
        [
            'event_type' => 'sensor.tamper',
            'message' => 'Считыватель QR: зафиксирована попытка физического вмешательства',
            'log_type' => Log::WARNING,
            'grant_door' => false,
            'deny_door' => true,
        ],
        [
            'event_type' => 'acs.heartbeat',
            'message' => 'Контроллер доступа: heartbeat OK, соединение с брокером стабильно',
            'log_type' => Log::SUCCESS,
            'grant_door' => false,
            'deny_door' => false,
        ],
        [
            'event_type' => 'sensor.badge_denied',
            'message' => 'RFID-считыватель: неизвестная карта отклонена',
            'log_type' => Log::FAIL,
            'grant_door' => false,
            'deny_door' => true,
        ],
        [
            'event_type' => 'sensor.intrusion',
            'message' => 'Периметр: сработал датчик движения при закрытой двери',
            'log_type' => Log::WARNING,
            'grant_door' => false,
            'deny_door' => true,
        ],
        [
            'event_type' => 'acs.remote_unlock',
            'message' => 'Удалённая команда разблокировки от оператора АСКУД',
            'log_type' => Log::SUCCESS,
            'grant_door' => true,
            'deny_door' => false,
        ],
    ];

    public function handle(AccessEventPublisher $publisher): int
    {
        $interval = max(3, (int) $this->option('interval'));

        do {
            $scenario = $this->scenarios[array_rand($this->scenarios)];
            $user = User::query()->inRandomOrder()->first();

            $publisher->publish(new AccessEvent(
                eventType: $scenario['event_type'],
                message: $scenario['message'],
                logType: $scenario['log_type'],
                userId: $user?->id,
                ip: sprintf('10.0.%d.%d', random_int(1, 20), random_int(2, 254)),
                userAgent: 'ACS-Controller/1.0',
                source: 'iot-simulator',
                grantDoor: $scenario['grant_door'],
                denyDoor: $scenario['deny_door'],
                meta: [
                    'controller_id' => 'ACS-'.str_pad((string) random_int(1, 12), 3, '0', STR_PAD_LEFT),
                    'zone' => 'main-entrance',
                ],
            ));

            $this->info(sprintf('Published: %s', $scenario['event_type']));

            if ($this->option('once')) {
                break;
            }

            sleep($interval);
        } while (true);

        return self::SUCCESS;
    }
}
