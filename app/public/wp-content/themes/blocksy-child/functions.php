<?php
/**
 * Theme: Aperture - Blocksy child theme
 *
 * - Enqueues fonts, dark editorial stylesheet and main.js
 * - Registers custom page templates (Categories, My Desk, Write,
 *   Login, Register, About, Explore, Authors)
 * - Light helpers used in templates (read counts, brand name, default
 *   category blurbs, roman year for the footer)
 *
 * Platform workflow hooks live in wp-content/mu-plugins/aperture-workflow.php,
 * backed by ready plugins such as PublishPress Planner, Antispam Bee,
 * Post Views Counter, WP ULike, WP User Frontend and PublishPress Capabilities.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'BP_CHILD_VERSION', '3.1.0' );
define( 'BP_CHILD_DIR', get_stylesheet_directory() );
define( 'BP_CHILD_URI', get_stylesheet_directory_uri() );

if ( ! function_exists( 'bp_asset_version' ) ) {
    function bp_asset_version( $relative_path ) {
        $file = BP_CHILD_DIR . '/' . ltrim( $relative_path, '/' );
        return file_exists( $file ) ? (string) filemtime( $file ) : BP_CHILD_VERSION;
    }
}

if ( ! function_exists( 'bp_brand_icon_url' ) ) {
    function bp_brand_icon_url() {
        return BP_CHILD_URI . '/assets/images/aperture-logo-mark.png';
    }
}

if ( ! function_exists( 'bp_brand_full_logo_url' ) ) {
    function bp_brand_full_logo_url() {
        return BP_CHILD_URI . '/assets/images/aperture-logo-full.png';
    }
}

if ( ! function_exists( 'bp_brand_logo_url' ) ) {
    function bp_brand_logo_url() {
        return bp_brand_full_logo_url();
    }
}

if ( ! function_exists( 'bp_site_icon_url' ) ) {
    function bp_site_icon_url() {
        $png = BP_CHILD_DIR . '/assets/images/site-icon.png';
        return file_exists( $png ) ? BP_CHILD_URI . '/assets/images/site-icon.png' : bp_brand_icon_url();
    }
}

if ( ! function_exists( 'bp_render_site_icons' ) ) {
    function bp_render_site_icons() {
        $png = bp_site_icon_url();
        ?>
        <link rel="icon" href="<?php echo esc_url( $png ); ?>" type="image/png" sizes="512x512">
        <link rel="apple-touch-icon" href="<?php echo esc_url( $png ); ?>">
        <?php
    }
}

if ( ! function_exists( 'bp_remove_core_site_icon' ) ) {
    function bp_remove_core_site_icon() {
        remove_action( 'wp_head', 'wp_site_icon', 99 );
        remove_action( 'admin_head', 'wp_site_icon', 99 );
        remove_action( 'login_head', 'wp_site_icon', 99 );
    }
}

bp_remove_core_site_icon();
add_action( 'init', 'bp_remove_core_site_icon' );
add_action( 'login_init', 'bp_remove_core_site_icon' );
add_action( 'admin_init', 'bp_remove_core_site_icon' );
add_action( 'wp_head', function () {
    remove_action( 'wp_head', 'wp_site_icon', 99 );
}, 0 );
add_action( 'admin_head', 'bp_remove_core_site_icon', 0 );
add_action( 'login_head', 'bp_remove_core_site_icon', 0 );
add_action( 'wp_head', 'bp_render_site_icons', 1 );
add_action( 'admin_head', 'bp_render_site_icons', 1 );
add_action( 'login_head', 'bp_render_site_icons', 1 );

/* =========================================================================
 * Assets — fonts, stylesheet, script
 * ====================================================================== */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'blocksy-parent-style',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme( 'blocksy' )->get( 'Version' )
    );

    wp_enqueue_style(
        'bp-main-style',
        BP_CHILD_URI . '/assets/css/main.css',
        [ 'blocksy-parent-style' ],
        bp_asset_version( 'assets/css/main.css' )
    );

    wp_enqueue_script(
        'bp-main-script',
        BP_CHILD_URI . '/assets/js/main.js',
        [],
        bp_asset_version( 'assets/js/main.js' ),
        true
    );

    wp_localize_script( 'bp-main-script', 'BP_DATA', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'bp_nonce' ),
        'home_url' => home_url( '/' ),
    ] );
} );

/* =========================================================================
 * Theme supports + nav menus
 * ====================================================================== */
add_action( 'after_setup_theme', function () {
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'html5', [ 'comment-list', 'comment-form', 'search-form' ] );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'custom-logo' );

    register_nav_menus( [
        'primary' => __( 'Primary Menu', 'blocksy-child' ),
        'footer'  => __( 'Footer Menu',  'blocksy-child' ),
    ] );
} );

/* =========================================================================
 * Custom page templates
 * ====================================================================== */
add_filter( 'theme_page_templates', function ( $templates ) {
    return array_merge( $templates, [
        'page-templates/template-write.php'      => 'Write - Editor',
        'page-templates/template-my-desk.php'    => 'My Desk',
        'page-templates/template-categories.php' => 'Categories',
        'page-templates/template-explore.php'    => 'Explore',
        'page-templates/template-login.php'      => 'Sign in',
        'page-templates/template-register.php'   => 'Register',
        'page-templates/template-about.php'      => 'About / Manifesto',
        'page-templates/template-authors.php'    => 'Contributors',
    ] );
} );

