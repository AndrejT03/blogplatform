<?php
/**
 * Single post template.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'BP_MERIDIAN_READER_CHROME' ) ) {
    define( 'BP_MERIDIAN_READER_CHROME', true );
}

get_header();

while ( have_posts() ) : the_post();
    $author_id  = get_the_author_meta( 'ID' );
    $demo_author = get_post_meta( get_the_ID(), '_bp_demo_author', true );
    $demo_date   = get_post_meta( get_the_ID(), '_bp_demo_date', true );
    $word_count = str_word_count( wp_strip_all_tags( get_the_content() ) );
    $read_time  = max( 1, ceil( $word_count / 220 ) );
    $cats       = get_the_category();
    $story_link = get_post_meta( get_the_ID(), '_bp_external_link', true );

    ?>

    <main class="meridian-single">
        <article class="bp-shell bp-shell--narrow meridian-reader">
            <a class="meridian-back-link" href="<?php echo esc_url( wp_get_referer() ?: home_url( '/explore/' ) ); ?>">← Back</a>

            <header class="meridian-single-head reveal">
                <?php if ( ! empty( $cats ) ) : ?>
                    <div class="meridian-single-meta">
                        <span><?php echo esc_html( $cats[0]->name ); ?></span>
                    </div>
                <?php endif; ?>

                <h1><?php the_title(); ?></h1>

                <div class="meridian-single-byline">
                    <a class="meridian-author-row" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( $demo_author ?: get_the_author() ); ?></a>
                    <span>·</span>
                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( $demo_date ?: get_the_date( 'Y-m-d' ) ); ?></time>
                </div>
            </header>

            <figure class="meridian-single-cover reveal" data-delay="100">
                <div class="meridian-cover meridian-cover--single" data-parallax="0.018">
                    <?php bp_render_cover_media( get_the_ID(), 'single', 'large' ); ?>
                </div>
            </figure>

            <div class="meridian-single-content reveal" data-delay="150">
                <?php the_content(); ?>
                <?php if ( $story_link ) : ?>
                    <p class="meridian-story-link">
                        <span>Source link</span>
                        <a href="<?php echo esc_url( $story_link ); ?>" target="_blank" rel="noopener"><?php echo esc_html( preg_replace( '#^https?://#i', '', $story_link ) ); ?></a>
                    </p>
                <?php endif; ?>
            </div>

            <div class="apple-comments reveal">
                <?php comments_template(); ?>
            </div>
        </article>
    </main>

<?php endwhile; ?>
<?php get_footer(); ?>
