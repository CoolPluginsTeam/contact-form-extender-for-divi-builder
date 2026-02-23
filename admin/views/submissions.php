<?php
/**
 * Submissions view
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use CFEFD\Admin\Entries\CFEFD_Submissions_Post_Type;

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$submissions = CFEFD_Submissions_Post_Type::get_instance();
$submissions->output_entries_list();
