# 🚀 SATORI Plugin Release Checklist

/* -------------------------------------------------
 * SATORI Standard — Automated Plugin Workflow
 * This document defines the release process for all
 * SATORI plugins (Forms, Events, Audit, Search, etc.)
 * -------------------------------------------------*/

## 1. Pre-Release Prep
- [ ] Confirm **all commits are pushed** to `main` (or release branch).
- [ ] Run `git status` → should show *nothing to commit*.
- [ ] Update **plugin version header** in `plugin-name.php` (e.g. `Version: 1.0.1`).
- [ ] Update **CHANGELOG.md** with release notes.
- [ ] Update **README.md** if features or usage changed.

---

## 2. Tagging the Release
- [ ] From terminal:
  ```bash
  git tag -a vX.Y.Z -m "Release vX.Y.Z"
  git push origin vX.Y.Z
  ```
- [ ] Verify tag appears under **Releases → Tags** on GitHub.

---

## 3. GitHub Release (Manual / Automated)
- If **manual**:
  - Go to GitHub → Releases → “Draft new release”.
  - Select the tag you just pushed (`vX.Y.Z`).
  - Paste release notes (from CHANGELOG).
  - Attach built **plugin zip** (if not automated yet).

- If **automated**:
  - Tag push triggers GitHub Actions.
  - Actions workflow runs:
    - Lints code.
    - Builds plugin zip.
    - Publishes release with attached artifact.

---

## 4. Post-Release
- [ ] Test install the new release on a local/staging WP site.
- [ ] Verify version number matches in WP Admin → Plugins.
- [ ] Verify docs (`docs/`) and guides are still up to date.
- [ ] Announce or log release (internal/external).
