<?php
/**
 * Plugin Name: Aperture Workflow
 * Description: Activates and configures ready-made workflow plugins while the child theme stays focused on presentation.
 * Version: 1.1.0
 * Author: Aperture Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'APERTURE_WORKFLOW_VERSION', '1.1.0' );

if ( ! function_exists( 'aperture_ready_plugins' ) ) {
    function aperture_ready_plugins() {
        return [
            'publishpress/publishpress.php' => [
                'name' => 'PublishPress Planner',
                'role' => 'editorial workflow and post review',
            ],
            'antispam-bee/antispam_bee.php' => [
                'name' => 'Antispam Bee',
                'role' => 'comment spam filtering',
            ],
            'blocksy-companion/blocksy-companion.php' => [
                'name' => 'Blocksy Companion',
                'role' => 'Blocksy theme features',
            ],
            'wp-user-frontend/wpuf.php' => [
                'name' => 'WP User Frontend',
                'role' => 'front-end post submission and author dashboard',
            ],
            'remove-dashboard-access-for-non-admins/remove-dashboard-access.php' => [
                'name' => 'Remove Dashboard Access',
                'role' => 'non-admin dashboard redirects',
            ],
            'post-views-counter/post-views-counter.php' => [
                'name' => 'Post Views Counter',
                'role' => 'post view tracking and reporting',
            ],
            'wp-ulike/wp-ulike.php' => [
                'name' => 'WP ULike',
                'role' => 'likes and engagement counters',
            ],
            'capability-manager-enhanced/capsman-enhanced.php' => [
                'name' => 'PublishPress Capabilities',
                'role' => 'role and capability management',
            ],
        ];
    }
}

if ( ! function_exists( 'aperture_is_plugin_active' ) ) {
    function aperture_is_plugin_active( $plugin_file ) {
        $active = (array) get_option( 'active_plugins', [] );
        return in_array( $plugin_file, $active, true );
    }
}

if ( ! function_exists( 'aperture_bootstrap_ready_plugins' ) ) {
    function aperture_bootstrap_ready_plugins() {
        $active = (array) get_option( 'active_plugins', [] );

        // Remove the deleted legacy custom moderation plugin from the active list.
        $stale = [ 'blog-moderation/blog-moderation.php' ];
        $kept  = array_values( array_diff( $active, $stale ) );
        if ( count( $kept ) !== count( $active ) ) {
            update_option( 'active_plugins', $kept );
            $active = $kept;
        }

        if ( get_option( 'aperture_ready_plugins_seeded' ) === APERTURE_WORKFLOW_VERSION ) {
            return;
        }

        if ( ! function_exists( 'activate_plugin' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach ( aperture_ready_plugins() as $plugin_file => $details ) {
            if ( aperture_is_plugin_active( $plugin_file ) ) {
                continue;
            }

            if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
                continue;
            }

            $result = activate_plugin( $plugin_file, '', false, false );
            if ( is_wp_error( $result ) ) {
                continue;
            }
        }

        aperture_configure_ready_plugins();
        update_option( 'aperture_ready_plugins_seeded', APERTURE_WORKFLOW_VERSION );
    }
}
add_action( 'admin_init', 'aperture_bootstrap_ready_plugins', 1 );

if ( ! function_exists( 'aperture_table_exists' ) ) {
    function aperture_table_exists( $table_name ) {
        global $wpdb;

        return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name;
    }
}

if ( ! function_exists( 'aperture_repair_ready_plugin_tables' ) ) {
    function aperture_repair_ready_plugin_tables() {
        global $wpdb;

        if ( get_option( 'aperture_ready_plugin_tables_seeded' ) === APERTURE_WORKFLOW_VERSION ) {
            return;
        }

        if (
            aperture_is_plugin_active( 'post-views-counter/post-views-counter.php' )
            && ! aperture_table_exists( $wpdb->prefix . 'post_views' )
            && function_exists( 'Post_Views_Counter' )
        ) {
            Post_Views_Counter()->activate_site();
        }

        $ulike_tables = [
            $wpdb->prefix . 'ulike',
            $wpdb->prefix . 'ulike_comments',
            $wpdb->prefix . 'ulike_activities',
            $wpdb->prefix . 'ulike_forums',
            $wpdb->prefix . 'ulike_meta',
        ];

        $missing_ulike_table = false;
        foreach ( $ulike_tables as $table_name ) {
            if ( ! aperture_table_exists( $table_name ) ) {
                $missing_ulike_table = true;
                break;
            }
        }

        if (
            $missing_ulike_table
            && aperture_is_plugin_active( 'wp-ulike/wp-ulike.php' )
            && defined( 'WP_ULIKE_INC_DIR' )
        ) {
            if ( ! class_exists( 'wp_ulike_activator' ) ) {
                require_once WP_ULIKE_INC_DIR . '/classes/class-wp-ulike-activator.php';
            }

            if ( class_exists( 'wp_ulike_activator' ) ) {
                wp_ulike_activator::get_instance()->activate();
            }
        }

        if (
            aperture_table_exists( $wpdb->prefix . 'post_views' )
            && ! array_filter( $ulike_tables, function ( $table_name ) {
                return ! aperture_table_exists( $table_name );
            } )
        ) {
            update_option( 'aperture_ready_plugin_tables_seeded', APERTURE_WORKFLOW_VERSION, false );
        }
    }
}
add_action( 'init', 'aperture_repair_ready_plugin_tables', 5 );

if ( ! function_exists( 'aperture_configure_ready_plugins' ) ) {
    function aperture_configure_ready_plugins() {
        update_option( 'comment_moderation', '1' );
        update_option( 'comment_previously_approved', '0' );

        update_option( 'rda_access_switch', 'manage_options' );
        update_option( 'rda_access_cap', 'manage_options' );
        update_option( 'rda_redirect_url', home_url( '/my-desk/' ) );
        update_option( 'rda_enable_profile', 1 );
        update_option( 'rda_lock_ajax', 0 );
        update_option( 'rda_url_allowlist', '' );

        $pvc_general = get_option( 'post_views_counter_settings_general', [] );
        $pvc_general = is_array( $pvc_general ) ? $pvc_general : [];
        $pvc_general = array_merge( $pvc_general, [
            'post_types_count' => [ 'post' ],
            'data_storage'     => 'cookies',
            'counter_mode'     => 'php',
        ] );
        update_option( 'post_views_counter_settings_general', $pvc_general, false );

        $pvc_display = get_option( 'post_views_counter_settings_display', [] );
        $pvc_display = is_array( $pvc_display ) ? $pvc_display : [];
        $pvc_display = array_merge( $pvc_display, [
            'post_types_display' => [],
            'page_types_display' => [],
        ] );
        update_option( 'post_views_counter_settings_display', $pvc_display, false );

        $ulike_settings = get_option( 'wp_ulike_settings', [] );
        $ulike_settings = is_array( $ulike_settings ) ? $ulike_settings : [];
        $ulike_settings['posts_group'] = array_merge(
            isset( $ulike_settings['posts_group'] ) && is_array( $ulike_settings['posts_group'] ) ? $ulike_settings['posts_group'] : [],
            [
                'template'                    => 'wpulike-default',
                'button_type'                 => 'image',
                'enable_auto_display'         => false,
                'logging_method'              => 'by_username',
                'enable_only_logged_in_users' => true,
                'counter_display_condition'   => 'visible',
            ]
        );
        $ulike_settings['comments_group'] = array_merge(
            isset( $ulike_settings['comments_group'] ) && is_array( $ulike_settings['comments_group'] ) ? $ulike_settings['comments_group'] : [],
            [
                'enable_auto_display' => false,
                'logging_method'      => 'by_username',
            ]
        );
        $ulike_settings['enable_toast_notice'] = false;
        update_option( 'wp_ulike_settings', $ulike_settings, false );
    }
}

add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }

    $missing = [];
    foreach ( aperture_ready_plugins() as $plugin_file => $details ) {
        if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
            $missing[] = $details['name'] . ' (' . $details['role'] . ')';
        }
    }

    if ( ! $missing ) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo '<strong>Aperture:</strong> Install these ready plugins to keep custom workflow code minimal: ';
    echo esc_html( implode( ', ', $missing ) );
    echo '.</p></div>';
} );

/* =========================================================================
 * Front-end post submission workflow.
 * ====================================================================== */
