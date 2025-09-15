# SATORI Integration Guide

This document explains how SATORI plugins integrate with each other and WordPress.

---

## 1. Core Integration
- All plugins must check `defined( 'ABSPATH' )` to prevent direct access.
- Namespaces avoid conflicts.
- Shared utilities live in `satori-core` (future).

---

## 2. Plugin Communication
- Plugins can hook into each other via actions & filters.
- Example: **SATORI Forms** can submit data into **SATORI Events**.

---

## 3. Branding Consistency
- All admin menus show `SATORI` uppercase.
- Each plugin must declare itself under the **SATORI Suite** umbrella.

---

## 4. Future-Proofing
- Modular file structures.
- Optional add-ons load conditionally.
- Centralised logging and debugging.

---

*Version: 1.0.0*
