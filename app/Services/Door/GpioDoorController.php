<?php

namespace App\Services\Door;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class GpioDoorController implements DoorControllerInterface
{
    public function grantAccess(): void
    {
        $this->run('gpio mode 9 out && gpio write 9 1 && gpio write 11 0');
        Log::info('[door:gpio] ACCESS GRANTED');
    }

    public function denyAccess(): void
    {
        $this->run('gpio mode 11 out && gpio write 11 1 && gpio write 9 0');
        Log::warning('[door:gpio] ACCESS DENIED');
    }

    private function run(string $command): void
    {
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException('GPIO command failed: '.$command);
        }
    }
}
