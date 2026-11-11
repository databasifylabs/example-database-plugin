<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\DTOs\Databases\ConnectionProfile;
use App\Plugins\AbstractPlugin;

final class ExampleDatabasePlugin extends AbstractPlugin
{
    private const string ID = 'example-cache';

    private const array ENV_KEYS = ['host' => 'REDIS_HOST', 'port' => 'REDIS_PORT', 'username' => null, 'password' => 'REDIS_PASSWORD', 'database' => null];

    public function boot(): void
    {
        $this->databasify()->database(self::ID)
            ->label('Example Cache')
            ->category('Caching')
            ->image('ghcr.io/databasifylabs/databasify-valkey')
            ->versions('9', [['value' => '8', 'label' => 'Example Cache (Valkey 8)'], ['value' => '9', 'label' => 'Example Cache (Valkey 9)']])
            ->connection(
                $this->connectionVia('valkey-cli --tls --insecure -u rediss://{username}:{password}@{host}:{port}'),
                withPublicCertificate: $this->connectionVia('valkey-cli --tls -u rediss://{username}:{password}@{host}:{port}'),
            )
            ->migrationSource(6379, credentialsRequired: false, selectsDatabases: false)
            ->readOnlyNodesVote()
            ->persistenceChoice()
            ->publicCertificate()
            ->failoverHint('Sentinel needs three voting nodes to fail over on its own. Add a standby or a read-only node.')
            ->register();
    }

    private function connectionVia(string $command): ConnectionProfile
    {
        return new ConnectionProfile(
            uriScheme: 'rediss',
            uriQuery: '',
            tlsParameter: 'tls',
            tlsMode: 'on',
            command: $command,
            fixedUsername: 'default',
            selectsDatabase: false,
            envKeys: self::ENV_KEYS,
        );
    }
}
