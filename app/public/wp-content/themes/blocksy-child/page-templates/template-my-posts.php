<?php
/**
 * Template Name: My Stories (legacy)
 *
 * Alias kept so that any page already assigned to "My Stories" doesn't
 * 404. Forwards each render to the new "Your desk" template.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

include locate_template( 'page-templates/template-my-desk.php' );
