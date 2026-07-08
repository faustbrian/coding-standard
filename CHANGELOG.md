# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Added `Cline\CodingStandard\EasyCodingStandard\Factory::configure()`
  as the preferred fluent ECS config entrypoint while keeping
  `Factory::create()` for backward compatibility.
- Added repository-level maintainer guidance in `AGENTS.md`.
- Added Rector's pipe operator migration rules for nested function
  calls and sequential assignment chains.
- Added `Architecture/phpdoc_line_length_fixer` to wrap long PHPDoc prose
  lines at 80 columns.
- Added `Architecture/remove_author_tag_fixer` to strip `@author`
  annotations from PHPDoc blocks.
- Added `Architecture/remove_header_comment_fixer` to strip leading
  file header comments.
- Added `Architecture/remove_version_tag_fixer` to strip `@version`
  annotations from PHPDoc blocks.
- Reflowed the `ImportFqcnInNewFixer` docblock to keep prose wrapped and
  annotation lines separate.
- Initial release

### Changed
- Switched the Standard preset to enable
  `nullable_type_declaration_for_default_null_value` without the
  deprecated `use_nullable_type_declaration` option so ECS remains
  compatible with current PHP-CS-Fixer releases.
- Switched the Standard preset to remove `@author` tags by default
  instead of adding them.
- Switched the Standard preset to remove file header comments by
  default instead of adding them.
- Switched the Standard preset to remove `@version` tags by default
  instead of adding them.