/* =========================================================================
 * Auto-create the platform pages.
 *
 * This used to live on `after_switch_theme` (one-shot on activation), but
 * that meant a tweak to the page list never took effect without a manual
 * Re-Save Permalinks. Running on init with a version flag keeps the page
 * set in sync without re-activation: cheap to check, idempotent, and the
 * version flag short-circuits everything once the work is done.
 * ====================================================================== */
function bp_ensure_platform_pages() {

    if ( get_option( 'bp_pages_version' ) === BP_CHILD_VERSION ) {
        return;
    }

    $pages = [
        [ 'title' => 'About',        'slug' => 'about',      'template' => 'page-templates/template-about.php' ],
        [ 'title' => 'Explore',      'slug' => 'explore',    'template' => 'page-templates/template-explore.php' ],
        [ 'title' => 'Topics',       'slug' => 'categories', 'template' => 'page-templates/template-categories.php' ],
        [ 'title' => 'My content',   'slug' => 'my-desk',    'template' => 'page-templates/template-my-desk.php' ],
        [ 'title' => 'Write',        'slug' => 'write',      'template' => 'page-templates/template-write.php' ],
        [ 'title' => 'Sign in',      'slug' => 'login',      'template' => 'page-templates/template-login.php' ],
        [ 'title' => 'Register',     'slug' => 'register',   'template' => 'page-templates/template-register.php' ],
        [ 'title' => 'Contributors', 'slug' => 'authors',    'template' => 'page-templates/template-authors.php' ],
    ];

    foreach ( $pages as $p ) {
        $existing = get_page_by_path( $p['slug'] );
        if ( ! $existing ) {
            $page_id = wp_insert_post( [
                'post_title'   => $p['title'],
                'post_name'    => $p['slug'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ] );
            if ( $page_id && ! is_wp_error( $page_id ) ) {
                update_post_meta( $page_id, '_wp_page_template', $p['template'] );
            }
        } else {
            // Keep the template binding in sync; cheap because update_post_meta
            // is a no-op if the value hasn't changed.
            update_post_meta( $existing->ID, '_wp_page_template', $p['template'] );
            if ( $existing->post_status !== 'publish' ) {
                wp_update_post( [ 'ID' => $existing->ID, 'post_status' => 'publish' ] );
            }
        }
    }

    // Always render posts on the front, so our front-page.php wins.
    update_option( 'show_on_front', 'posts' );

    // Seed default categories with editorial blurbs and mark them approved.
    $seed_terms = [
        'Technology' => 'AI, software, gadgets and the digital frontier.',
        'Design'     => 'Visual culture, UX, typography and craft.',
        'Culture'    => 'Art, film, music and the human experience.',
        'Science'    => 'Physics, biology, space and discoveries.',
        'Travel'     => 'Destinations, stories and wanderlust.',
        'Health'     => 'Wellness, nutrition, mental health and movement.',
        'Business'   => 'Startups, strategy, finance and leadership.',
        'Philosophy' => 'Ethics, logic, consciousness and meaning.',
    ];
    foreach ( $seed_terms as $name => $desc ) {
        $term = get_term_by( 'name', $name, 'category' );
        if ( ! $term ) {
            $created = wp_insert_term( $name, 'category', [ 'description' => $desc ] );
            if ( ! is_wp_error( $created ) ) {
                update_term_meta( $created['term_id'], '_bp_term_status', 'approved' );
            }
        } else {
            update_term_meta( $term->term_id, '_bp_term_status', 'approved' );
            if ( ! $term->description ) {
                wp_update_term( $term->term_id, 'category', [ 'description' => $desc ] );
            }
        }
    }

    // Permalinks need a refresh so /about/, /explore/ etc. resolve cleanly.
    flush_rewrite_rules( false );

    update_option( 'bp_pages_version', BP_CHILD_VERSION );
}
add_action( 'init', 'bp_ensure_platform_pages', 20 );

if ( ! function_exists( 'bp_ensure_english_platform_page_titles' ) ) {
    function bp_ensure_english_platform_page_titles() {
        $titles = [
            'about'       => 'About',
            'authors'     => 'Contributors',
            'submit-post' => 'Submit',
            'my-posts'    => 'My posts',
            'submit'      => 'Submit',
            'my-desk'     => 'My content',
            'write'       => 'Write',
            'explore'     => 'Explore',
            'categories'  => 'Topics',
            'login'       => 'Sign in',
            'register'    => 'Register',
        ];

        foreach ( $titles as $slug => $title ) {
            $page = get_page_by_path( $slug );
            if ( ! $page || ! preg_match( '/\p{Cyrillic}/u', $page->post_title ) ) {
                continue;
            }

            wp_update_post( [
                'ID'         => $page->ID,
                'post_title' => $title,
            ] );
        }
    }
}
add_action( 'init', 'bp_ensure_english_platform_page_titles', 21 );

// Also run once on activation, so the user can start clicking before any
// front-end request would have been the first to fire init.
add_action( 'after_switch_theme', 'bp_ensure_platform_pages' );

if ( ! function_exists( 'bp_ensure_blocksy_nav_menu' ) ) {
    function bp_ensure_blocksy_nav_menu() {
        if ( get_option( 'bp_blocksy_nav_menu_seeded' ) === BP_CHILD_VERSION ) {
            return;
        }

        $locations = (array) get_nav_menu_locations();

        if ( ! empty( $locations['menu_1'] ) && ! empty( $locations['menu_mobile'] ) ) {
            update_option( 'bp_blocksy_nav_menu_seeded', BP_CHILD_VERSION );
            return;
        }

        $menu_name = 'Aperture Main';
        $menu      = wp_get_nav_menu_object( $menu_name );
        $menu_id   = $menu ? (int) $menu->term_id : 0;

        if ( ! $menu_id ) {
            $created = wp_create_nav_menu( $menu_name );
            if ( is_wp_error( $created ) ) {
                return;
            }
            $menu_id = (int) $created;
        }

        $existing_items = wp_get_nav_menu_items( $menu_id );

        if ( empty( $existing_items ) ) {
            $items = [
                [ 'title' => 'Home',       'url' => home_url( '/' ) ],
                [ 'title' => 'Explore',    'url' => home_url( '/explore/' ) ],
                [ 'title' => 'About',      'url' => home_url( '/about/' ) ],
                [ 'title' => 'Contribute', 'url' => home_url( '/write/' ) ],
            ];

            foreach ( $items as $item ) {
                wp_update_nav_menu_item( $menu_id, 0, [
                    'menu-item-title'  => $item['title'],
                    'menu-item-url'    => $item['url'],
                    'menu-item-status' => 'publish',
                    'menu-item-type'   => 'custom',
                ] );
            }
        }

        $locations['menu_1']      = $menu_id;
        $locations['menu_mobile'] = $menu_id;
        $locations['primary']     = $menu_id;

        set_theme_mod( 'nav_menu_locations', $locations );
        update_option( 'bp_blocksy_nav_menu_seeded', BP_CHILD_VERSION );
    }
}
add_action( 'init', 'bp_ensure_blocksy_nav_menu', 21 );

if ( ! function_exists( 'bp_ensure_blocksy_mobile_header_builder' ) ) {
    function bp_ensure_blocksy_mobile_header_builder() {
        if ( get_option( 'bp_blocksy_mobile_header_seeded' ) === BP_CHILD_VERSION ) {
            return;
        }

        $header = get_theme_mod( 'header_placements' );

        if ( ! is_array( $header ) || empty( $header['sections'] ) || ! is_array( $header['sections'] ) ) {
            return;
        }

        foreach ( $header['sections'] as &$section ) {
            if ( ! isset( $section['items'] ) || ! is_array( $section['items'] ) ) {
                $section['items'] = [];
            }

            $has_offcanvas_item = false;
            foreach ( $section['items'] as &$item ) {
                if ( isset( $item['id'] ) && $item['id'] === 'offcanvas' ) {
                    $item['values'] = array_merge( (array) ( $item['values'] ?? [] ), [
                        'offcanvas_behavior' => 'modal',
                    ] );
                    $has_offcanvas_item = true;
                    break;
                }
            }
            unset( $item );

            if ( ! $has_offcanvas_item ) {
                $section['items'][] = [
                    'id'     => 'offcanvas',
                    'values' => [
                        'offcanvas_behavior' => 'modal',
                    ],
                ];
            }

            if ( isset( $section['mobile'] ) && is_array( $section['mobile'] ) ) {
                foreach ( $section['mobile'] as &$row ) {
                    if ( empty( $row['placements'] ) || ! is_array( $row['placements'] ) ) {
                        continue;
                    }

                    foreach ( $row['placements'] as &$placement ) {
                        if ( ! isset( $placement['items'] ) || ! is_array( $placement['items'] ) ) {
                            $placement['items'] = [];
                        }

                        if ( $row['id'] === 'middle-row' && $placement['id'] === 'end' && ! in_array( 'trigger', $placement['items'], true ) ) {
                            $placement['items'][] = 'trigger';
                        }

                        if ( $row['id'] === 'offcanvas' && $placement['id'] === 'start' && ! in_array( 'mobile-menu', $placement['items'], true ) ) {
                            $placement['items'][] = 'mobile-menu';
                        }
                    }
                    unset( $placement );
                }
                unset( $row );
            }
        }
        unset( $section );

        set_theme_mod( 'header_placements', $header );
        update_option( 'bp_blocksy_mobile_header_seeded', BP_CHILD_VERSION );
    }
}
add_action( 'init', 'bp_ensure_blocksy_mobile_header_builder', 22 );

if ( ! function_exists( 'bp_render_blocksy_offcanvas_account_links' ) ) {
    function bp_render_blocksy_offcanvas_account_links() {
        ?>
        <div class="bp-blocksy-offcanvas-actions">
            <?php if ( is_user_logged_in() ) : ?>
                <a class="ct-button bp-blocksy-offcanvas-button bp-blocksy-offcanvas-button--primary" href="<?php echo esc_url( home_url( '/my-desk/' ) ); ?>">My Content</a>
                <a class="ct-button bp-blocksy-offcanvas-button" href="<?php echo esc_url( home_url( '/write/' ) ); ?>">New essay</a>
                <a class="ct-button bp-blocksy-offcanvas-button" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Sign out</a>
            <?php else : ?>
                <a class="ct-button bp-blocksy-offcanvas-button bp-blocksy-offcanvas-button--primary" href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Sign in</a>
                <a class="ct-button bp-blocksy-offcanvas-button" href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create account</a>
            <?php endif; ?>
        </div>
        <?php
    }
}
add_action( 'blocksy:header:offcanvas:mobile:bottom', 'bp_render_blocksy_offcanvas_account_links', 20 );

/* =========================================================================
 * Comment form copy
 * ====================================================================== */
add_filter( 'comment_form_defaults', function ( $defaults ) {
    $defaults['title_reply']          = 'Join the conversation';
    $defaults['title_reply_to']       = 'Reply to %s';
    $defaults['comment_notes_before'] = '<p style="color:var(--ink-3); font-size:13px; margin-bottom:14px;">Your email stays private. Comments are reviewed before they appear publicly.</p>';
    $defaults['label_submit']         = 'Post comment';
    $defaults['submit_button']        = '<button name="%1$s" type="submit" id="%2$s" class="bp-btn bp-btn--accent %3$s">%4$s</button>';
    return $defaults;
} );

/* =========================================================================
 * Excerpt tweaks
 * ====================================================================== */
add_filter( 'excerpt_length', function () { return 26; } );
add_filter( 'excerpt_more',   function () { return '...'; } );

add_filter( 'the_content', function ( $content ) {
    if ( is_admin() || is_feed() ) {
        return $content;
    }

    return preg_replace_callback(
        '/\[([^\]\n]+)\]\((https?:\/\/[^\s)]+)\)/',
        function ( $matches ) {
            return sprintf(
                '<a class="bp-markdown-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                esc_url( $matches[2] ),
                esc_html( $matches[1] )
            );
        },
        $content
    );
}, 8 );

/* =========================================================================
 * Body classes — used by the CSS to enable home-specific effects
 * ====================================================================== */
add_filter( 'body_class', function ( $classes ) {
    $classes[] = 'bp-platform';
    if ( is_user_logged_in() ) { $classes[] = 'bp-logged-in'; }
    if ( is_front_page() )     { $classes[] = 'home'; }
    return $classes;
} );

add_filter( 'document_title_parts', function ( $title ) {
    if ( is_front_page() ) {
        $title['title'] = 'Aperture';
        unset( $title['site'], $title['tagline'] );
        return $title;
    }

    $title['site'] = 'Aperture';
    return $title;
} );

add_filter( 'pre_option_blogname', function () {
    return 'Aperture';
} );

/* =========================================================================
 * Helpers used by the templates
 * ====================================================================== */

if ( ! function_exists( 'bp_brand_name' ) ) {
    /**
     * The brand string. Falls back to the configured site title.
     */
    function bp_brand_name() {
        return 'Aperture';
    }
}

if ( ! function_exists( 'bp_aperture_cover_url' ) ) {
    function bp_aperture_cover_url( $file ) {
        return BP_CHILD_URI . '/assets/images/aperture-covers/' . ltrim( $file, '/' );
    }
}

if ( ! function_exists( 'bp_aperture_demo_stories' ) ) {
    function bp_aperture_demo_stories() {
        return [
            [
                'title'    => 'The Art of Disappearing',
                'category' => 'Design',
                'excerpt'  => 'Why the best products feel as if they were never designed at all.',
                'author'   => 'Anna Whitfield',
                'date'     => '2026-05-20',
                'cover'    => 'abstract-waves.png',
                'variant'  => 'waves',
                'content'  => "The finest interfaces make their decisions quietly. They remove their scaffolding from view, then leave the reader with a clear path and a little room to breathe.\n\nDisappearing is not a lack of design. It is design that has earned enough confidence to stop announcing itself.",
            ],
            [
                'title'    => 'Silicon and Soul',
                'category' => 'Intelligence',
                'excerpt'  => 'On machines that understand us before we speak.',
                'author'   => 'Elias Park',
                'date'     => '2026-05-15',
                'cover'    => 'robot-chair.png',
                'variant'  => 'robot',
                'content'  => "Every useful machine eventually becomes a question about care. What should it notice, what should it ignore, and when should it step back?\n\nThe answer is not more noise. It is a quieter relationship between intent, context, and trust.",
            ],
            [
                'title'    => 'The Future is Quiet',
                'category' => 'Innovation',
                'excerpt'  => 'On the senses that great products restore.',
                'author'   => 'Sara Lindgren',
                'date'     => '2026-04-30',
                'cover'    => 'earth-night.png',
                'variant'  => 'earth',
                'content'  => "Progress does not always arrive as spectacle. Sometimes it looks like lower latency, fewer interruptions, and tools that ask less from the person using them.\n\nThe future worth building may be the one that gives attention back.",
            ],
            [
                'title'    => 'Glass, Light, Intention',
                'category' => 'Design',
                'excerpt'  => 'The materials of modern computing are barely materials at all.',
                'author'   => 'Yuki Tanaka',
                'date'     => '2026-04-12',
                'cover'    => 'liquid-glass.png',
                'variant'  => 'liquid',
                'content'  => "Screens ask us to believe in surfaces that are not really there. Glass becomes a desk, a studio, a memory, a page.\n\nThe craft is in making those surfaces feel honest.",
            ],
            [
                'title'    => 'A Vocabulary for Machines',
                'category' => 'Culture',
                'excerpt'  => 'What language do we owe to the things that listen?',
                'author'   => 'Noor Abadi',
                'date'     => '2026-03-28',
                'cover'    => 'vr-culture.png',
                'variant'  => 'vr',
                'content'  => "Every new medium teaches us new manners. The question is not only what machines can understand, but what kind of language keeps humans legible to each other.\n\nCulture is the interface beneath the interface.",
            ],
            [
                'title'    => 'Slow Software',
                'category' => 'Technology',
                'excerpt'  => 'In praise of products that wait for you to think.',
                'author'   => 'Henrik Vasse',
                'date'     => '2026-03-10',
                'cover'    => 'orange-architecture.png',
                'variant'  => 'architecture',
                'content'  => "Speed is useful until it becomes a demand. Slow software is not sluggish; it is software with pauses in the right places.\n\nA humane tool knows the difference between responsiveness and pressure.",
            ],
        ];
    }
}

if ( ! function_exists( 'bp_aperture_story_cards' ) ) {
    function bp_aperture_post_story_card( $post ) {
        $post = get_post( $post );
        if ( ! $post ) {
            return null;
        }

        $categories = get_the_category( $post->ID );
        $category   = ! empty( $categories ) ? $categories[0]->name : 'Essay';
        $demo_author = get_post_meta( $post->ID, '_bp_demo_author', true );
        $demo_date   = get_post_meta( $post->ID, '_bp_demo_date', true );
        $cover_url   = get_the_post_thumbnail_url( $post, 'large' ) ?: get_post_meta( $post->ID, '_bp_external_cover_url', true );
        $demo_cover  = get_post_meta( $post->ID, '_bp_demo_cover', true );

        if ( ! $cover_url && $demo_cover ) {
            $cover_url = bp_aperture_cover_url( $demo_cover );
        }

        return [
            'id'        => $post->ID,
            'title'     => get_the_title( $post ),
            'category'  => $category,
            'excerpt'   => wp_trim_words( get_the_excerpt( $post ), 24, '...' ),
            'author'    => $demo_author ?: get_the_author_meta( 'display_name', $post->post_author ),
            'date'      => $demo_date ?: get_the_date( 'Y-m-d', $post ),
            'url'       => get_permalink( $post ),
            'cover_url' => $cover_url,
        ];
    }

    function bp_aperture_story_cards() {
        $posts = get_posts( [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 24,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );

        $stories = [];
        foreach ( $posts as $post ) {
            $story = bp_aperture_post_story_card( $post );
            if ( $story ) {
                $stories[] = $story;
            }
        }

        return $stories;
    }
}

if ( ! function_exists( 'bp_aperture_story_categories' ) ) {
    function bp_aperture_story_categories( $stories = [], $include_all = false ) {
        $seen = [];

        foreach ( $stories as $story ) {
            $category = isset( $story['category'] ) ? trim( (string) $story['category'] ) : '';
            if ( $category === '' ) {
                continue;
            }

            $seen[ strtolower( $category ) ] = $category;
        }

        $categories = array_values( $seen );
        natcasesort( $categories );
        $categories = array_values( $categories );

        if ( $include_all ) {
            array_unshift( $categories, 'All' );
        }

        return $categories;
    }
}

if ( ! function_exists( 'bp_ensure_aperture_demo_content' ) ) {
    function bp_ensure_aperture_demo_content() {
        if ( get_option( 'bp_aperture_demo_seed_version' ) === BP_CHILD_VERSION ) {
            return;
        }

        $admin = get_users( [
            'role'   => 'administrator',
            'number' => 1,
            'fields' => [ 'ID' ],
        ] );
        $author_id = ! empty( $admin ) ? (int) $admin[0]->ID : 1;

        $term_descriptions = [
            'Design'       => 'Interfaces, visual systems, typography and craft.',
            'Technology'   => 'Software, systems and the patient work of building.',
            'Innovation'   => 'New ideas that get quieter as they mature.',
            'Culture'      => 'Language, media and the habits that shape tools.',
            'Intelligence' => 'AI, machine behavior and human judgment.',
        ];

        foreach ( $term_descriptions as $name => $description ) {
            $term = get_term_by( 'name', $name, 'category' );
            if ( ! $term ) {
                $created = wp_insert_term( $name, 'category', [ 'description' => $description ] );
                if ( ! is_wp_error( $created ) ) {
                    update_term_meta( $created['term_id'], '_bp_term_status', 'approved' );
                }
            } else {
                update_term_meta( $term->term_id, '_bp_term_status', 'approved' );
                wp_update_term( $term->term_id, 'category', [ 'description' => $description ] );
            }
        }

        foreach ( bp_aperture_demo_stories() as $story ) {
            $existing = get_page_by_title( $story['title'], OBJECT, 'post' );
            $post_data = [
                'post_title'   => $story['title'],
                'post_excerpt' => $story['excerpt'],
                'post_content' => $story['content'],
                'post_status'  => 'publish',
                'post_type'    => 'post',
                'post_author'  => $author_id,
                'post_date'    => $story['date'] . ' 10:00:00',
            ];

            if ( $existing ) {
                $post_id = $existing->ID;
            } else {
                $post_id = wp_insert_post( $post_data, true );
            }

            if ( ! $post_id || is_wp_error( $post_id ) ) {
                continue;
            }

            $term = get_term_by( 'name', $story['category'], 'category' );
            if ( $term ) {
                wp_set_post_categories( $post_id, [ (int) $term->term_id ] );
            }

            update_post_meta( $post_id, '_bp_demo_author', $story['author'] );
            update_post_meta( $post_id, '_bp_demo_date', $story['date'] );
            update_post_meta( $post_id, '_bp_demo_cover', $story['cover'] );
            update_post_meta( $post_id, '_bp_cover_variant', $story['variant'] );
        }

        update_option( 'bp_aperture_demo_seed_version', BP_CHILD_VERSION );
    }
}
add_action( 'init', 'bp_ensure_aperture_demo_content', 22 );

if ( ! function_exists( 'bp_ensure_aperture_demo_user' ) ) {
    function bp_ensure_aperture_demo_user() {
        if ( get_option( 'bp_aperture_demo_user_version' ) === BP_CHILD_VERSION && email_exists( 'demo@aperture.app' ) ) {
            return;
        }

        $user = get_user_by( 'email', 'demo@aperture.app' );
        if ( ! $user ) {
            $user_id = wp_insert_user( [
                'user_login'   => 'demo_reader',
                'user_pass'    => 'demo1234',
                'user_email'   => 'demo@aperture.app',
                'display_name' => 'Demo Reader',
                'first_name'   => 'Demo',
                'last_name'    => 'Reader',
                'role'         => 'contributor',
            ] );
            if ( is_wp_error( $user_id ) ) {
                return;
            }
        } else {
            wp_update_user( [
                'ID'           => $user->ID,
                'display_name' => 'Demo Reader',
                'role'         => 'contributor',
            ] );
        }

        update_option( 'bp_aperture_demo_user_version', BP_CHILD_VERSION );
    }
}
add_action( 'init', 'bp_ensure_aperture_demo_user', 23 );

if ( ! function_exists( 'bp_ensure_aperture_demo_comments' ) ) {
    function bp_ensure_aperture_demo_comments() {
        if ( get_option( 'bp_aperture_demo_comments_version' ) === BP_CHILD_VERSION ) {
            return;
        }

        $comments_by_story = [
            'Silicon and Soul' => [
                [
                    'author'  => 'Maya Chen',
                    'email'   => 'maya.reader@example.com',
                    'content' => 'The line about machines knowing when to step back really lands. It makes the whole AI conversation feel less flashy and more humane.',
                ],
                [
                    'author'  => 'Jon Bell',
                    'email'   => 'jon.reader@example.com',
                    'content' => 'I like how this frames trust as a design material. Would read a longer follow-up on what quiet AI looks like inside everyday tools.',
                ],
            ],
            'The Future is Quiet' => [
                [
                    'author'  => 'Elena Petrova',
                    'email'   => 'elena.reader@example.com',
                    'content' => 'This is exactly the product direction I wish more teams took: less interruption, more room for attention.',
                ],
            ],
            'Slow Software' => [
                [
                    'author'  => 'Theo Martin',
                    'email'   => 'theo.reader@example.com',
                    'content' => 'Responsiveness and pressure is such a useful distinction. The piece makes slow feel intentional, not nostalgic.',
                ],
            ],
        ];

        foreach ( $comments_by_story as $title => $comments ) {
            $post = get_page_by_title( $title, OBJECT, 'post' );
            if ( ! $post ) {
                continue;
            }

            foreach ( $comments as $comment ) {
                $existing = get_comments( [
                    'post_id'      => $post->ID,
                    'author_email' => $comment['email'],
                    'count'        => true,
                ] );

                if ( $existing ) {
                    continue;
                }

                wp_insert_comment( [
                    'comment_post_ID'      => $post->ID,
                    'comment_author'       => $comment['author'],
                    'comment_author_email' => $comment['email'],
                    'comment_content'      => $comment['content'],
                    'comment_approved'     => 1,
                    'comment_date'         => current_time( 'mysql' ),
                    'comment_date_gmt'     => current_time( 'mysql', 1 ),
                ] );
            }

            wp_update_comment_count_now( $post->ID );
        }

        update_option( 'bp_aperture_demo_comments_version', BP_CHILD_VERSION );
    }
}
add_action( 'init', 'bp_ensure_aperture_demo_comments', 24 );

if ( ! function_exists( 'bp_get_post_views_count' ) ) {
    function bp_get_post_views_count( $post_id ) {
        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return 0;
        }

        $plugin_views = function_exists( 'pvc_get_post_views' ) ? (int) pvc_get_post_views( $post_id ) : 0;
        $legacy_views = (int) get_post_meta( $post_id, '_bp_views', true );

        return max( $plugin_views, $legacy_views );
    }
}

if ( ! function_exists( 'bp_get_post_likes_count' ) ) {
    function bp_get_post_likes_count( $post_id ) {
        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return 0;
        }

        $plugin_likes = function_exists( 'wp_ulike_get_post_likes' ) ? (int) wp_ulike_get_post_likes( $post_id ) : 0;
        $legacy_likes = (int) get_post_meta( $post_id, '_bp_likes', true );

        return max( $plugin_likes, $legacy_likes );
    }
}

