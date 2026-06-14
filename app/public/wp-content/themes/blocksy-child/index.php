<?php
/**
 * Template: Index fallback
 *
 * WordPress requires an index.php on every theme. We just pass through to
 * archive.php since the same feed-row layout fits any post listing.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_template_part( 'archive' );
