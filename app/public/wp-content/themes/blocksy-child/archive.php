<?php
/**
 * Archive template.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

if ( is_category() ) {
    $arch_title = single_cat_title( '', false );
    $arch_desc  = category_description();
    $eyebrow    = 'Category';
} elseif ( is_tag() ) {
    $arch_title = '#' . single_tag_title( '', false );
    $arch_desc  = tag_description();
    $eyebrow    = 'Tag';
} elseif ( is_author() ) {
    $arch_title = get_the_author();
    $arch_desc  = get_the_author_meta( 'description' );
    $eyebrow    = 'Author';
} elseif ( is_date() ) {
    $arch_title = single_month_title( ' ', false );
    $arch_desc  = '';
    $eyebrow    = 'Date';
} else {
    $arch_title = 'The Archive';
    $arch_desc  = 'Every published essay, newest first.';
    $eyebrow    = 'Archive';
}
?>

<main class="apple-archive">
    <div class="bp-shell">
        <header class="apple-archive__head reveal">
            <div class="apple-archive__head-inner">
                <span class="bp-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
                <h1><?php echo esc_html( $arch_title ); ?></h1>
                <?php if ( $arch_desc ) : ?>
                    <p class="apple-archive__desc"><?php echo wp_kses_post( $arch_desc ); ?></p>
                <?php endif; ?>
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
                                <?php bp_render_cover_media( get_the_ID(), 'archive', 'large' ); ?>
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

                <?php
                global $wp_query;
                $pagination = paginate_links( [
                    'total'     => $wp_query->max_num_pages,
                    'current'   => max( 1, get_query_var( 'paged' ) ),
                    'prev_text' => 'Previous',
                    'next_text' => 'Next',
                    'type'      => 'array',
                ] );
                if ( $pagination ) :
                    ?>
                    <nav class="apple-pagination reveal" aria-label="<?php esc_attr_e( 'Archive pagination', 'blocksy-child' ); ?>">
                        <?php foreach ( $pagination as $link ) { echo wp_kses_post( $link ); } ?>
                    </nav>
                <?php endif; ?>
            <?php else : ?>
                <div class="apple-archive__empty reveal">
                    <h2>Nothing here yet.</h2>
                    <p>Try exploring another category.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php get_footer(); ?>
