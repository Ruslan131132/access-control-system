<?php

namespace App\Services\Door;

use Illuminate\Support\Facades\Log;

class MockDoorController implements DoorControllerInterface
{
    public function grantAccess(): void
    {
        Log::info('[door:mock] ACCESS GRANTED — relay unlocked');
    }

    public function denyAccess(): void
    {
        Log::warning('[door:mock] ACCESS DENIED — relay remains locked');
    }
}
