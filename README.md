# Example Database Plugin

An example [Databasify](https://github.com/databasifylabs/databasify) plugin that adds an engine called **Example Cache** (`example-cache`, category Caching).

It shows the smallest useful engine plugin. Databasify runs every engine through the `databasify` command inside the engine's container image, so a plugin engine is a registration plus a published engine image. This one registers the official Valkey image, `ghcr.io/databasifylabs/databasify-valkey`, under its own id, name and version lines. It has no PHP driver and no server scripts.

## Try it

1. Download this repository as a zip (or point Databasify at `databasifylabs/example-database-plugin`).
2. In Databasify, open **Plugins** and install it from the repository or upload the zip.
3. Create a database and pick **Example Cache**. It offers lines 8 and 9, and runs and connects like Valkey.

## File layout

```
composer.json                      name, PSR-4 autoload and extra.databasify (plugin class, requires, icon)
src/ExampleDatabasePlugin.php      registers the engine in boot()
public/icon.svg                    plugin icon
public/icons/database-providers/example-cache.svg
                                   engine icon
```

## What the registration sets, and why

- `image('ghcr.io/databasifylabs/databasify-valkey')`: without it the image would be `databasify-example-cache`, which does not exist. Databasify picks the newest `<line>.x` tag of the database's line and pins its digest.
- `versions('9', [...])`: the lines offered on the create page, each one a tag prefix in the image.
- `connection(...)`: how the connection details page builds the URI, the CLI command and the Laravel `.env` keys. The second profile is used once a domain with a public certificate is attached.
- `migrationSource(6379, ...)`: the default port and the fields asked for when migrating into this engine.
- `readOnlyNodesVote()`, `persistenceChoice()`, `publicCertificate()`, `failoverHint()`: the same behaviour as the built-in Valkey engine.

The server side follows the image, not the plugin id. The image's `io.databasify.engine` label (`valkey`) names the container (`databasify-valkey`) and its data folder (`/srv/valkey`).

## Start your own engine plugin

- **Your own engine:** engine logic is Go code in the shared `databasify` wrapper, so a new engine is a pull request to [`databasifylabs/engines`](https://github.com/databasifylabs/engines): a package under `internal/engines/<name>` implementing `contracts.Engine`, and an image under `images/<name>` that passes the smoke test and its conformance suite. Databasify installs the same pinned wrapper on every node, so an engine kept only in a fork loses its code at the next wrapper update. Once its image is published with exact `<line>.x` tags, register it from your plugin.
- **Your registration:** copy `src/ExampleDatabasePlugin.php`, set your id, label, category, image and lines, and describe how clients connect with `connection()`.

Read more in [Writing plugins](https://docs.databasify.io/plugins/writing-plugins/).

## License

Apache-2.0