add_action( 'init', function () {

    if ( ! isset( $_POST['bp_submit_post_nonce'] ) ) { return; }
    if ( ! wp_verify_nonce( $_POST['bp_submit_post_nonce'], 'bp_submit_post' ) ) { return; }
    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/login/' ) ); exit;
    }

    $user_id = get_current_user_id();
    $action  = isset( $_POST['bp_action'] ) && in_array( $_POST['bp_action'], [ 'submit', 'draft' ], true )
        ? $_POST['bp_action']
        : 'submit';

    $title       = isset( $_POST['bp_title'] )    ? sanitize_text_field( wp_unslash( $_POST['bp_title'] ) ) : '';
    $excerpt     = isset( $_POST['bp_excerpt'] )  ? sanitize_textarea_field( wp_unslash( $_POST['bp_excerpt'] ) ) : '';
    $content_raw = isset( $_POST['bp_content'] )  ? wp_unslash( $_POST['bp_content'] ) : '';
    $content     = wp_kses_post( $content_raw );
    $category    = isset( $_POST['bp_category'] ) ? absint( $_POST['bp_category'] ) : 0;
    $tags        = isset( $_POST['bp_tags'] )     ? sanitize_text_field( wp_unslash( $_POST['bp_tags'] ) ) : '';
    $visibility  = isset( $_POST['bp_visibility'] ) && $_POST['bp_visibility'] === 'members' ? 'members' : 'public';
    $suggested   = isset( $_POST['bp_suggest_category'] ) ? sanitize_text_field( wp_unslash( $_POST['bp_suggest_category'] ) ) : '';
    $contribution_mode = isset( $_POST['bp_contribution_mode'] ) ? sanitize_key( wp_unslash( $_POST['bp_contribution_mode'] ) ) : 'essay';
    $edit_id     = isset( $_POST['bp_edit_id'] )  ? absint( $_POST['bp_edit_id'] ) : 0;
    $link_raw    = isset( $_POST['bp_external_link'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['bp_external_link'] ) ) ) : '';
    $link_url    = '';
    if ( $link_raw !== '' ) {
        if ( ! preg_match( '#^[a-z][a-z0-9+.-]*://#i', $link_raw ) ) {
            $link_raw = 'https://' . $link_raw;
        }
        $link_url = esc_url_raw( $link_raw );
    }
    $thumbnail_url_raw = isset( $_POST['bp_thumbnail_url'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['bp_thumbnail_url'] ) ) ) : '';
    $thumbnail_url     = '';
    if ( $thumbnail_url_raw !== '' ) {
        if ( ! preg_match( '#^[a-z][a-z0-9+.-]*://#i', $thumbnail_url_raw ) ) {
            $thumbnail_url_raw = 'https://' . $thumbnail_url_raw;
        }
        $thumbnail_url = esc_url_raw( $thumbnail_url_raw );
    }

    $cover_variant = isset( $_POST['bp_cover_variant'] ) ? sanitize_key( wp_unslash( $_POST['bp_cover_variant'] ) ) : 'blue';
    $cover_variants = [ 'blue', 'purple', 'orange', 'green', 'rose', 'indigo' ];
    if ( ! in_array( $cover_variant, $cover_variants, true ) ) {
        $cover_variant = 'blue';
    }
    $remove_thumbnail = ! empty( $_POST['bp_remove_thumbnail'] );

    if ( $contribution_mode === 'category' ) {
        if ( $suggested === '' ) {
            set_transient( 'bp_submit_error_' . $user_id, 'Category name is required.', 60 );
            wp_safe_redirect( wp_get_referer() ?: home_url( '/write/' ) );
            exit;
        }

        $existing_term = get_term_by( 'name', $suggested, 'category' );
        if ( ! $existing_term ) {
            $new_term = wp_insert_term( $suggested, 'category', [
                'description' => 'Suggested by ' . wp_get_current_user()->display_name . ' on ' . date_i18n( 'j M Y' ) . '. Pending editor review.',
            ] );
            if ( is_wp_error( $new_term ) ) {
                set_transient( 'bp_submit_error_' . $user_id, $new_term->get_error_message(), 60 );
                wp_safe_redirect( wp_get_referer() ?: home_url( '/write/' ) );
                exit;
            }
            update_term_meta( $new_term['term_id'], '_bp_term_status', 'pending' );
            update_term_meta( $new_term['term_id'], '_bp_term_suggester', $user_id );
        }

        set_transient( 'bp_category_success_' . $user_id, $suggested, 60 );
        wp_safe_redirect( home_url( '/write/' ) );
        exit;
    }

    if ( $action === 'submit' && ( empty( $title ) || empty( $content ) ) ) {
        set_transient( 'bp_submit_error_' . $user_id, 'Title and body are required to submit. Save as draft if you\'re not done yet.', 60 );
        wp_safe_redirect( wp_get_referer() ?: home_url( '/write/' ) );
        exit;
    }

    $post_status = $action === 'draft' ? 'draft' : 'pending';

    $base_data = [
        'post_title'   => $title ?: 'Untitled',
        'post_content' => $content,
        'post_excerpt' => $excerpt,
        'post_status'  => $post_status,
        'post_author'  => $user_id,
        'post_type'    => 'post',
    ];

    if ( $edit_id ) {
        $existing = get_post( $edit_id );
        if (
            ! $existing
            || intval( $existing->post_author ) !== $user_id
            || $existing->post_status === 'trash'
            || get_post_meta( $edit_id, '_bp_declined_at', true )
        ) {
            wp_safe_redirect( home_url( '/my-desk/' ) ); exit;
        }
        $base_data['ID'] = $edit_id;
        $post_id = wp_update_post( $base_data, true );
    } else {
        $post_id = wp_insert_post( $base_data, true );
    }

    if ( is_wp_error( $post_id ) ) {
        set_transient( 'bp_submit_error_' . $user_id, $post_id->get_error_message(), 60 );
        wp_safe_redirect( wp_get_referer() ?: home_url( '/write/' ) );
        exit;
    }

    update_post_meta( $post_id, '_bp_visibility', $visibility );
    update_post_meta( $post_id, '_bp_cover_variant', $cover_variant );

    if ( $thumbnail_url ) {
        update_post_meta( $post_id, '_bp_external_cover_url', $thumbnail_url );
    } else {
        delete_post_meta( $post_id, '_bp_external_cover_url' );
    }

    if ( $link_url ) {
        update_post_meta( $post_id, '_bp_external_link', $link_url );
    } else {
        delete_post_meta( $post_id, '_bp_external_link' );
    }

    if ( $suggested !== '' ) {
        $existing_term = get_term_by( 'name', $suggested, 'category' );
        if ( ! $existing_term ) {
            $new_term = wp_insert_term( $suggested, 'category', [
                'description' => 'Suggested by ' . wp_get_current_user()->display_name . ' on ' . date_i18n( 'j M Y' ) . '. Pending editor review.',
            ] );
            if ( ! is_wp_error( $new_term ) ) {
                update_term_meta( $new_term['term_id'], '_bp_term_status', 'pending' );
                update_term_meta( $new_term['term_id'], '_bp_term_suggester', $user_id );
                wp_set_post_categories( $post_id, [ $new_term['term_id'] ] );
            }
        } else {
            wp_set_post_categories( $post_id, [ $existing_term->term_id ] );
        }
    } elseif ( $category > 0 ) {
        wp_set_post_categories( $post_id, [ $category ] );
    }

    if ( ! empty( $tags ) ) {
        $tag_arr = array_filter( array_map( 'trim', explode( ',', $tags ) ) );
        if ( $tag_arr ) {
            wp_set_post_tags( $post_id, $tag_arr );
        }
    }

    if ( $remove_thumbnail && empty( $_FILES['bp_thumbnail']['name'] ) && empty( $thumbnail_url ) ) {
        delete_post_thumbnail( $post_id );
    }

    if ( ! empty( $_FILES['bp_thumbnail']['name'] ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $att_id = media_handle_upload( 'bp_thumbnail', $post_id );
        if ( ! is_wp_error( $att_id ) ) {
            set_post_thumbnail( $post_id, $att_id );
            delete_post_meta( $post_id, '_bp_external_cover_url' );
        }
    } elseif ( $thumbnail_url ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $att_id = media_sideload_image( $thumbnail_url, $post_id, null, 'id' );
        if ( ! is_wp_error( $att_id ) ) {
            set_post_thumbnail( $post_id, $att_id );
        }
    }

    if ( $action === 'draft' ) {
        wp_safe_redirect( add_query_arg( 'tab', 'drafts', home_url( '/my-desk/' ) ) );
        exit;
    }

    set_transient( 'bp_submit_success_' . $user_id, $post_id, 60 );
    wp_safe_redirect( home_url( '/write/' ) );
    exit;
} );

