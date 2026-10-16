# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the package uses
[Semantic Versioning](https://semver.org/). Changes to the `audit_record` schema are
called out under **Schema**: each one needs a migration in the consuming application.

## [Unreleased]

### Added

- An error is logged on `kernel.terminate` and `console.terminate` when audit
  records were recorded but never flushed, so a record made after the last flush
  no longer disappears without a trace ([ADR-0010](docs/adr/0010-records-commit-with-the-callers-flush.md)).
- `AuditEntry::fromRecord()` builds a detached entry from any `AuditRecord`.

### Changed

- `RecordReader::actors()` labels each actor by their most recent record instead
  of the lexicographically greatest label.
- `NullSubjectLabeller` moved from `Infrastructure\Symfony` to `Recording`. Only
  code that references the class itself is affected; the `SubjectLabeller` alias
  is unchanged.

### Fixed

- `FieldChange`, `RenderLine` parameters and event `data` now reject values JSON
  cannot encode (`NAN`, `INF`, invalid UTF-8) when the event is built or recorded,
  instead of failing the flush and rolling back the audited change.

### Schema

- Added indexes on `channel`, `actor_label` and `subject_label`, the columns the
  reader filters and sorts on.

## [6.0.0-rc.3] - 2026-10-15

### Added

- `AuditQuery::$order` orders the trail by occurrence, code, channel, actor or
  subject, ascending or descending; every order ends on the record id so pages
  neither repeat nor skip rows.

## [6.0.0-rc.2] - 2026-06-02

### Fixed

- Symfony 8.1 is allowed: constraints widened to `^8.0`.

## [6.0.0-rc.1] - 2026-05-31

### Added

- First release candidate: the framework-free core (`AuditEvent`,
  `AbstractAuditEvent`, `Recorder`, `RecordReader`), Doctrine persistence
  (`AuditRecordEntity`, DBAL types, store and reader) and Symfony runtime adapters,
  pre-wired by `AuditLogBundle`.

[Unreleased]: https://github.com/wedevelopnl/audit-log/compare/6.0.0-rc.3...HEAD
[6.0.0-rc.3]: https://github.com/wedevelopnl/audit-log/compare/6.0.0-rc.2...6.0.0-rc.3
[6.0.0-rc.2]: https://github.com/wedevelopnl/audit-log/compare/6.0.0-rc.1...6.0.0-rc.2
[6.0.0-rc.1]: https://github.com/wedevelopnl/audit-log/releases/tag/6.0.0-rc.1
