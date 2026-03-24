# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.3.0] - 2026-03-24

### Added

- Added Dutch (nl_NL) translations.

### Changed

- PHP 8.2 is now required.
- Modernized code with PHP 8.x syntax: `readonly` class properties, `Stringable` interface, first-class callables.
- Added `declare(strict_types=1)` to all source files.
- Guarded vendor autoload inclusion with `file_exists` check.
- Added namespace declaration to the main plugin file.
- Moved source files from `src/` to `psr-4/` directory.
- Added Rector for automated code quality analysis.
- Revamped build and i18n composer scripts.

### Composer

- Changed `firebase/php-jwt` from `^6.10` to `^7.0`.
- Changed `wp-pay/core` from `^4.26` to `^4.28`.
- Changed `automattic/jetpack-autoloader` from `v5.0.7` to `v5.0.16`.
	Release notes: https://github.com/Automattic/jetpack-autoloader/releases/tag/v5.0.16
- Changed `firebase/php-jwt` from `v6.11.1` to `v7.0.3`.
	Release notes: https://github.com/firebase/php-jwt/releases/tag/v7.0.3
- Changed `pronamic/wp-datetime` from `v2.1.8` to `v2.2.0`.
	Release notes: https://github.com/pronamic/wp-datetime/releases/tag/v2.2.0
- Changed `pronamic/wp-number` from `v1.3.3` to `v1.4.1`.
	Release notes: https://github.com/pronamic/wp-number/releases/tag/v1.4.1
- Changed `pronamic/wp-pay-logos` from `v2.2.3` to `v2.3.2`.
	Release notes: https://github.com/pronamic/wp-pay-logos/releases/tag/v2.3.2
- Changed `woocommerce/action-scheduler` from `3.9.2` to `3.9.3`.
	Release notes: https://github.com/woocommerce/action-scheduler/releases/tag/3.9.3
- Changed `wp-pay/core` from `v4.26.0` to `v4.32.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.32.0

Full set of changes: [`1.2.1...1.3.0`][1.3.0]

[1.3.0]: https://github.com/pronamic/pronamic-pay-ideal-2/compare/v1.2.1...v1.3.0

## [1.2.1] - 2025-06-19

### Commits

- Also allow Jetpack autoloader 4 and 5. ([8116e21](https://github.com/pronamic/pronamic-pay-ideal-2/commit/8116e21bcc64900ea3f52b1db8721181fe6fb3f4))

### Composer

- Changed `automattic/jetpack-autoloader` from `v3.1.3` to `v5.0.7`.
	Release notes: https://github.com/Automattic/jetpack-autoloader/releases/tag/v5.0.7
- Changed `pronamic/pronamic-wp-updater` from `v1.0.2` to `v1.0.3`.
	Release notes: https://github.com/pronamic/pronamic-wp-updater/releases/tag/v1.0.3

Full set of changes: [`1.2.0...1.2.1`][1.2.1]

[1.2.1]: https://github.com/pronamic/pronamic-pay-ideal-2/compare/v1.2.0...v1.2.1

## [1.2.0] - 2025-06-19

### Commits

- Merge pull request #12 from pronamic/remove-ideal-issuers-field ([3742835](https://github.com/pronamic/pronamic-pay-ideal-2/commit/3742835ef1aee32ae935df2d53e9d34f3d0e5487))
- Removed iDEAL issuers field. ([0b72c49](https://github.com/pronamic/pronamic-pay-ideal-2/commit/0b72c49d1032e7c3932f140113b631220f29128c))

### Composer

- Removed `pronamic/ideal-issuers` `^1.1`.
- Changed `automattic/jetpack-autoloader` from `v3.1.0` to `v3.1.3`.
	Release notes: https://github.com/Automattic/jetpack-autoloader/releases/tag/v3.1.3
- Changed `firebase/php-jwt` from `v6.10.1` to `v6.11.1`.
	Release notes: https://github.com/firebase/php-jwt/releases/tag/v6.11.1
- Changed `pronamic/wp-http` from `v1.2.3` to `v1.2.4`.
	Release notes: https://github.com/pronamic/wp-http/releases/tag/v1.2.4
- Changed `wp-pay/core` from `v4.22.0` to `v4.26.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.26.0

Full set of changes: [`1.1.1...1.2.0`][1.2.0]

[1.2.0]: https://github.com/pronamic/pronamic-pay-ideal-2/compare/v1.1.1...v1.2.0

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
