# CI/CD Guide for SATORI Forms

/* -------------------------------------------------
 * Explains the continuous integration and deployment 
 * process for this plugin.
 * -------------------------------------------------*/

- Automated linting on push.
- Automated build to package plugin zip.
- Tagging a release triggers deployment artifact build.
- Optionally deploy to WordPress.org (future).

See .github/workflows/build.yml for implementation.
