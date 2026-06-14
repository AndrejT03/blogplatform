<?php
/**
 * Site header.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$bp_logo = function_exists( 'bp_brand_logo_url' ) ? bp_brand_logo_url() : BP_CHILD_URI . '/assets/images/aperture-logo-full.png';

$is_contribute = is_page( 'write' );
$nav_items = [
    [ 'label' => 'Home',       'url' => home_url( '/' ),        'active' => is_front_page() || is_home() ],
    [ 'label' => 'Explore',    'url' => home_url( '/explore/' ), 'active' => is_page( 'explore' ) || is_category() || is_tag() || is_archive() ],
    [ 'label' => 'About',      'url' => home_url( '/about/' ),   'active' => is_page( 'about' ) ],
    [ 'label' => 'Contribute', 'url' => home_url( '/write/' ),   'active' => $is_contribute ],
];

$bp_user = is_user_logged_in() ? wp_get_current_user() : null;
$bp_avatar_source = $bp_user ? ( $bp_user->display_name ?: $bp_user->user_login ) : '';
$bp_avatar_letter = $bp_avatar_source ? strtoupper( function_exists( 'mb_substr' ) ? mb_substr( $bp_avatar_source, 0, 1 ) : substr( $bp_avatar_source, 0, 1 ) ) : '';
$bp_blocksy_header_render = class_exists( 'Blocksy_Header_Builder_Render' ) ? new Blocksy_Header_Builder_Render() : null;
$bp_blocksy_header_elements = class_exists( 'Blocksy_Header_Builder_Elements' ) ? new Blocksy_Header_Builder_Elements() : null;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="bp-topbar">
    <div class="bp-topbar__inner">
        <a class="bp-brand bp-brand--full" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( bp_brand_name() ); ?>">
            <img class="bp-brand__mark" src="<?php echo esc_url( $bp_logo ); ?>" alt="" width="150" height="51">
            <span class="bp-brand__name screen-reader-text"><?php echo esc_html( bp_brand_name() ); ?></span>
        </a>

        <nav class="bp-topnav" aria-label="<?php esc_attr_e( 'Primary navigation', 'blocksy-child' ); ?>">
            <?php foreach ( $nav_items as $item ) : ?>
                <a class="<?php echo $item['active'] ? 'is-current' : ''; ?>" href="<?php echo esc_url( $item['url'] ); ?>">
                    <?php echo esc_html( $item['label'] ); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="bp-topbar__right">
            <?php if ( is_user_logged_in() ) : ?>
                <div class="bp-profile-menu" data-userdrop>
                    <button class="bp-account-trigger" type="button" aria-label="<?php esc_attr_e( 'Profile menu', 'blocksy-child' ); ?>" aria-haspopup="true" aria-expanded="false" data-userdrop-trigger>
                        <span class="bp-account-trigger__status" aria-hidden="true"></span>
                        <span class="bp-profile-button"><?php echo esc_html( $bp_avatar_letter ); ?></span>
                        <span class="bp-account-trigger__name"><?php echo esc_html( $bp_user->display_name ?: $bp_user->user_login ); ?></span>
                        <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="m7 10 5 5 5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="bp-account-trigger__divider" aria-hidden="true"></span>
                    </button>
                    <div class="bp-profile-dropdown" role="menu">
                        <div class="bp-profile-dropdown__head">
                            <span class="bp-profile-button"><?php echo esc_html( $bp_avatar_letter ); ?></span>
                            <div>
                                <strong><?php echo esc_html( $bp_user->display_name ?: $bp_user->user_login ); ?></strong>
                                <small>Signed in</small>
                            </div>
                        </div>
                        <a role="menuitem" href="<?php echo esc_url( home_url( '/my-desk/' ) ); ?>">
                            <span class="bp-dropdown-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    <rect x="4.5" y="4.5" width="6" height="6" rx="1.2"></rect>
                                    <rect x="13.5" y="4.5" width="6" height="6" rx="1.2"></rect>
                                    <rect x="4.5" y="13.5" width="6" height="6" rx="1.2"></rect>
                                    <rect x="13.5" y="13.5" width="6" height="6" rx="1.2"></rect>
                                </svg>
                            </span>
                            My Content
                        </a>
                        <a role="menuitem" href="<?php echo esc_url( home_url( '/write/' ) ); ?>">
                            <span class="bp-dropdown-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    <path d="M12 5v14"></path>
                                    <path d="M5 12h14"></path>
                                </svg>
                            </span>
                            New essay
                        </a>
                        <?php if ( current_user_can( 'manage_options' ) ) : ?>
                            <a role="menuitem" href="<?php echo esc_url( admin_url() ); ?>">
                                <span class="bp-dropdown-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" focusable="false">
                                        <path d="M12 3.75v2.5"></path>
                                        <path d="M18.2 6.25l-1.75 1.75"></path>
                                        <path d="M21 12h-2.5"></path>
                                        <path d="M5.5 12H3"></path>
                                        <path d="M7.55 8 5.8 6.25"></path>
                                        <path d="M8 19.5h8"></path>
                                        <path d="M7 15a5 5 0 0 1 10 0"></path>
                                        <path d="m12 15 2.35-3.25"></path>
                                    </svg>
                                </span>
                                WP Admin
                            </a>
                        <?php endif; ?>
                        <a role="menuitem" class="is-danger" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">
                            <span class="bp-dropdown-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    <path d="M10 7 5 12l5 5"></path>
                                    <path d="M5 12h11"></path>
                                    <path d="M16 5h3v14h-3"></path>
                                </svg>
                            </span>
                            Sign out
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <a class="bp-btn bp-btn--dark bp-btn--sm" href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Sign in</a>
                <a class="bp-nav-link" href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create account</a>
            <?php endif; ?>
        </div>

        <?php if ( $bp_blocksy_header_render ) : ?>
            <div class="bp-blocksy-mobile-trigger">
                <?php echo $bp_blocksy_header_render->render_single_item( 'trigger', [ 'device' => 'mobile' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        <?php endif; ?>
    </div>
</header>

<?php
if ( $bp_blocksy_header_elements ) {
    echo $bp_blocksy_header_elements->render_offcanvas( [ 'device' => 'mobile' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>
