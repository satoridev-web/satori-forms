# SATORI Standards – Coding & Project Guidelines

These standards define how all SATORI projects (plugins, MU-plugins, and themes) should be structured.  
They are designed to ensure consistency, readability, and compatibility across the SATORI Suite.

---

## 1. Commenting Style

Use boxed block comments for all major sections:

```php
/* -------------------------------------------------
 * Section: Description of the section or logic
 * -------------------------------------------------*/
```

Inline explanations can use `//` for brevity:

```php
// Sanitize user input
```

---

## 2. Namespaces & Prefixes

- Each plugin uses a **namespace** with the SATORI prefix:  
  Example: `namespace Satori_Events;`
- All global functions must use a `satori_` prefix to prevent conflicts:  
  Example: `satori_events_log()`

---

## 3. File & Folder Structure

**Root Plugin File**  
- Named after the plugin slug, e.g., `satori-events.php`.

**Folders**
- `admin/` → Admin-only classes, settings pages.  
- `assets/css/` → Compiled CSS files.  
- `assets/scss/` → Modular SCSS partials.  
- `assets/js/` → JavaScript files.  
- `includes/` → Core classes (CPT, taxonomies, AJAX handlers, template functions).  
- `templates/` → Frontend templates (archives, singles, shortcodes).  
- `languages/` → Translation files (.pot, .po, .mo).  

---

## 4. File Naming Conventions

- Use lowercase, hyphen-separated names.  
  Examples:  
  - `class-satori-events-ajax-handler.php`  
  - `satori-events-template-functions.php`  
- Class files must begin with `class-`.  
- Template files begin with `satori-` and match their purpose.  

---

## 5. Versioning & Headers

Each plugin root file must include:

```php
/**
 * Plugin Name: SATORI – Events
 * Description: Custom events calendar plugin by SATORI.
 * Version: 1.0.0
 * Author: SATORI
 */
```

---

## 6. Coding Conventions

- **PHP**:  
  - Use PSR-12 formatting where possible.  
  - Functions: snake_case.  
  - Classes: PascalCase.  

- **JS**:  
  - Use ES6+ features where applicable.  
  - Variables: camelCase.  

- **CSS/SCSS**:  
  - Modular SCSS partials (`_archive.scss`, `_form.scss`, `_mixins.scss`, etc.).  
  - Compiled into a single plugin CSS file.  

---

## 7. Debugging & Logging

- Use the helper: `satori_events_log( $message )`.  
- Avoid `error_log()` directly.  
- Toggle logging via plugin settings or constants.  

---

## 8. Git & Repo Standards

- Always include:
  - `README.md` (project overview).  
  - `CHANGELOG.md` (version history).  
  - `LICENSE` (GPL-compatible).  
- Branching:
  - `main` → stable release.  
  - `dev` → active development.  

---

## 9. AI/Copilot Guidance

When generating code with GitHub Copilot:
- Follow these standards by default.  
- Always use SATORI block comments for sections.  
- Respect naming conventions (`satori_` prefix, class/file names).  

---

## 10. Branding & Capitalisation

- Always use uppercase **SATORI** in product names.  
- Examples: **SATORI Forms**, **SATORI Events**, **SATORI Audit**.  
- WordPress admin menu items must use the branded format:  
  ```php
  add_menu_page( 'SATORI Events', 'SATORI Events', ... );
  ```
- File and folder names remain lowercase, but all **UI-facing labels** must use uppercase branding.

---

*Version: 1.1.0 — SATORI Standards*