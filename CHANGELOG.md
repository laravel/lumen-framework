# Changelog

All Notable changes to `lumen-framework` will be documented in this file

## UNRELEASED - YYYY-MM-DD

### Added
- Nothing

### Changed
- Nothing

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing

## 5.1.2 - 2015-07-12

### Added
- Added DOCTYPE to the welcome page ([5e28e8e](https://github.com/laravel/lumen-framework/commit/5e28e8e)).
- Added missing method to `Application`class ([49930f1](https://github.com/laravel/lumen-framework/commit/49930f1)).
- **Testing**: transformed headers to server vars for the `get`, `post`, `put`, `patch`, and `delete` methods ([bdc151b](https://github.com/laravel/lumen-framework/commit/bdc151b)).

### Changed
- Convert array() to square brackets ([7bcebe6](https://github.com/laravel/lumen-framework/commit/7bcebe6), [3c2cb5b](https://github.com/laravel/lumen-framework/commit/3c2cb5b)).
- Minor typographical corrections.

### Deprecated
- Nothing

### Fixed
- Fixed([#162](https://github.com/laravel/lumen-framework/issues/162)): switched parameters order for `seeStatusCode` method ([63a37f6](https://github.com/laravel/lumen-framework/commit/63a37f6)).
- Fixed([#161](https://github.com/laravel/lumen-framework/issues/161)): added `cache.store` binding in `Application::registerCacheBindings()` to resolve `ReflectionException` ([eb9d49a](https://github.com/laravel/lumen-framework/commit/eb9d49a)).
- Fixed `CrawlerTrait::withoutMiddleware()` method call when running through middlewares ([d7a4cb5](https://github.com/laravel/lumen-framework/commit/d7a4cb5)).

### Removed
- **phpunit**: Removed unused `syntaxCheck` parameter ([c9b6bec](https://github.com/laravel/lumen-framework/commit/c9b6bec)).
- Removed `matrix` section relative to PHP 7 in `.travis.yml` file ([ae900a6](https://github.com/laravel/lumen-framework/commit/ae900a6)).

### Security
- Nothing
