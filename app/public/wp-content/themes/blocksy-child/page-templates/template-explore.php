<?php
/**
 * Template Name: Explore
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$stories = bp_aperture_story_cards();
$categories = bp_aperture_story_categories( $stories, true );
?>

<main class="ap-page ap-explore">
    <section class="ap-hero ap-hero--left ap-hero--explore">
        <div class="ap-hero__glow" aria-hidden="true"></div>
        <div class="bp-shell">
            <div class="ap-hero__content reveal">
                <span class="ap-eyebrow">Explore</span>
                <h1>Every story, one search away.</h1>
                <p class="ap-explore-intro">
                    <span>Filter by discipline, search by name, or simply scroll.</span>
                    <span>The library grows every week.</span>
                </p>
                <label class="ap-search" for="aperture-explore-search">
                    <span class="ap-search__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    </span>
                    <input id="aperture-explore-search" class="ap-search__input" type="search" placeholder="Search stories, authors, ideas..." autocomplete="off" data-aperture-search>
                </label>
            </div>
        </div>
    </section>

    <section class="bp-shell ap-explore__body">
        <div class="ap-filterbar reveal">
            <div class="ap-filterbar__chips" role="list" aria-label="<?php esc_attr_e( 'Story filters', 'blocksy-child' ); ?>">
                <?php foreach ( $categories as $index => $category ) : ?>
                    <button type="button" class="<?php echo $index === 0 ? 'is-active' : ''; ?>" data-aperture-filter="<?php echo esc_attr( strtolower( $category ) ); ?>">
                        <?php echo esc_html( $category ); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <span><?php echo intval( count( $stories ) ); ?> stories</span>
        </div>

        <div class="ap-story-grid ap-story-grid--explore" data-aperture-grid>
            <?php foreach ( $stories as $index => $story ) : ?>
                <article class="ap-story-card reveal" data-delay="<?php echo esc_attr( 60 + $index * 40 ); ?>" data-category="<?php echo esc_attr( strtolower( $story['category'] ) ); ?>" data-search="<?php echo esc_attr( strtolower( $story['title'] . ' ' . $story['author'] . ' ' . $story['excerpt'] . ' ' . $story['category'] ) ); ?>">
                    <a class="ap-story-card__link" href="<?php echo esc_url( $story['url'] ); ?>" aria-label="<?php echo esc_attr( $story['title'] ); ?>"></a>
                    <div class="ap-story-card__image">
                        <?php if ( ! empty( $story['id'] ) ) : ?>
                            <?php bp_render_cover_media( $story['id'], 'explore-story', 'large' ); ?>
                        <?php elseif ( ! empty( $story['cover_url'] ) ) : ?>
                            <img src="<?php echo esc_url( $story['cover_url'] ); ?>" alt="">
                        <?php endif; ?>
                    </div>
                    <div class="ap-story-card__body">
                        <span class="ap-card-kicker"><?php echo esc_html( $story['category'] ); ?></span>
                        <h3><?php echo esc_html( $story['title'] ); ?></h3>
                        <p><?php echo esc_html( $story['excerpt'] ); ?></p>
                        <small><?php echo esc_html( $story['author'] ); ?> <span>-</span> <?php echo esc_html( $story['date'] ); ?></small>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="ap-empty-state" data-aperture-empty hidden>
            <h2>Nothing matched.</h2>
            <p>Try a broader keyword or switch back to all stories.</p>
        </div>
    </section>
</main>

<?php get_footer(); ?>