/* =========================================================================
 * Editorial workflow integrations.
 * ====================================================================== */
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
    if ( $post->post_type !== 'post' )    { return; }
    if ( $new_status !== 'publish' )      { return; }
    if ( $old_status === 'publish' )      { return; }

    $vis = get_post_meta( $post->ID, '_bp_visibility', true );
    if ( $vis === 'members' ) {
        wp_update_post( [ 'ID' => $post->ID, 'post_status' => 'private' ] );
    }
}, 10, 3 );

add_action( 'pre_get_terms', function ( $query ) {

    if ( is_admin() ) { return; }
    $taxonomies = (array) $query->query_vars['taxonomy'];
    if ( ! in_array( 'category', $taxonomies, true ) ) { return; }

    $existing_meta = $query->query_vars['meta_query'] ?? [];
    if ( $existing_meta ) { return; }

    $query->query_vars['meta_query'] = [
        'relation' => 'OR',
        [ 'key' => '_bp_term_status', 'value' => 'approved' ],
        [ 'key' => '_bp_term_status', 'compare' => 'NOT EXISTS' ],
    ];
} );

add_filter( 'manage_edit-category_columns', function ( $cols ) {
    $cols['bp_status'] = 'Status';
    return $cols;
} );

add_filter( 'manage_category_custom_column', function ( $content, $column, $term_id ) {
    if ( $column !== 'bp_status' ) { return $content; }
    $status = get_term_meta( $term_id, '_bp_term_status', true );
    if ( $status === 'pending' ) {
        $approve = wp_nonce_url(
            admin_url( 'admin-post.php?action=bp_approve_term&term_id=' . $term_id ),
            'bp_approve_term_' . $term_id
        );
        return '<span style="color:var(--warn); background:rgba(255, 159, 10, 0.1); padding:2px 8px; border-radius:99px; font-size:11px;">Pending</span> &nbsp;<a href="' . esc_url( $approve ) . '">Approve</a>';
    }
    return '<span style="color:var(--ok); background:rgba(50, 215, 75, 0.1); padding:2px 8px; border-radius:99px; font-size:11px;">Approved</span>';
}, 10, 3 );

