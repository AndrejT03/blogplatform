<?php
/**
 * Default page template.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>

<main class="apple-page">
    <div class="bp-shell">
        <?php while ( have_posts() ) : the_post(); ?>
            <header class="apple-page__head reveal">
                <h1><?php the_title(); ?></h1>
            </header>

            <article class="apple-page__body reveal" data-delay="100">
                <div class="apple-page__content">
                    <?php the_content(); ?>
                </div>
            </article>

            <?php if ( comments_open() || get_comments_number() ) : ?>
                <div class="apple-comments reveal">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>