if ( ! function_exists( 'bp_get_user_contribution_comments' ) ) {
    /**
     * Comments authored by a user, including older comments matched by email.
     */
    function bp_get_user_contribution_comments( $user_id, $args = [] ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return [];
        }

        $defaults = [
            'status'  => 'all',
            'orderby' => 'comment_date_gmt',
            'order'   => 'DESC',
            'number'  => 0,
        ];
        $query_args = wp_parse_args( $args, $defaults );
        $limit      = isset( $query_args['number'] ) ? (int) $query_args['number'] : 0;
        $query_args['number'] = 0;

        $queries = [
            array_merge( $query_args, [ 'user_id' => (int) $user_id ] ),
        ];

        if ( ! empty( $user->user_email ) ) {
            $queries[] = array_merge( $query_args, [ 'author_email' => $user->user_email ] );
        }

        $comments = [];
        foreach ( $queries as $query ) {
            foreach ( get_comments( $query ) as $comment ) {
                if ( ! in_array( (string) $comment->comment_approved, [ '0', '1' ], true ) ) {
                    continue;
                }
                $comments[ $comment->comment_ID ] = $comment;
            }
        }

        $comments = array_values( $comments );
        usort( $comments, function ( $a, $b ) {
            return strtotime( $b->comment_date_gmt . ' GMT' ) <=> strtotime( $a->comment_date_gmt . ' GMT' );
        } );

        return $limit > 0 ? array_slice( $comments, 0, $limit ) : $comments;
    }
}

