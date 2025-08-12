# SATORI Forms – Initial Commit Checklist

## 1. Folder & File Structure
- [ ] Plugin folder name: `satori-forms` (no spaces, lowercase, hyphen-separated).
- [ ] Main plugin file: `satori-forms.php` with correct plugin header.
- [ ] Standardized folder structure:
  ```
  admin/
  assets/
    css/
    js/
    scss/
  includes/
  languages/
  templates/
  .vscode/
  ```
- [ ] Remove any unused example files or temp code.

## 2. Code Formatting & Standards
- [ ] Use **PSR-12** / WordPress PHP coding standards.
- [ ] Ensure files have `<?php` opening tag only (no closing `?>` in pure PHP files).
- [ ] All function/class names prefixed with `Satori_Forms_` or under a namespace.
- [ ] No debug `echo`, `var_dump`, or test code in production files.

## 3. Asset Compilation
- [ ] SCSS compiled into `/assets/css/` with `.map` files ignored in `.gitignore`.
- [ ] JavaScript minified if needed for production.
- [ ] No `.sass-cache/` or `node_modules/` committed.

## 4. Configuration Files
- [ ] `.gitignore` is present and ignores:
  - OS junk files (`.DS_Store`, `Thumbs.db`)
  - Build artifacts (`node_modules/`, `vendor/`)
  - Sass cache
  - Local WP environment files
- [ ] `README.md` includes:
  - Plugin name & description
  - Installation instructions
  - Basic usage
  - Changelog placeholder
- [ ] `LICENSE` file present and correct.
- [ ] Optional: `.editorconfig` for consistent formatting.

## 5. Testing Before Commit
- [ ] Plugin activates in WordPress without errors.
- [ ] No PHP warnings/notices in debug log.
- [ ] Works with WP_DEBUG enabled.
- [ ] Admin page(s) load without fatal errors.

## 6. Commit & Push
- [ ] Stage all required files:
  ```bash
  git add .
  ```
- [ ] Commit with clear message:
  ```bash
  git commit -m "Initial commit – SATORI Forms plugin base structure"
  ```
- [ ] Push to main branch:
  ```bash
  git push -u origin main
  ```
