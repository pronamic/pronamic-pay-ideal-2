# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.1] - 2024-09-09

### Fixed

- Fixed caching access token uniquely across modes. ([#10](https://github.com/pronamic/pronamic-pay-ideal-2/issues/10))
- Fixed using expired access token. ([#9](https://github.com/pronamic/pronamic-pay-ideal-2/issues/9))
- Fixed issue with `%` and `<` characters in private key passwords. ([#6](https://github.com/pronamic/pronamic-pay-ideal-2/issues/6))

### Composer

- Added `pronamic/wp-http-extended-ssl-support` `^1.0.0`.
- Changed `automattic/jetpack-autoloader` from `v3.0.8` to `v3.1.0`.
	Release notes: https://github.com/Automattic/jetpack-autoloader/releases/tag/v3.1.0
- Changed `wp-pay/core` from `v4.19.0` to `v4.22.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.22.0

Full set of changes: [`1.1.0...1.1.1`][1.1.1]

[1.1.1]: https://github.com/pronamic/pronamic-pay-ideal-2/compare/v1.1.0...v1.1.1

## [1.1.0] - 2024-06-07

### Added

- Added support for iDEAL issuers.

### Composer

- Added `pronamic/ideal-issuers` `^1.1`.
- Changed `php` from `>=8.0` to `>=8.1`.
- Changed `automattic/jetpack-autoloader` from `v3.0.7` to `v3.0.8`.
	Release notes: https://github.com/Automattic/jetpack-autoloader/releases/tag/v3.0.8
- Changed `wp-pay/core` from `v4.17.0` to `v4.19.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.19.0

Full set of changes: [`1.0.0...1.1.0`][1.1.0]

[1.1.0]: https://github.com/pronamic/pronamic-pay-ideal-2/compare/v1.0.0...v1.1.0

## [1.0.0] - 2024-03-26

- First relase.

[1.0.0]: https://github.com/pronamic/pronamic-pay-worldline-open-banking-ideal-2/releases/tag/v1.0.0
