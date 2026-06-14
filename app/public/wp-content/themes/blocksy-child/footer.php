<?php
/**
 * Site footer.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$bp_hide_chrome = defined( 'BP_HIDE_FOOTER_CHROME' ) && BP_HIDE_FOOTER_CHROME;
?>

<?php if ( ! $bp_hide_chrome ) : ?>
<footer class="bp-footer">
    <div class="bp-footer__inner">
        <div class="bp-footer__brand-block">
            <a class="bp-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img src="<?php echo esc_url( function_exists( 'bp_brand_logo_url' ) ? bp_brand_logo_url() : BP_CHILD_URI . '/assets/images/aperture-logo-full.png' ); ?>" alt="" width="150" height="51">
                <span class="screen-reader-text"><?php echo esc_html( bp_brand_name() ); ?></span>
            </a>
            <p>A quiet place for considered ideas about design, technology, and intelligence.</p>
        </div>

        <nav class="bp-footer__column" aria-label="<?php esc_attr_e( 'Read', 'blocksy-child' ); ?>">
            <h2>Read</h2>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
            <a href="<?php echo esc_url( home_url( '/explore/' ) ); ?>">Explore</a>
            <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
        </nav>

        <nav class="bp-footer__column" aria-label="<?php esc_attr_e( 'Participate', 'blocksy-child' ); ?>">
            <h2>Participate</h2>
            <a href="<?php echo esc_url( home_url( '/write/' ) ); ?>">Contribute</a>
            <a href="<?php echo esc_url( home_url( '/my-desk/' ) ); ?>">My Content</a>
            <a href="<?php echo esc_url( home_url( '/categories/' ) ); ?>">Suggest a category</a>
        </nav>

        <nav class="bp-footer__column" aria-label="<?php esc_attr_e( 'Account', 'blocksy-child' ); ?>">
            <h2>Account</h2>
            <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Sign in</a>
            <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create account</a>
            <a href="<?php echo esc_url( home_url( '/about/#contact' ) ); ?>">Contact</a>
        </nav>
    </div>

    <div class="bp-footer__bottom">
        <span>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( bp_brand_name() ); ?>. All rights reserved.</span>
        <span>Designed with intention.</span>
    </div>
</footer>
<?php endif; ?>

<?php if ( ! is_user_logged_in() ) : ?>
<div class="bp-modal" id="bp-signin-modal" role="dialog" aria-modal="true" aria-labelledby="bp-signin-title">
    <div class="bp-modal__overlay" data-signin-close></div>
    <div class="bp-modal__card">
        <button type="button" class="bp-modal__close" data-signin-close aria-label="<?php esc_attr_e( 'Close', 'blocksy-child' ); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
        <div class="bp-modal__body">
            <h2 id="bp-signin-title">Sign in to write</h2>
            <p>Create a free contributor account, submit your draft, and follow it from your desk.</p>
            <div class="bp-modal__actions">
                <a class="bp-btn bp-btn--dark" href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create account</a>
                <a class="bp-btn bp-btn--ghost" href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Sign in</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
