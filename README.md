# SATORI Forms

[![Build & Package](https://github.com/satoridev/satori-forms/actions/workflows/build.yml/badge.svg)](https://github.com/satoridev/satori-forms/actions)
[![License: GPL v2](https://img.shields.io/badge/License-GPLv2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-6.3%2B-brightgreen.svg)](https://wordpress.org)

---

## 📖 Overview
SATORI Forms is a lightweight and extensible **form builder plugin for WordPress**.  
It allows you to quickly create forms, display them via shortcodes or blocks, and capture submissions as entries.

Built with the [SATORI Standards Integration Guide](docs/SATORI-STANDARDS-INTEGRATION-GUIDE.md) in mind, it is designed for **simplicity, interoperability, and extensibility**.

---

## ✨ Features
- Create forms in the WP Admin UI.
- Display forms with `[satori_forms id="123"]` shortcode.
- Store submissions as entries (`satori_forms_entry`).
- Manage entries in WP Admin.
- Email notifications (planned).
- Drag-and-drop builder (roadmap).

---

## 📂 Project Structure
```
satori-forms/
├─ admin/           # Admin UI + settings
├─ assets/          # CSS, JS, SCSS
├─ includes/        # Core classes (CPTs, shortcodes, logic)
├─ templates/       # Frontend form templates
├─ scripts/         # Automation scripts
├─ docs/            # Project docs
├─ CHANGELOG.md
├─ README.md
├─ satori-forms.php
└─ .github/workflows/build.yml
```

---

## 🚀 Installation

1. Download the latest release `.zip` from [GitHub Actions → Artifacts](https://github.com/satoridev/satori-forms/actions).
2. Upload via **WordPress Admin → Plugins → Add New → Upload Plugin**.
3. Activate **SATORI Forms**.
4. Create your first form under **Forms → Add New**.
5. Embed with `[satori_forms id="123"]`.

---

## 🧩 Shortcode Usage
```php
[satori_forms id="123"]
```

Planned Gutenberg block: `satori/forms`.

---

## 📜 Documentation
- [Project Spec](docs/PROJECT_SPEC.md)  
- [CI/CD Guide](docs/CI-CD-GUIDE.md)  
- [SATORI Standards Integration Guide](docs/SATORI-STANDARDS-INTEGRATION-GUIDE.md)  

---

## 📅 Roadmap
- [ ] MVP release 1.0.0
- [ ] Drag-and-drop form builder
- [ ] Conditional logic + AJAX submissions
- [ ] REST API endpoints
- [ ] Webhook & email service integrations

---

## 🤝 Contributing
1. Fork the repo.
2. Create your feature branch:  
   ```bash
   git checkout -b feature/my-feature
   ```
3. Commit changes with clear messages.  
4. Push to your branch:  
   ```bash
   git push origin feature/my-feature
   ```
5. Open a Pull Request.

---

## 📝 License
Copyright © SATORI Graphics Pty Ltd.  
Licensed under the [GPLv2](LICENSE) or later.
