<?php
/**
 * Template: Search form
 *
 * Replaces WordPress's default get_search_form() output with one that
 * matches the dark editorial input styling. Used by the search widget
 * and by the Explore page header.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<form role="search" method="get" class="bp-search bp-search-panel" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <input type="search" class="bp-input" name="s" placeholder="Search essays, writers, places" value="<?php echo esc_attr( get_search_query() ); ?>">
    <button type="submit" class="bp-btn bp-btn--accent">Search</button>
</form>
