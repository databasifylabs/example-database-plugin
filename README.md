# Example Database Plugin

An example [Databasify](https://github.com/databasifylabs/databasify) plugin that adds a community engine called **Example Cache** (`example`, category Caching).

A community engine is two pieces:
- **its images**, built from [`databasifylabs/engine-template`](https://github.com/databasifylabs/engine-template): the official database image, its tools and the [engine SDK](https://github.com/databasifylabs/engine-sdk) 1.0 runtime;
- **this plugin**, which registers the engine and ships its **driver**: the PHP class that runs the engine's Bash scripts inside the container.

This example runs on the template's own image, `ghcr.io/databasifylabs/databasify-example` (Valkey as a stand-in database). Its driver reuses Databasify's Sentinel driver, the one behind the built-in Valkey and Redis engines, under the id `example`.

## Try it

1. Download this repository as a zip (or point Databasify at `databasifylabs/plugin-example-database`).
2. In Databasify 0.2 or later, open **Plugins** and install it from the repository or upload the zip.
3. Create a database and pick **Example Cache**. It offers line 9, and runs and connects like Valkey.

## File layout

```
composer.json                      name, PSR-4 autoload and extra.databasify (plugin class, requires, icon, engines)
src/ExampleDatabasePlugin.php      registers the engine in boot()
src/ExampleDriver.php              the engine's driver
public/icon.svg                    plugin icon
public/icons/database-providers/example.svg
                                   engine icon
```

## What the registration sets, and why

- `extra.databasify.engines` in `composer.json` (`["example"]`): every engine id the plugin registers. Databasify reads it before any plugin code runs and shows the admin a warning to confirm, because a community engine is built and supported by the plugin's author, not Databasify.
- `extra.databasify.requires` (`^0.2`): drivers arrived in Databasify 0.2.
- `image('ghcr.io/databasifylabs/databasify-example')`: Databasify picks the newest release tag of the database's line and pins its digest. The image's `io.databasify.engine` label must equal the engine id (`example`): it names the container (`databasify-example`) and its data folder (`/srv/example`), and Databasify refuses an image built for another engine.
- `driver(ExampleDriver::class)`: required. Databasify refuses an engine without one.
- `versions('9', [...])`: the lines offered on the create page, each one a tag prefix in the image.
- `connection(...)`: how the connection details page builds the URI, the CLI command and the Laravel `.env` keys. The second profile is used once a domain with a public certificate is attached.
- `migrationSource(6379, ...)`, `readOnlyNodesVote()`, `persistenceChoice()`, `publicCertificate()`, `failoverHint()`: the same behaviour as the built-in Valkey engine.

## The driver

`ExampleDriver` extends Databasify's `SentinelDriver` and names its flavor:

```php
final readonly class ExampleDriver extends SentinelDriver
{
    protected function flavor(): SentinelFamilyEngine
    {
        return SentinelFamilyEngine::valkeyAs('example');
    }
}
```

`valkeyAs('example')` runs Valkey's binaries as the image's `valkey` user, with Valkey's settings, under the engine's own home (`/srv/example`) and unit names (`example-server`).

An engine that isn't Valkey or Redis writes its own driver: a class extending `App\Engines\Drivers\AbstractEngineDriver` that declares its facts per line and hands out its groups (`nodes`, `cluster` and `config` are required; `accounts`, `archive`, `migration` and `pools` are optional). Each group method runs one Blade script inside the container. Ship the scripts in the plugin and register their folder under the engine id:

```php
public function boot(): void
{
    $this->loadViewsFrom(__DIR__.'/../resources/scripts', 'acme');
    // ...
}
```

The driver then runs `acme::nodes/start` (the file `resources/scripts/nodes/start.blade.php`). Values travel in the script's environment, never quoted into the script. The [`adding-database-engine`](https://github.com/databasifylabs/databasify/tree/0.x/.claude/skills/adding-database-engine) skill in Databasify covers the script rules and points to the closest built-in engine to copy.

## Start your own engine plugin

- **Your images:** create a repository from [`databasifylabs/engine-template`](https://github.com/databasifylabs/engine-template). Its workflows publish `ghcr.io/<your owner>/databasify-<engine>` with release tags per line. Make the package public, because Databasify pulls images without credentials.
- **Your plugin:** copy this repository, declare your engine id in `extra.databasify.engines`, set your id, label, category, `image()`, lines and `connection()` in the plugin class, and write your driver.

Read more in [Writing plugins](https://docs.databasify.io/plugins/writing-plugins/).

## License

Apache-2.0
