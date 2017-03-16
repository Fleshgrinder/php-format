# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this
 project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased][unreleased]

## [1.1.0] - 2017-03-16
### Added
- `#c` string format modifier to transform ASCII control characters to their
  [caret notation](https://en.wikipedia.org/wiki/Caret_notation) in the
  formatted string.
- `#p` string format modifier to transform ASCII control characters to their
  printable Unicode pendant in the formatted string.
- `strpos` check for brackets to avoid the heavy recursive regular expression
  for optional parts if it is definitely not needed.
### Changed
- Empty strings are now formatted as `empty {:?}` in the formatted string
  instead of resulting in nothing.

## 1.0.0 - 2017-03-12
- Initial Release

[unreleased]: https://github.com/Fleshgrinder/php-pattern-formatter/compare/1.1.0...HEAD
[1.1.0]: https://github.com/Fleshgrinder/php-pattern-formatter/compare/1.0.0...1.1.0
