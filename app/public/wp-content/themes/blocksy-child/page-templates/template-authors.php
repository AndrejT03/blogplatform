<?php
/**
 * Template Name: Contributors
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$authors = get_users( [
    'has_published_posts' => [ 'post' ],
    'orderby'             => 'post_count',
    'order'               => 'DESC',
] );
?>

<main class="apple-page">
    <div class="bp-shell">
        <header class="apple-page__head reveal">
            <span class="bp-eyebrow">Contributors</span>
            <h1>Writers on <?php echo esc_html( bp_brand_name() ); ?>.</h1>
            <p class="apple-archive__desc">Browse published authors and their archives.</p>
        </header>

        <?php if ( ! empty( $authors ) ) : ?>
            <section class="bp-author-grid">
                <?php $i = 0; foreach ( $authors as $author ) : $i++;
                    $count = (int) count_user_posts( $author->ID, 'post' );
                    $city  = get_user_meta( $author->ID, 'city', true );
                    ?>
                    <a class="bp-author-card reveal" data-delay="<?php echo esc_attr( min( ( $i - 1 ) * 45, 260 ) ); ?>" href="<?php echo esc_url( get_author_posts_url( $author->ID ) ); ?>">
                        <?php echo get_avatar( $author->ID, 56 ); ?>
                        <div>
                            <h3><?php echo esc_html( $author->display_name ); ?></h3>
                            <p>
                                <?php echo intval( $count ); ?> <?php echo $count === 1 ? 'essay' : 'essays'; ?>
                                <?php if ( $city ) : ?> / <?php echo esc_html( $city ); ?><?php endif; ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </section>
        <?php else : ?>
            <div class="apple-archive__empty reveal">
                <h2>No contributors yet.</h2>
                <p>Published authors will appear here after approval.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
