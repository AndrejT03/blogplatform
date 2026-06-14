<?php
/**
 * Template Name: Sign in
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$requested_redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : '';
$requested_redirect_to = $requested_redirect_to ? wp_validate_redirect( $requested_redirect_to, '' ) : '';

$bp_login_default_redirect = function ( $user = null ) {
    if ( $user instanceof WP_User ) {
        return user_can( $user, 'manage_options' ) ? admin_url() : home_url( '/my-desk/' );
    }

    return current_user_can( 'manage_options' ) ? admin_url() : home_url( '/my-desk/' );
};

if ( is_user_logged_in() ) {
    $redirect_to = $requested_redirect_to ?: $bp_login_default_redirect();
    wp_safe_redirect( $redirect_to );
    exit;
}

$login_error = '';
if ( isset( $_POST['bp_login_nonce'] ) && wp_verify_nonce( $_POST['bp_login_nonce'], 'bp_login' ) ) {
    $creds = [
        'user_login'    => sanitize_text_field( wp_unslash( $_POST['log'] ?? '' ) ),
        'user_password' => isset( $_POST['pwd'] ) ? (string) $_POST['pwd'] : '',
        'remember'      => true,
    ];

    $user = wp_signon( $creds, is_ssl() );
    if ( is_wp_error( $user ) ) {
        $login_error = wp_strip_all_tags( $user->get_error_message() );
    } else {
        wp_set_current_user( $user->ID );
        $redirect_to = $requested_redirect_to ?: $bp_login_default_redirect( $user );
        wp_safe_redirect( $redirect_to );
        exit;
    }
}

get_header();
?>

<main class="ap-auth">
    <section class="ap-auth__scene">
        <div class="ap-auth__glow" aria-hidden="true"></div>
        <div class="bp-shell ap-auth__grid">
            <aside class="ap-auth__copy reveal">
                <span class="ap-eyebrow">Welcome back</span>
                <h1>Pi<span class="ap-gradient-text">ck</span> up<br> where you left.</h1>
                <p>Sign in to publish essays, leave comments, and track everything you contribute.</p>
                <ul>
                    <li><strong>Editor-reviewed</strong><span>Every contribution is read by a human.</span></li>
                    <li><strong>Track everything</strong><span>See what's pending, published, or declined.</span></li>
                    <li><strong>No noise</strong><span>No ads, no metrics, no infinite feeds.</span></li>
                </ul>
            </aside>

            <form class="ap-auth-card reveal" method="post" action="" autocomplete="on">
                <?php wp_nonce_field( 'bp_login', 'bp_login_nonce' ); ?>
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $requested_redirect_to ); ?>">

                <a class="ap-auth-card__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <img src="<?php echo esc_url( function_exists( 'bp_brand_logo_url' ) ? bp_brand_logo_url() : BP_CHILD_URI . '/assets/images/aperture-logo-full.png' ); ?>" alt="" width="150" height="51">
                    <span class="screen-reader-text"><?php echo esc_html( bp_brand_name() ); ?></span>
                </a>

                <h2>Sign in</h2>
                <p>Sign in to continue to your workspace.</p>

                <?php if ( $login_error ) : ?>
                    <div class="bp-form-error"><?php echo esc_html( $login_error ); ?></div>
                <?php endif; ?>

                <div class="ap-field ap-field--full">
                    <label for="log">Email</label>
                    <input type="text" id="log" name="log" class="bp-input" required autocomplete="username" placeholder="you@domain.com">
                </div>

                <div class="ap-field ap-field--full">
                    <label for="pwd">Password</label>
                    <input type="password" id="pwd" name="pwd" class="bp-input" required autocomplete="current-password" placeholder="password">
                </div>

                <button class="bp-btn bp-btn--dark bp-btn--wide" type="submit">Sign in</button>

                <div class="ap-or"><span>Or</span></div>

                <div class="ap-social-row" aria-label="Social sign in placeholders">
                    <button class="ap-social-button" type="button" data-auth-placeholder="true" aria-label="Apple sign in placeholder" title="Placeholder only">
                        <span class="ap-social-button__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" focusable="false">
                                <path d="M16.52 1.9c.05 1.08-.4 2.13-1.15 2.92-.75.81-1.98 1.43-3.04 1.34-.07-1.04.42-2.15 1.12-2.9.78-.84 2.12-1.48 3.07-1.36ZM21.05 17.54c-.54 1.22-.8 1.77-1.49 2.85-.97 1.49-2.34 3.35-4.04 3.37-1.51.02-1.9-.99-3.96-.98-2.06.01-2.49 1-4 .98-1.7-.02-3-1.68-3.97-3.17-2.71-4.15-3-9.02-1.32-11.61 1.19-1.84 3.08-2.92 4.85-2.92 1.8 0 2.94 1 4.43 1 1.45 0 2.33-1 4.42-1 1.58 0 3.25.86 4.44 2.35-3.9 2.14-3.27 7.72.64 9.13Z"/>
                            </svg>
                        </span>
                        <span class="ap-social-button__label">Apple</span>
                    </button>
                    <button class="ap-social-button" type="button" data-auth-placeholder="true" aria-label="Google sign in placeholder" title="Placeholder only">
                        <span class="ap-social-button__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" focusable="false">
                                <path fill="#4285f4" d="M23.49 12.27c0-.83-.07-1.44-.22-2.08H12.24v4.02h6.48c-.13 1-.84 2.51-2.42 3.52l-.02.13 3.52 2.49.24.02c2.22-1.88 3.45-4.64 3.45-8.1Z"/>
                                <path fill="#34a853" d="M12.24 22.8c3.17 0 5.83-.96 7.77-2.62l-3.7-2.68c-.99.63-2.31 1.07-4.07 1.07-3.11 0-5.75-1.88-6.69-4.48l-.14.01-3.65 2.58-.05.12c1.93 3.5 5.9 6 10.53 6Z"/>
                                <path fill="#fbbc05" d="M5.55 14.09a6.3 6.3 0 0 1-.36-2.09c0-.72.13-1.43.35-2.09l-.01-.14-3.7-2.63-.12.05A10.18 10.18 0 0 0 .6 12c0 1.73.45 3.36 1.24 4.8l3.71-2.71Z"/>
                                <path fill="#ea4335" d="M12.24 5.43c2.2 0 3.69.87 4.54 1.6l3.32-2.96C18.06 2.34 15.41 1.2 12.24 1.2c-4.63 0-8.6 2.5-10.53 5.99l3.83 2.72c.96-2.6 3.6-4.48 6.7-4.48Z"/>
                            </svg>
                        </span>
                        <span class="ap-social-button__label">Google</span>
                    </button>
                </div>

                <p class="ap-auth-helper">New to Aperture? <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create an account</a></p>
            </form>
        </div>
    </section>
</main>

<?php get_footer(); ?>
