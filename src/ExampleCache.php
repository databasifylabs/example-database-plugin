<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\DatabaseProviders\Drivers\AbstractSentinelDriver;
use App\Enums\Databases\SentinelFlavor;

final readonly class ExampleCache extends AbstractSentinelDriver
{
    public static function id(): string
    {
        return 'example-cache';
    }

    public function flavor(): SentinelFlavor
    {
        return SentinelFlavor::Valkey;
    }
}
