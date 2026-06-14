<?php
/**
 * Template Name: Submit a Story (legacy)
 *
 * Alias kept so that pages already assigned to this template don't 404.
 * Forwards every render to the new template-write.php editor.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

include locate_template( 'page-templates/template-write.php' );