add_action( 'admin_post_bp_approve_term', function () {
    $term_id = isset( $_GET['term_id'] ) ? absint( $_GET['term_id'] ) : 0;
    if ( ! $term_id || ! current_user_can( 'manage_categories' ) ) { wp_die( 'No.' ); }
    check_admin_referer( 'bp_approve_term_' . $term_id );
    update_term_meta( $term_id, '_bp_term_status', 'approved' );
    wp_safe_redirect( admin_url( 'edit-tags.php?taxonomy=category&approved=1' ) );
    exit;
} );

/* =========================================================================
 * Auth routing for the custom login/register UI.
 * ====================================================================== */
add_action( 'login_init', function () {

    if ( ! empty( $_POST ) )                              { return; }
    if ( defined( 'DOING_AJAX' )  && DOING_AJAX )         { return; }
    if ( defined( 'DOING_CRON' )  && DOING_CRON )         { return; }
    if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'GET' ) { return; }

    $action  = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';
    $owned_by_wp = [ 'logout', 'lostpassword', 'retrievepassword', 'rp', 'resetpass', 'postpass', 'confirm_admin_email', 'confirmaction' ];
    if ( in_array( $action, $owned_by_wp, true ) ) { return; }

    $redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : '';
    if ( $redirect_to && strpos( $redirect_to, '/wp-admin' ) !== false ) {
        return;
    }

    if ( $action === 'register' ) {
        $page = get_page_by_path( 'register' );
        if ( $page ) {
            wp_safe_redirect( home_url( '/register/' ) ); exit;
        }
        return;
    }

    $page = get_page_by_path( 'login' );
    if ( $page ) {
        wp_safe_redirect( home_url( '/login/' ) ); exit;
    }
} );
