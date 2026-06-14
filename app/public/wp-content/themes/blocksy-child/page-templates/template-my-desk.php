<?php
/**
 * Template Name: My Desk
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! is_user_logged_in() ) {
    get_header();
    ?>
    <main class="bp-page">
        <div class="bp-shell">
            <section class="bp-gate" data-trigger-signin>
                <div class="bp-gate__inner">
                    <div class="bp-eyebrow bp-eyebrow--accent reveal">My content</div>
                    <h1 class="reveal" data-delay="80">Sign in to open your dashboard.</h1>
                    <p class="reveal" data-delay="160">Drafts, reviews, published stories and reader stats live here.</p>
                    <div class="bp-gate__actions reveal" data-delay="240">
                        <a class="bp-btn bp-btn--accent bp-btn--lg" href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Sign in</a>
                        <a class="bp-btn bp-btn--ghost bp-btn--lg" href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create account</a>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <?php
    get_footer();
    return;
}

if ( ! empty( $_POST['bp_delete_post_id'] ) && check_admin_referer( 'bp_delete_post', 'bp_delete_nonce' ) ) {
    $del_id = absint( $_POST['bp_delete_post_id'] );
    $p      = get_post( $del_id );
    if ( $p && intval( $p->post_author ) === get_current_user_id() ) {
        wp_trash_post( $del_id );
    }
    wp_safe_redirect( home_url( '/my-desk/?deleted=1' ) );
    exit;
}

get_header();

$user     = wp_get_current_user();
$user_id  = get_current_user_id();
$name     = $user->display_name ?: $user->user_login;
$initials = strtoupper( substr( $name, 0, 1 ) );

$all_posts = get_posts( [
    'author'         => $user_id,
    'post_status'    => [ 'publish', 'private', 'pending', 'draft' ],
    'posts_per_page' => -1,
    'orderby'        => 'modified',
    'order'          => 'DESC',
] );

$declined_posts = get_posts( [
    'author'         => $user_id,
    'post_status'    => 'trash',
    'posts_per_page' => -1,
    'orderby'        => 'modified',
    'order'          => 'DESC',
    'meta_query'     => [
        [
            'key'     => '_bp_declined_at',
            'compare' => 'EXISTS',
        ],
    ],
] );

$desk_posts = array_merge( $all_posts, $declined_posts );
usort( $desk_posts, function ( $a, $b ) {
    $a_declined_at = get_post_status( $a ) === 'trash' ? get_post_meta( $a->ID, '_bp_declined_at', true ) : '';
    $b_declined_at = get_post_status( $b ) === 'trash' ? get_post_meta( $b->ID, '_bp_declined_at', true ) : '';
    $a_time = $a_declined_at ? strtotime( $a_declined_at ) : strtotime( $a->post_modified_gmt . ' GMT' );
    $b_time = $b_declined_at ? strtotime( $b_declined_at ) : strtotime( $b->post_modified_gmt . ' GMT' );

    return $b_time <=> $a_time;
} );

$post_metrics = [];
$published_count = 0;
$private_count = 0;
$pending_count = 0;
$draft_count = 0;
$total_reads = 0;
$total_likes = 0;
$approved_comments = 0;
$pending_comments = 0;
$received_approved_comments = 0;
$total_words = 0;
$read_time_items = 0;
$top_read_post = null;
$top_read_views = -1;
$most_discussed_post = null;
$most_discussed_count = -1;

foreach ( $all_posts as $desk_post ) {
    $status = get_post_status( $desk_post );
    if ( $status === 'publish' ) { $published_count++; }
    if ( $status === 'private' ) { $private_count++; }
    if ( $status === 'pending' ) { $pending_count++; }
    if ( $status === 'draft' )   { $draft_count++; }

    $views = bp_get_post_views_count( $desk_post->ID );
    $likes = bp_get_post_likes_count( $desk_post->ID );
    $approved_for_post = (int) get_comments( [ 'post_id' => $desk_post->ID, 'status' => 'approve', 'count' => true ] );
    $pending_for_post  = (int) get_comments( [ 'post_id' => $desk_post->ID, 'status' => 'hold', 'count' => true ] );
    $comment_total     = $approved_for_post + $pending_for_post;
    $words             = str_word_count( wp_strip_all_tags( $desk_post->post_content ) );
    $read_time         = max( 1, (int) ceil( $words / 220 ) );

    if ( in_array( $status, [ 'publish', 'private' ], true ) ) {
        $total_reads += $views;
        $total_likes += $likes;
        $total_words += $words;
        $read_time_items++;
    }

    $received_approved_comments += $approved_for_post;

    if ( $views > $top_read_views ) {
        $top_read_views = $views;
        $top_read_post  = $desk_post;
    }

    if ( $comment_total > $most_discussed_count ) {
        $most_discussed_count = $comment_total;
        $most_discussed_post  = $desk_post;
    }

    $post_metrics[ $desk_post->ID ] = [
        'views'            => $views,
        'likes'            => $likes,
        'approved_comments'=> $approved_for_post,
        'pending_comments' => $pending_for_post,
        'comments'         => $comment_total,
        'read_time'        => $read_time,
        'words'            => $words,
    ];
}

$all_comments = bp_get_user_contribution_comments( $user_id );
$approved_comments = count( array_filter( $all_comments, function ( $comment ) {
    return (string) $comment->comment_approved === '1';
} ) );
$pending_comments = count( array_filter( $all_comments, function ( $comment ) {
    return (string) $comment->comment_approved === '0';
} ) );
$recent_comments = array_slice( $all_comments, 0, 5 );

$total_posts = count( $desk_posts );
$published_total = $published_count + $private_count;
$total_comments = $approved_comments + $pending_comments;
$avg_read_time = $read_time_items ? max( 1, (int) ceil( ( $total_words / $read_time_items ) / 220 ) ) : 0;
$engagement_rate = $total_reads > 0 ? round( ( ( $total_likes + $received_approved_comments ) / $total_reads ) * 100, 1 ) : null;

$suggested_terms = get_terms( [
    'taxonomy'   => 'category',
    'hide_empty' => false,
    'meta_query' => [
        [
            'key'   => '_bp_term_suggester',
            'value' => $user_id,
        ],
    ],
] );
$suggestion_count = is_wp_error( $suggested_terms ) ? 0 : count( $suggested_terms );

$declined_count = count( $declined_posts );

$latest_post = $desk_posts ? $desk_posts[0] : null;
$member_year = $user->user_registered ? mysql2date( 'Y', $user->user_registered ) : date_i18n( 'Y' );
$overview_count = $total_posts + $total_comments + $suggestion_count;

$status_label = function ( $status ) {
    if ( in_array( $status, [ 'publish', 'private' ], true ) ) {
        return 'Published';
    }
    if ( $status === 'pending' ) {
        return 'In review';
    }
    if ( $status === 'draft' ) {
        return 'Draft';
    }
    return 'Declined';
};

$status_class = function ( $status ) {
    if ( in_array( $status, [ 'publish', 'private' ], true ) ) {
        return 'published';
    }
    if ( $status === 'pending' || $status === 'draft' ) {
        return 'review';
    }
    return 'declined';
};

$post_url = function ( $post ) {
    $status = get_post_status( $post );
    if ( in_array( $status, [ 'publish', 'private' ], true ) ) {
        return get_permalink( $post );
    }
    if ( $status === 'trash' ) {
        return add_query_arg( 'edit', $post->ID, home_url( '/write/' ) );
    }
    return add_query_arg( 'edit', $post->ID, home_url( '/write/' ) );
};

$latest_url = $latest_post ? $post_url( $latest_post ) : home_url( '/write/' );
$latest_status = $latest_post ? get_post_status( $latest_post ) : '';
$latest_metrics = $latest_post && isset( $post_metrics[ $latest_post->ID ] ) ? $post_metrics[ $latest_post->ID ] : [ 'comments' => 0 ];
$latest_excerpt = $latest_post
    ? ( $latest_status === 'trash' ? 'This blog was declined by the editorial team.' : wp_trim_words( $latest_post->post_excerpt ?: wp_strip_all_tags( $latest_post->post_content ), 14, '...' ) )
    : 'Start your first blog and it will appear here.';

$activity_items = [];
foreach ( $desk_posts as $desk_post ) {
    $status      = get_post_status( $desk_post );
    $declined_at = $status === 'trash' ? get_post_meta( $desk_post->ID, '_bp_declined_at', true ) : '';
    $activity_items[] = [
        'kind'   => 'essay',
        'time'   => $declined_at ? strtotime( $declined_at ) : strtotime( $desk_post->post_modified_gmt . ' GMT' ),
        'date'   => $declined_at ? mysql2date( 'Y-m-d', $declined_at ) : mysql2date( 'Y-m-d', $desk_post->post_modified ),
        'title'  => get_the_title( $desk_post ) ?: 'Untitled draft',
        'url'    => $post_url( $desk_post ),
        'status' => $status_label( $status ),
        'class'  => $status_class( $status ),
        'meta'   => $status === 'trash' ? 'BLOG DECLINED' : 'BLOG',
        'excerpt'=> $status === 'trash' ? 'This blog was declined by the editorial team.' : '',
    ];
}

foreach ( $recent_comments as $comment ) {
    $comment_post = get_post( $comment->comment_post_ID );
    $parent_comment = $comment->comment_parent ? get_comment( $comment->comment_parent ) : null;
    $is_reply = $parent_comment instanceof WP_Comment;
    $comment_post_title = $comment_post ? get_the_title( $comment_post ) : 'your essay';
    $comment_url = $comment_post ? get_permalink( $comment_post ) . '#comments' : '#';
    $activity_items[] = [
        'kind'   => 'conversation',
        'time'   => strtotime( $comment->comment_date_gmt . ' GMT' ),
        'date'   => mysql2date( 'Y-m-d', $comment->comment_date ),
        'title'  => ( $is_reply ? 'Reply on ' : 'Comment on ' ) . $comment_post_title,
        'url'    => $comment->comment_approved === '1' ? get_comment_link( $comment ) : $comment_url,
        'status' => $comment->comment_approved === '1' ? 'Published' : 'In review',
        'class'  => $comment->comment_approved === '1' ? 'published' : 'review',
        'meta'   => $is_reply ? 'REPLY' : 'COMMENT',
        'excerpt'=> wp_trim_words( wp_strip_all_tags( $comment->comment_content ), 22, '...' ),
        'context'=> $is_reply ? 'Reply to ' . ( $parent_comment->comment_author ?: 'Reader' ) : $comment_post_title,
    ];
}

if ( ! is_wp_error( $suggested_terms ) ) {
    foreach ( $suggested_terms as $term ) {
        $term_status = get_term_meta( $term->term_id, '_bp_term_status', true );
        $activity_items[] = [
            'kind'   => 'suggestion',
            'time'   => 0,
            'date'   => date_i18n( 'Y-m-d' ),
            'title'  => $term->name,
            'url'    => home_url( '/categories/' ),
            'status' => $term_status === 'pending' ? 'In review' : 'Published',
            'class'  => $term_status === 'pending' ? 'review' : 'published',
            'meta'   => 'SUGGESTION',
        ];
    }
}

usort( $activity_items, function ( $a, $b ) {
    return $b['time'] <=> $a['time'];
} );
$activity_items = array_slice( $activity_items, 0, 6 );
$activity_count = count( $activity_items );
?>

<main class="meridian-dashboard myspace-page" id="overview">
    <div class="myspace-shell">
        <div class="myspace-topline reveal">
            <span>My space</span>
            <a class="bp-btn bp-btn--gradient myspace-new" href="<?php echo esc_url( home_url( '/write/' ) ); ?>">New essay</a>
        </div>

        <div class="myspace-layout">
            <aside class="myspace-sidebar">
                <section class="myspace-profile reveal" aria-label="<?php esc_attr_e( 'Profile summary', 'blocksy-child' ); ?>">
                    <div class="myspace-avatar"><?php echo esc_html( $initials ); ?></div>
                    <h1><?php echo esc_html( $name ); ?></h1>
                    <a href="<?php echo esc_url( get_edit_profile_url( $user_id ) ); ?>">Edit name</a>
                    <span class="myspace-member"><span></span> Member &middot; <?php echo esc_html( $member_year ); ?></span>
                    <div class="myspace-profile__stats">
                        <span><strong><?php echo intval( $total_posts ); ?></strong>Blogs</span>
                        <span><strong><?php echo intval( $total_comments ); ?></strong>Comments</span>
                        <span><strong><?php echo intval( $suggestion_count ); ?></strong>Ideas</span>
                    </div>
                </section>

                <nav class="myspace-menu reveal" data-delay="80" aria-label="<?php esc_attr_e( 'My content filters', 'blocksy-child' ); ?>">
                    <button class="is-active" type="button" data-myspace-filter="all">
                        <span class="myspace-menu__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 11.5 12 6l7 5.5V19H5zM9 19v-5h6v5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></span>
                        Overview
                        <b><?php echo intval( $overview_count ); ?></b>
                    </button>
                    <button type="button" data-myspace-filter="essay">
                        <span class="myspace-menu__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 5h10v14H7zM9.5 9h5M9.5 12h5M9.5 15h3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
                        Blogs
                        <b><?php echo intval( $total_posts ); ?></b>
                    </button>
                    <button type="button" data-myspace-filter="conversation">
                        <span class="myspace-menu__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 7h12v8H9l-3 3z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
                        Conversations
                        <b><?php echo intval( $total_comments ); ?></b>
                    </button>
                    <button type="button" data-myspace-filter="suggestion">
                        <span class="myspace-menu__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
                        Suggestions
                        <b><?php echo intval( $suggestion_count ); ?></b>
                    </button>
                </nav>

                <section class="myspace-status reveal" data-delay="130" aria-label="<?php esc_attr_e( 'Publishing status', 'blocksy-child' ); ?>">
                    <h2>Status</h2>
                    <p><span class="myspace-dot is-published"></span>Published <strong><?php echo intval( $published_total ); ?></strong></p>
                    <p><span class="myspace-dot is-review"></span>In review <strong><?php echo intval( $pending_count + $draft_count ); ?></strong></p>
                    <p><span class="myspace-dot is-declined"></span>Declined <strong><?php echo intval( $declined_count ); ?></strong></p>
                </section>
            </aside>

            <section class="myspace-main">
                <article class="myspace-latest reveal" data-delay="90">
                    <a class="myspace-latest__media" href="<?php echo esc_url( $latest_url ); ?>" aria-label="<?php echo esc_attr( $latest_post ? get_the_title( $latest_post ) : 'New essay' ); ?>">
                        <?php if ( $latest_post ) : ?>
                            <?php bp_render_cover_media( $latest_post->ID, 'myspace-latest', 'large' ); ?>
                        <?php else : ?>
                            <?php bp_render_pattern( 0, 'myspace-empty' ); ?>
                        <?php endif; ?>
                    </a>
                    <div class="myspace-latest__body">
                        <div class="myspace-kicker">
                            <span>Latest essay</span>
                            <?php if ( $latest_post ) : ?>
                                <em class="myspace-pill is-<?php echo esc_attr( $status_class( $latest_status ) ); ?>"><?php echo esc_html( $status_label( $latest_status ) ); ?></em>
                            <?php endif; ?>
                        </div>
                        <h2><a href="<?php echo esc_url( $latest_url ); ?>"><?php echo esc_html( $latest_post ? ( get_the_title( $latest_post ) ?: 'Untitled draft' ) : 'No essays yet' ); ?></a></h2>
                        <p><?php echo esc_html( $latest_excerpt ); ?></p>
                        <div class="myspace-latest__meta">
                            <time><?php echo esc_html( $latest_post ? mysql2date( 'Y-m-d', $latest_post->post_date ) : date_i18n( 'Y-m-d' ) ); ?></time>
                            <span>&middot;</span>
                            <span><?php echo intval( $latest_metrics['comments'] ); ?> comments</span>
                        </div>
                    </div>
                </article>

                <section class="myspace-timeline reveal" id="myspace-timeline" data-delay="150">
                    <div class="myspace-section-head">
                        <span>Timeline</span>
                        <strong>Recent activity</strong>
                        <small data-myspace-entry-count><?php echo intval( $activity_count ); ?> entries</small>
                    </div>

                    <div class="myspace-activity" data-myspace-list>
                        <?php if ( $activity_items ) : ?>
                            <?php foreach ( $activity_items as $item ) : ?>
                                <a class="myspace-activity__item" href="<?php echo esc_url( $item['url'] ); ?>" data-myspace-item data-myspace-kind="<?php echo esc_attr( $item['kind'] ); ?>">
                                    <span class="myspace-activity__dot" aria-hidden="true"></span>
                                    <span class="myspace-activity__card">
                                        <span class="myspace-activity__meta"><?php echo esc_html( $item['meta'] ); ?> &middot; <?php echo esc_html( $item['date'] ); ?></span>
                                        <strong><?php echo esc_html( $item['title'] ); ?></strong>
                                        <?php if ( ! empty( $item['excerpt'] ) ) : ?>
                                            <span class="myspace-activity__excerpt"><?php echo esc_html( $item['excerpt'] ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $item['context'] ) ) : ?>
                                            <span class="myspace-activity__context"><?php echo esc_html( $item['context'] ); ?></span>
                                        <?php endif; ?>
                                        <em class="myspace-pill is-<?php echo esc_attr( $item['class'] ); ?>"><?php echo esc_html( $item['status'] ); ?></em>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="myspace-activity__empty" data-myspace-item data-myspace-kind="essay">
                                <span class="myspace-activity__dot" aria-hidden="true"></span>
                                <span class="myspace-activity__card">
                                    <span class="myspace-activity__meta">Essay &middot; <?php echo esc_html( date_i18n( 'Y-m-d' ) ); ?></span>
                                    <strong>No activity yet</strong>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button class="myspace-view-all" type="button" data-myspace-filter="all">View everything &rarr;</button>
                </section>
            </section>
        </div>
    </div>
</main>

<?php get_footer(); ?>
