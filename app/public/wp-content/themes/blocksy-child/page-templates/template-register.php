<?php
/**
 * Template Name: Register
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/my-desk/' ) );
    exit;
}

$reg_error  = '';
$reg_fields = [ 'display_name' => '', 'user_email' => '' ];

if ( isset( $_POST['bp_register_nonce'] ) && wp_verify_nonce( $_POST['bp_register_nonce'], 'bp_register' ) ) {
    $display_name = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
    $user_email   = sanitize_email( wp_unslash( $_POST['user_email'] ?? '' ) );
    $user_pass    = isset( $_POST['user_pass'] ) ? (string) $_POST['user_pass'] : '';
    $confirm_pass = isset( $_POST['confirm_pass'] ) ? (string) $_POST['confirm_pass'] : '';

    $reg_fields = compact( 'display_name', 'user_email' );

    $email_parts = explode( '@', $user_email );
    $base_login = sanitize_user( $email_parts[0] ?? '', true );
    if ( ! $base_login ) {
        $base_login = sanitize_user( strtolower( str_replace( ' ', '.', $display_name ) ), true );
    }
    $user_login = $base_login ?: 'reader';
    $base_login = $user_login;
    $suffix = 1;
    while ( username_exists( $user_login ) ) {
        $suffix++;
        $user_login = $base_login . $suffix;
    }

    if ( empty( $display_name ) || empty( $user_email ) || empty( $user_pass ) || empty( $confirm_pass ) ) {
        $reg_error = 'Please fill every field.';
    } elseif ( ! is_email( $user_email ) ) {
        $reg_error = 'That email address is not valid.';
    } elseif ( strlen( $user_pass ) < 8 ) {
        $reg_error = 'Password should be at least 8 characters.';
    } elseif ( $user_pass !== $confirm_pass ) {
        $reg_error = 'Passwords do not match.';
    } elseif ( email_exists( $user_email ) ) {
        $reg_error = 'An account with that email already exists.';
    } else {
        $new_id = wp_insert_user( [
            'user_login'   => $user_login,
            'user_pass'    => $user_pass,
            'user_email'   => $user_email,
            'display_name' => $display_name,
            'first_name'   => $display_name,
            'role'         => 'contributor',
        ] );

        if ( is_wp_error( $new_id ) ) {
            $reg_error = wp_strip_all_tags( $new_id->get_error_message() );
        } else {
            wp_set_current_user( $new_id );
            wp_set_auth_cookie( $new_id, true );
            do_action( 'wp_login', $user_login, get_user_by( 'id', $new_id ) );
            wp_safe_redirect( home_url( '/my-desk/?welcome=1' ) );
            exit;
        }
    }
}

get_header();
?>

<main class="ap-auth">
    <section class="ap-auth__scene">
        <div class="ap-auth__glow" aria-hidden="true"></div>
        <div class="bp-shell ap-auth__grid">
            <aside class="ap-auth__copy reveal">
                <span class="ap-eyebrow">Join Aperture</span>
                <h1>A qui<span class="ap-gradient-text">et</span>er<br> place to read.</h1>
                <p>Create an account to submit essays, join conversations, and propose new categories - all reviewed by editors.</p>
                <ul>
                    <li><strong>Editor-reviewed</strong><span>Every contribution is read by a human.</span></li>
                    <li><strong>Track everything</strong><span>See what's pending, published, or declined.</span></li>
                    <li><strong>No noise</strong><span>No ads, no metrics, no infinite feeds.</span></li>
                </ul>
            </aside>

            <form class="ap-auth-card reveal" method="post" action="" autocomplete="on">
                <?php wp_nonce_field( 'bp_register', 'bp_register_nonce' ); ?>

                <a class="ap-auth-card__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <img src="<?php echo esc_url( function_exists( 'bp_brand_logo_url' ) ? bp_brand_logo_url() : BP_CHILD_URI . '/assets/images/aperture-logo-full.png' ); ?>" alt="" width="150" height="51">
                    <span class="screen-reader-text"><?php echo esc_html( bp_brand_name() ); ?></span>
                </a>

                <h2>Create your account</h2>
                <p>Free and takes about 20 seconds.</p>

                <?php if ( $reg_error ) : ?>
                    <div class="bp-form-error"><?php echo esc_html( $reg_error ); ?></div>
                <?php endif; ?>

                <div class="ap-field ap-field--full">
                    <label for="display_name">Full name</label>
                    <input type="text" id="display_name" name="display_name" class="bp-input" required value="<?php echo esc_attr( $reg_fields['display_name'] ); ?>" autocomplete="name" placeholder="Jane Doe">
                </div>

                <div class="ap-field ap-field--full">
                    <label for="user_email">Email</label>
                    <input type="email" id="user_email" name="user_email" class="bp-input" required value="<?php echo esc_attr( $reg_fields['user_email'] ); ?>" autocomplete="email" placeholder="you@domain.com">
                </div>

                <div class="ap-field ap-field--full">
                    <label for="user_pass">Password</label>
                    <input type="password" id="user_pass" name="user_pass" class="bp-input" required minlength="8" autocomplete="new-password" placeholder="password">
                </div>

                <div class="ap-field ap-field--full">
                    <label for="confirm_pass">Confirm password</label>
                    <input type="password" id="confirm_pass" name="confirm_pass" class="bp-input" required minlength="8" autocomplete="new-password" placeholder="password">
                </div>

                <button class="bp-btn bp-btn--dark bp-btn--wide" type="submit">Create account</button>

                <div class="ap-or"><span>Or</span></div>

                <div class="ap-social-row" aria-label="Social registration placeholders">
                    <button class="ap-social-button" type="button" data-auth-placeholder="true" aria-label="Apple registration placeholder" title="Placeholder only">
                        <span class="ap-social-button__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" focusable="false">
                                <path d="M16.52 1.9c.05 1.08-.4 2.13-1.15 2.92-.75.81-1.98 1.43-3.04 1.34-.07-1.04.42-2.15 1.12-2.9.78-.84 2.12-1.48 3.07-1.36ZM21.05 17.54c-.54 1.22-.8 1.77-1.49 2.85-.97 1.49-2.34 3.35-4.04 3.37-1.51.02-1.9-.99-3.96-.98-2.06.01-2.49 1-4 .98-1.7-.02-3-1.68-3.97-3.17-2.71-4.15-3-9.02-1.32-11.61 1.19-1.84 3.08-2.92 4.85-2.92 1.8 0 2.94 1 4.43 1 1.45 0 2.33-1 4.42-1 1.58 0 3.25.86 4.44 2.35-3.9 2.14-3.27 7.72.64 9.13Z"/>
                            </svg>
                        </span>
                        <span class="ap-social-button__label">Apple</span>
                    </button>
                    <button class="ap-social-button" type="button" data-auth-placeholder="true" aria-label="Google registration placeholder" title="Placeholder only">
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

                <p class="ap-auth-helper">Already have an account? <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Sign in</a></p>
            </form>
        </div>
    </section>
</main>

<?php get_footer(); ?>