if ( ! function_exists( 'bp_default_category_blurb' ) ) {
    /**
     * If a category was created without a description, return a sensible
     * one-liner so the Categories page never shows an empty row.
     */
    function bp_default_category_blurb( $name ) {
        $defaults = [
            'Technology'         => 'AI, software, gadgets and the digital frontier.',
            'Design'             => 'Visual culture, UX, typography and craft.',
            'Science'            => 'Physics, biology, space and discoveries.',
            'Health'             => 'Wellness, nutrition, mental health and movement.',
            'Philosophy'         => 'Ethics, logic, consciousness and meaning.',
            'Politics & Society' => 'Civic life and public debate across the region.',
            'Engineering'        => 'Systems, software, and the craft of building.',
            'Culture'            => 'Cinema, literature, music, and everyday life.',
            'Business'           => 'Companies, markets, and the people behind them.',
            'Personal Essays'    => 'First-person narratives and quiet reportage.',
            'Diaspora'           => 'Stories from the global Macedonian community.',
            'Food'               => 'Recipes, restaurants, and the rituals around the table.',
            'Architecture'       => 'Buildings, streets, and the city around us.',
            'Memory'             => 'Personal histories and quiet recollections.',
            'Travel'             => 'Places, journeys and small dispatches from the road.',
        ];
        return $defaults[ $name ] ?? 'Essays filed under this section.';
    }
}

