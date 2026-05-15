# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**Drafteam** is a League of Legends draft tool built on the Symfony 8 / PHP 8.5 skeleton, served by FrankenPHP and backed by PostgreSQL. User-facing strings are in French.

## Everything runs through Docker

There is no host PHP toolchain expected. Every command goes through `docker compose exec php …`, wrapped by the `Makefile`:

- `make start` — build images and start the stack (FrankenPHP at `:80`/`:443`, Postgres on a random host port).
- `make up` / `make down` / `make logs` / `make sh` (or `make bash`) — lifecycle and shell into the `php` container.
- `make sf c="<cmd>"` — run `bin/console <cmd>` (e.g. `c=debug:router`, `c="doctrine:migrations:migrate"`).
- `make composer c="<args>"` — run composer inside the container.
- `make cc` — clear cache.
- `make test c="<phpunit args>"` — run PHPUnit with `APP_ENV=test` (e.g. `c="--filter SomeTest"` for a single test). Note: `bin/phpunit` and a `tests/` directory do not exist yet — PHPUnit is not currently installed.

The `php` service auto-runs `doctrine:migrations:migrate` on boot via `frankenphp/docker-entrypoint.sh`, so a fresh `make start` lands on a migrated schema. Dev mode has hot reload (`FRANKENPHP_SITE_CONFIG=hot_reload`) and a worker watcher.

## Linting / static analysis: Mago

`mago.toml` configures both the formatter and the analyzer. Run via `make sh` then `vendor/bin/mago …` (Mago is not in `composer.json`; it is expected as a separate tool — invoke it however it's installed in the dev container). Relevant settings to be aware of:

- `linter.integrations = ["symfony"]` — Symfony-specific rules are on.
- Analyzer is strict: `find-unused-definitions`, `find-unused-expressions`, `find-unused-parameters`, `check-throws`, `check-missing-override`, `strict-list-index-checks`, `memoize-properties`, `register-super-globals`. Don't leave unused parameters or untyped/unchecked throws — Mago will flag them.
- `unchecked-exceptions = ["Error", "LogicException"]` — only these may be thrown without being declared.
- Format: 120-column print width, 4-space indent.

## Architecture

The application has two functional layers today:

### 1. League of Legends data ingestion (`src/Bridge/LeagueOfLegends/DataDragon/`)

`DataDragonService` is a thin HTTP client over Riot's public DDragon CDN, wired with a **scoped HTTP client** named `datadragon.client` (see `config/packages/http_client.yaml`). Inject it with `#[Target('datadragon.client')]` — do not use the generic `HttpClientInterface` for DDragon calls. Responses are deserialized into immutable DTOs under `Dto/Output/` (e.g. `ChampionOutput`, `ImageOutput`), using `UnwrappingDenormalizer::UNWRAP_PATH` to peel the `data` wrapper out of DDragon's response shape. Failures are wrapped in `DataDragonRequestException`.

The `app:champions:update` console command (`src/Command/UpdateChampionsCommand.php`) is the entry point that consumes this bridge. It:

1. Resolves the latest game version via `CacheInterface` (TTL 86400s, key `league_of_legends.data_dragon.last_version`).
2. Iterates all champions for a `--lang` (default `en_US`), and **upserts in three branches**: create Champion + ChampionData, or add a new ChampionData for an existing Champion, or update the existing ChampionData. Re-run the command per language to populate translations.

The command is a `final readonly` invokable (`__invoke`), not a class extending `Command` — this is the Symfony 7.1+ style and the pattern to follow for new commands.

### 2. Domain entities (`src/Entity/`)

Doctrine 3 with attribute mappings, **constructor property promotion** for all columns, and `naming_strategy: underscore` (so `imageFull` → column `image_full`).

- `Champion` — Riot's canonical champion (one row per champion, identified by `lolId` + `lolKey`). Stores sprite-sheet coordinates (`imageX`, `imageY`, `imageWidth`, `imageHeight`) for client-side rendering.
- `ChampionData` — per-language localized name/title for a `Champion`. **Composite primary key** of `(champion, language)` via two `#[ORM\Id]` attributes. When adding new localized fields, they belong here, not on `Champion`.

The PostgreSQL identity-generation preference is set explicitly (`identity_generation_preferences`), so new entities with `#[ORM\GeneratedValue]` get IDENTITY columns, not sequences.

### 3. Controllers and templates

Controllers (`src/Controller/`) use attribute routes and `AbstractController`. Templates live under `templates/` with one extra wrinkle: there is a `Site/` subdirectory grouping route-area templates (e.g. `Site/draft/create.html.twig`), while top-level shells like `base.html.twig` and `index.html.twig` sit at the root. `twig.yaml` sets `file_name_pattern: '*.twig'`, so any `.twig` file (not just `.html.twig`) is treated as a template.

CSRF is **stateless** with token ids `submit` (the default for forms), `authenticate`, `logout` — see `config/packages/csrf.yaml`. Use those ids rather than introducing new ones unless there's a reason.

## Things to know

- PHP requirement is `>=8.5.6` and Mago is configured for `8.5.6`. Don't lower the floor.
- `composer.json` pins Symfony components to `8.0.*` and `replace`s every Symfony polyfill — don't add polyfills as deps.
- `compose.yaml` declares Mercure JWT env vars and a `MERCURE_URL`, but no Mercure bundle is currently installed (the recipe block in `bundles.php` / packages is empty). If you add real-time features, install `symfony/mercure-bundle` rather than rolling your own.
- A `var/share` directory is referenced via `APP_SHARE_DIR` in `.env` — check what writes there before assuming it's empty.
