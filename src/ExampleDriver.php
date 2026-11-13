<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\Engines\Drivers\Sentinel\SentinelDriver;
use App\Engines\Drivers\Sentinel\Support\SentinelFamilyEngine;

final readonly class ExampleDriver extends SentinelDriver
{
    protected function flavor(): SentinelFamilyEngine
    {
        return SentinelFamilyEngine::valkeyAs('example');
    }
}