if ( ! function_exists( 'bp_to_roman' ) ) {
    /**
     * Year → Roman numerals (e.g. 2026 → MMXXVI) for the footer.
     */
    function bp_to_roman( $n ) {
        $map = [ 1000=>'M', 900=>'CM', 500=>'D', 400=>'CD', 100=>'C', 90=>'XC',
                 50=>'L',  40=>'XL', 10=>'X',  9=>'IX',   5=>'V', 4=>'IV', 1=>'I' ];
        $out = '';
        foreach ( $map as $val => $r ) {
            while ( $n >= $val ) { $out .= $r; $n -= $val; }
        }
        return $out;
    }
}

if ( ! function_exists( 'bp_render_pattern' ) ) {
    /**
     * Clean editorial fallback used when a post has no uploaded cover.
     */
    function bp_render_pattern( $seed = 0, $key = '' ) {
        $palettes = [
            [ '#2F6FD6', '#EAF2FF', '#F8FAFD' ],
            [ '#7C5CFF', '#F1EEFF', '#FAF9FF' ],
            [ '#C47A16', '#FFF2DE', '#FFF9F0' ],
            [ '#25845A', '#E8F7EF', '#F7FBF8' ],
            [ '#C94B70', '#FFEAF1', '#FFF8FA' ],
            [ '#4F62B5', '#EBEEFF', '#F8F9FF' ],
        ];
        $idx = abs( crc32( (string) $seed . $key ) ) % count( $palettes );
        list( $accent, $soft, $paper ) = $palettes[ $idx ];
        $uid = $idx . '-' . absint( $seed ) . '-' . substr( md5( $key ), 0, 8 );
        ?>
        <svg viewBox="0 0 1200 620" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:100%;">
            <defs>
                <linearGradient id="bp-cover-<?php echo esc_attr( $uid ); ?>" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="<?php echo esc_attr( $paper ); ?>"/>
                    <stop offset=".62" stop-color="<?php echo esc_attr( $soft ); ?>"/>
                    <stop offset="1" stop-color="#ffffff"/>
                </linearGradient>
            </defs>
            <rect width="1200" height="620" fill="url(#bp-cover-<?php echo esc_attr( $uid ); ?>)"/>
            <circle cx="1050" cy="80" r="260" fill="<?php echo esc_attr( $accent ); ?>" opacity=".10"/>
            <circle cx="160" cy="540" r="230" fill="<?php echo esc_attr( $accent ); ?>" opacity=".08"/>
            <rect x="92" y="86" width="1016" height="448" rx="44" fill="#fff" opacity=".74"/>
            <rect x="92" y="86" width="1016" height="448" rx="44" fill="none" stroke="#182131" stroke-opacity=".08"/>
            <g opacity=".92">
                <rect x="146" y="140" width="176" height="22" rx="11" fill="<?php echo esc_attr( $accent ); ?>" opacity=".17"/>
                <rect x="146" y="190" width="420" height="24" rx="12" fill="#172033" opacity=".16"/>
                <rect x="146" y="232" width="330" height="16" rx="8" fill="#172033" opacity=".10"/>
                <rect x="146" y="262" width="385" height="16" rx="8" fill="#172033" opacity=".08"/>
                <rect x="146" y="404" width="58" height="58" rx="29" fill="#172033" opacity=".13"/>
                <rect x="224" y="414" width="135" height="16" rx="8" fill="#172033" opacity=".16"/>
                <rect x="224" y="444" width="190" height="14" rx="7" fill="#172033" opacity=".08"/>
            </g>
            <g transform="translate(760 144)">
                <rect width="252" height="252" rx="34" fill="<?php echo esc_attr( $accent ); ?>" opacity=".13"/>
                <path d="M75 165V88h21l31 43 31-43h21v77h-22v-42l-24 31h-13l-24-31v42H75Z" fill="<?php echo esc_attr( $accent ); ?>" opacity=".70"/>
                <path d="M66 72c35-22 85-22 120 0M66 180c35 22 85 22 120 0" fill="none" stroke="<?php echo esc_attr( $accent ); ?>" stroke-width="8" stroke-linecap="round" opacity=".28"/>
            </g>
            <g stroke="#172033" stroke-opacity=".055" stroke-width="2">
                <line x1="92" y1="328" x2="1108" y2="328"/>
                <line x1="664" y1="86" x2="664" y2="534"/>
            </g>
        </svg>
        <?php
    }
}

