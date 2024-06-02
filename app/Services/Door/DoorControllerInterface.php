<?php

namespace App\Services\Door;

interface DoorControllerInterface
{
    /**
     * Grant physical access (open the door).
     */
    public function grantAccess(): void;

    /**
     * Deny physical access (keep the door locked / signal denial).
     */
    public function denyAccess(): void;
}
