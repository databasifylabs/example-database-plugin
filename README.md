# Example Database Plugin

An example [Databasify](https://github.com/databasifylabs/databasify) plugin that adds an engine called **Example Cache** (`example-cache`, category Caching).

It shows the smallest useful engine plugin: instead of writing a whole engine, it reuses the built-in Valkey driver family (`AbstractSentinelDriver`), the official `ghcr.io/databasifylabs/databasify-valkey` image and Valkey's step scripts, and replaces one step (`config.host-tuning`) with its own view. That view runs the core host tuning and prints one extra line, so you can see it in the deploy log.

## Try it

1. Download this repository as a zip (or point Databasify at `databasifylabs/example-database-plugin`).
2. In Databasify, open **Plugins**, install it from the repository or upload the zip.
3. Create a database and pick **Example Cache**. The deploy log on each server ends its host tuning with `host tuned for Example Cache by the example-database-plugin`.

## File layout

```
composer.json                      name, PSR-4 autoload and extra.databasify (plugin class, requires, icon)
src/ExampleDatabasePlugin.php      registers the engine in boot()
src/ExampleCache.php               the driver: Valkey's sentinel driver under the id example-cache
resources/views/ssh/engines/example-cache/lines/9/config/host-tuning.blade.php
                                   the one overridden step
public/icon.svg                    plugin icon
public/icons/database-providers/example-cache.svg
                                   engine icon
```

## What the registration sets, and why

- `image('ghcr.io/databasifylabs/databasify-valkey')`: without it the image would be `databasify-example-cache`, which does not exist.
- `directory('/srv/valkey')` and `systemUser('valkey')`: the sentinel driver writes its config, TLS and data under `/srv/<flavor>`, and the Valkey image runs as the `valkey` user. The defaults (`/srv/example-cache`, user `example-cache`) would not match either.
- `scripts('ssh.engines.valkey', ['9' => ['config.host-tuning' => '...']])`: every step comes from Valkey's line 9 folder except the overridden one. Overrides are per version line.
- The container is still named after the engine id: `databasify-example-cache`.

Keep any step that runs on its own under `resources/views/ssh/engines/...`: Databasify runs a step inside the engine container only when its view path (after `namespace::`) starts with `ssh.engines.`.

## Start your own engine plugin

- **Your own driver:** extend `AbstractDatabaseDriver` (or a family base such as `AbstractSentinelDriver`) and implement the engine-specific methods.
- **Your own image:** publish it to a registry with exact `<line>.x` release tags (for example `25.8.1`); Databasify picks the newest tag of the database's line and pins its digest. Register it with `->image('ghcr.io/you/databasify-yourengine')`.
- **Your own step scripts:** put a Blade file for every step under `resources/views/ssh/engines/<id>/lines/<line>/` and register `->scripts('<namespace>::ssh.engines.<id>')`. A missing step fails loudly with `MissingEngineScript`.
- **Full walkthrough:** the `adding-database-engine` skill in the Databasify repository covers the image, driver, scripts, tests and a live three-server run.

Read more in [Writing plugins](https://docs.databasify.io/plugins/writing-plugins/).

## License

Apache-2.0
