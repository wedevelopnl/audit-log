# igor baseline

`igor-baseline.json` lists the findings `composer igor` reports and this package accepts as safe under FrankenPHP worker mode. This file says why. Each entry below names what the flagged code does and the condition under which the suppression must be dropped.

## Maintaining it

- The `igor-php/igor-php` wrapper downloads the latest scanner on every run, so a new igor release can add or reword findings with no change here. A new finding fails CI. Fix it if it is in `src/`; otherwise argue it below before adding it to the baseline. Never accept a regenerated baseline wholesale.
- Regenerate, then normalise (igor emits entries in random order, which buries real additions in churn):

  ```sh
  vendor/bin/igor-php --no-agent -generate-baseline -baseline igor-baseline.json .
  jq --indent 2 '{files: (.files | to_entries | sort_by(.key) | map({key, value: (.value | map({message}) | sort_by(.message))}) | from_entries)}' igor-baseline.json > igor-baseline.tmp && mv igor-baseline.tmp igor-baseline.json
  ```

- `config.autoloader-suffix` in `composer.json` exists for this baseline. igor matches entries by file and exact message, and the `autoload_real.php` finding names the generated class `ComposerAutoloaderInit<suffix>`. Without a fixed suffix, Composer derives it from the lock's content hash, and CI resolves a fresh lock on every run. That entry would stop matching and fail the build.
- Both baseline files are `export-ignore`d (`.gitattributes`). igor finds any `igor-baseline.json` shipped inside a vendor package and merges it into the consuming application's audit (verified with `debug-external-baseline`). Shipping this one would silently suppress findings in every application that installs the package.
- `composer igor` does not pass `-check-baseline`. In igor 0.10.1 that flag replaces the audit verdict instead of adding to it: with a finding missing from the baseline it still exits 0 (verified). It would need a second invocation. Run it by hand when pruning: `vendor/bin/igor-php --no-agent -baseline igor-baseline.json -check-baseline .`

## Project code

- **`src/Recording/DefaultRecorder.php:34`**: "Mutation detected on an injected dependency (`$this->store`)". igor matches the call shape `$this->dep->add(...)`. It has no proof that state is retained. `RecordStore::add()` hands the new record to a persistence collaborator. Its only implementation, `DoctrineRecordStore::add()` (`src/Infrastructure/Doctrine/DoctrineRecordStore.php:24`), is a single `EntityManager::persist()` with no flush. The record joins the host application's unit of work, which the host's `kernel.reset` chain (Doctrine's `Registry`) clears between requests. Both classes are `final readonly` and hold only their collaborators, so the recorder keeps no per-request state. **Drop it** if a `RecordStore` implementation starts buffering records on itself instead of passing them straight on.

## Composer autoloader (`vendor/autoload.php`, `vendor/composer/*`)

- **`vendor/autoload.php:7`, `vendor/composer/platform_check.php:13`**: `header('HTTP/1.1 500 …')`. Both calls are guarded by a platform check that fails only on an unsupported PHP version (`< 5.6`, `< 8.5`). The check runs once when the worker boots, before any request is served, and a failure aborts the process. **Drop it** if a call moves outside the platform guard.
- **`vendor/composer/autoload_real.php:28`**: `static::$loader` in `getLoader()`. This is the process-wide autoloader singleton, created once at bootstrap. It holds class paths, not request data.
- **`vendor/composer/ClassLoader.php:164-367`** (`addClassMap`, `add`, `addPsr4`, `set`, `setPsr4`, `setUseIncludePath`, `setClassMapAuthoritative`, `setApcuPrefix`): this is the autoloader's own configuration, written while `autoload_real.php` registers it at bootstrap. Nothing in `src/` calls these methods.
- **`vendor/composer/ClassLoader.php:396-399, 575`**: `static::$registeredLoaders` in `register()` and `static::$includeFile` in `initializeIncludeClosure()`. Both are one-time bootstrap registration.
- **`vendor/composer/ClassLoader.php:471`**: `missingClasses` in `findFile()`. This is a negative cache of class names that do not exist on disk. It is the same answer for every request and is keyed on class names, not request input.
- **`vendor/composer/InstalledVersions.php:281-386`** (`getRawData`, `reload`, `getSelfDir`, `getInstalled`): these are lazy, idempotent caches of the generated `installed.php` package metadata. They are identical on every request and never request-derived. **Drop it** if anything in `src/` calls `InstalledVersions::reload()` with request data.

## psr/log

- **`vendor/psr/log/src/LoggerAwareTrait.php:20`**: `logger` in `setLogger()`. The container calls it during service construction for `LoggerAwareInterface` services (in production, `symfony/cache` adapters), so it runs once per worker. It injects the same logger every time and nothing in `src/` calls it. **Drop it** if `src/` calls `setLogger()` at runtime.

## symfony/polyfill-php84

- **`vendor/symfony/polyfill-php84/Resources/stubs/ReflectionConstant.php:37, 96`**: `set_error_handler` and `static::$persistentConstants`. The whole stub sits inside `if (\PHP_VERSION_ID < 80400)`, and this package requires PHP `>=8.5`. The native class wins, so the stub never defines anything. **Drop it** if the PHP floor goes below 8.4.

## symfony/var-dumper

- **`vendor/symfony/var-dumper/Resources/functions/dump.php:49, 55, 66`**: `header()` and four `exit(1)` findings (two lines, each reported twice). All of them are inside `dd()`, the "dump and die" debug helper, which ends the process by design. Nothing in `src/` calls `dd()`, and leaving a `dd()` call in production code is a bug in its own right. **Drop it** if `dd()` becomes reachable from `src/`.
