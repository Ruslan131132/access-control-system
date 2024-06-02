<?php

namespace App\Console\Commands;

use App\Services\Door\DoorControllerInterface;
use Illuminate\Console\Command;

class ManageDoorCommand extends Command
{
    protected $signature = 'door:open {--grant : Grant access (open door); omit to deny}';

    protected $description = 'Control the physical door relay (GPIO or mock)';

    public function handle(DoorControllerInterface $door): int
    {
        if ($this->option('grant')) {
            $door->grantAccess();
            $this->info('Door access granted');
        } else {
            $door->denyAccess();
            $this->warn('Door access denied');
        }

        return self::SUCCESS;
    }
}
