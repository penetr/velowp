# VeloWP

WordPress plugin for secure WebP derivative generation and fail-safe delivery.

## Current status

This repository contains an implementation scaffold aligned with the VeloWP technical specification:

- OOP architecture with namespaced domains (`Core`, `Admin`, `Queue`, `Converter`, `Storage`, `Delivery`, `Diagnostics`, `Lifecycle`).
- Activation checks for platform requirements and WebP capabilities.
- Database-backed queue and generated files registry tables.
- Background worker with lock + stale processing reset + retry behavior.
- WebP conversion pipeline with temp file and atomic publish.
- Delivery methods: Apache marker rules and PHP Safe filter mode.
- Admin screen for environment diagnostics, settings, queue controls, and logs.
- Uninstall/deactivate cleanup policies.

## Plugin layout

- `velowp.php` — bootstrap and hooks.
- `uninstall.php` — uninstall entrypoint.
- `src/` — plugin classes by domain.
- `templates/` — admin rendering templates.
- `assets/` — admin CSS/JS.

## Notes

This is an initial implementation baseline and should be extended with deeper test coverage, AJAX folder tree UI, dry-run and full cleanup workflows, and full WPCS hardening.
