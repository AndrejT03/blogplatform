<?php
/**
 * Blog index.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>

<main class="apple-archive">
    <div class="bp-shell">
        <header class="apple-archive__head reveal">
            <div class="apple-archive__head-inner">
                <span class="bp-eyebrow">Library</span>
                <h1>All essays</h1>
                <p class="apple-archive__desc">Every published piece, newest first.</p>
            </div>
        </header>

        <section class="apple-archive__feed">
            <?php if ( have_posts() ) : ?>
                <div class="apple-grid">
                    <?php $i = 0; while ( have_posts() ) : the_post(); $i++;
                        $cats = get_the_category();
                        $read_time = max( 1, ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
                        ?>
                        <article class="apple-card reveal" data-delay="<?php echo esc_attr( min( $i * 45, 260 ) ); ?>">
                            <a class="apple-card__link" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>"></a>
                            <div class="apple-card__img">
                                <?php bp_render_cover_media( get_the_ID(), 'home-index', 'large' ); ?>
                            </div>
                            <div class="apple-card__content">
                                <span class="apple-card__meta">
                                    <?php echo ! empty( $cats ) ? esc_html( $cats[0]->name ) : 'Essay'; ?> / <?php echo esc_html( get_the_date( 'M j' ) ); ?>
                                </span>
                                <h3><?php the_title(); ?></h3>
                                <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '...' ) ); ?></p>
                                <span class="apple-card__author">By <?php the_author(); ?> / <?php echo intval( $read_time ); ?> min</span>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <div class="apple-archive__empty reveal">
                    <h2>Nothing published yet.</h2>
                    <p>The first essay will appear here after editorial approval.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php get_footer(); ?>