if ( ! function_exists( 'bp_render_cover_media' ) ) {
    /**
     * Render an uploaded cover when one exists, otherwise use the selected
     * generated Meridian cover style.
     */
    function bp_render_cover_media( $post_id = 0, $key = '', $size = 'large' ) {
        $post_id = $post_id ? absint( $post_id ) : get_the_ID();
        if ( ! $post_id ) {
            return;
        }

        if ( has_post_thumbnail( $post_id ) ) {
            echo get_the_post_thumbnail( $post_id, $size, [
                'loading'  => 'lazy',
                'decoding' => 'async',
            ] );
            return;
        }

        $external_cover = get_post_meta( $post_id, '_bp_external_cover_url', true );
        if ( $external_cover ) {
            printf(
                '<img src="%s" alt="" loading="lazy" decoding="async">',
                esc_url( $external_cover )
            );
            return;
        }

        $demo_cover = get_post_meta( $post_id, '_bp_demo_cover', true );
        if ( $demo_cover ) {
            printf(
                '<img src="%s" alt="" loading="lazy" decoding="async">',
                esc_url( bp_aperture_cover_url( $demo_cover ) )
            );
            return;
        }

        $variant = get_post_meta( $post_id, '_bp_cover_variant', true );
        bp_render_pattern( $post_id, trim( $key . '-' . $variant, '-' ) );
    }
}

