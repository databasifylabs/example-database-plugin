<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\DatabaseProviders\Drivers\AbstractSentinelDriver;
use App\Plugins\AbstractPlugin;
use App\Support\Databases\ClientPorts;

final class ExampleDatabasePlugin extends AbstractPlugin
{
    private const string VIEWS = 'example-database-plugin';

    private const string LINE = '9';

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', self::VIEWS);

        $this->databasify()->database(ExampleCache::id())
            ->label('Example Cache')
            ->handler(ExampleCache::class)
            ->category('Caching')
            ->stopSeconds(120)
            ->image('ghcr.io/databasifylabs/databasify-valkey')
            ->versions(self::LINE, [['value' => self::LINE, 'label' => 'Example Cache (Valkey 9)']])
            ->scripts('ssh.engines.valkey', [self::LINE => ['config.host-tuning' => $this->stepView('config.host-tuning')]])
            ->directory('/srv/valkey')
            ->systemUser('valkey')
            ->port(ClientPorts::KEY_VALUE)
            ->peerPorts([AbstractSentinelDriver::DATA_PORT, AbstractSentinelDriver::SENTINEL_PORT])
            ->nodeCertificates(['ca.crt', 'server.pem'])
            ->persistenceChoice()
            ->publicCertificate()
            ->failoverHint('Sentinel needs three voting nodes to fail over on its own. Add a standby or a read-only node.')
            ->register();
    }

    private function stepView(string $step): string
    {
        return self::VIEWS.'::ssh.engines.'.ExampleCache::id().'.lines.'.self::LINE.".{$step}";
    }
}
