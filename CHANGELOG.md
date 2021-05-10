# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v2.0.1] - 2021-05-10

### Fixed

- Fixed PDO library import

## [v2.0.0] - 2021-04-07

This version is compatible with [SimpleSAMLphp v1.17](https://simplesamlphp.org/docs/1.17/simplesamlphp-changelog)

### Changed

- Comply to [PSR-4: Autoloader](https://www.php-fig.org/psr/psr-4/) guidelines
- Comply to [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
  guidelines
- Comply to [markdownlint rules](https://github.com/DavidAnson/markdownlint/blob/main/doc/Rules.md)
- Apply modern array syntax to comply with [SimpleSAMLphp v1.17](https://simplesamlphp.org/docs/stable/simplesamlphp-upgrade-notes-1.17)

## [v1.0.0] - 2021-04-07

This version is compatible with [SimpleSAMLphp v1.14](https://simplesamlphp.org/docs/1.14/simplesamlphp-changelog)

### Added

- COmanageDbClient class
  - Express VO membership as entitlements based on [AARC-G002 guidelines](https://aarc-project.eu/guidelines/aarc-g002/)
  - Skip authproc rule for blacklisted SPs
  - Create entitlement for whitelisted VOs