add_action( 'login_enqueue_scripts', function () {
    $logo = bp_brand_icon_url();
    ?>
    <style>
        :root {
            --bp-admin-blue: #0071e3;
            --bp-admin-ink: #1d1d1f;
            --bp-admin-muted: #6e6e73;
            --bp-admin-line: rgba(0, 0, 0, .1);
        }
        body.login {
            min-height: 100vh;
            background:
                linear-gradient(135deg, rgba(0, 113, 227, .12), rgba(175, 82, 222, .10) 42%, rgba(255, 255, 255, 0) 68%),
                #f5f5f7;
            color: var(--bp-admin-ink);
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Helvetica Neue", Arial, sans-serif;
        }
        .login h1 a {
            width: 56px;
            height: 56px;
            background: url('<?php echo esc_url( $logo ); ?>') center / contain no-repeat;
            border-radius: 14px;
            box-shadow: 0 18px 45px rgba(0, 113, 227, .18);
        }
        .login form {
            border: 1px solid var(--bp-admin-line);
            border-radius: 8px;
            box-shadow: 0 24px 80px rgba(0, 0, 0, .08);
        }
        .login label { color: var(--bp-admin-muted); font-size: 13px; }
        .login form .input,
        .login input[type="text"] {
            border-color: var(--bp-admin-line);
            border-radius: 8px;
            min-height: 44px;
            box-shadow: none;
        }
        .wp-core-ui .button-primary {
            background: var(--bp-admin-blue);
            border-color: var(--bp-admin-blue);
            border-radius: 999px;
            padding: 0 18px;
        }
        .login #backtoblog a,
        .login #nav a { color: var(--bp-admin-muted); }
        .login #backtoblog a:hover,
        .login #nav a:hover { color: var(--bp-admin-blue); }
    </style>
    <?php
} );
