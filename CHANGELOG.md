# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

This version is compatible with [SimpleSAMLphp v1.14](https://simplesamlphp.org/docs/1.14/simplesamlphp-changelog)

### Added

- COmanageDbClient class
  - Express VO membership as entitlements based on [AARC-G002 guidelines](https://aarc-project.eu/guidelines/aarc-g002/)
  - Skip authproc rule for blacklisted SPs
  - Create entitlement for whitelisted VOs
