<?php
/**
 * 404 template.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>

<main class="apple-404">
    <div class="bp-shell">
        <section class="apple-404__inner reveal">
            <span class="bp-eyebrow">Not found</span>
            <h1>The page cannot be found.</h1>
            <p>It may have moved, or the link may be out of date.</p>
            <div class="apple-404__actions">
                <a class="bp-btn bp-btn--accent bp-btn--lg" href="<?php echo esc_url( home_url( '/' ) ); ?>">Go home</a>
                <a class="bp-btn bp-btn--ghost bp-btn--lg" href="<?php echo esc_url( home_url( '/explore/' ) ); ?>">Explore</a>
            </div>
        </section>
    </div>
</main>

<?php get_footer(); ?>
