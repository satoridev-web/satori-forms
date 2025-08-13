<?php

/**
 * This file is part of the SATORI Forms plugin.
 *
 * SATORI Forms is free software...
 * (long GPL header omitted for brevity; include if you prefer)
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

/* -------------------------------------------------
 * Remove plugin options (keep data conservative)
 * -------------------------------------------------*/
delete_option('satori_forms_debug_enabled');
