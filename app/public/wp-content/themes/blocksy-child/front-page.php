<?php
/**
 * Aperture home page.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$stories = bp_aperture_story_cards();
$featured = $stories[0] ?? null;
$latest = array_slice( $stories, 1, 6 );
$categories = bp_aperture_story_categories( $stories );
?>

<main class="ap-page ap-home">
    <section class="ap-hero ap-hero--center">
        <div class="ap-hero__glow" aria-hidden="true"></div>
        <div class="bp-shell">
            <div class="ap-hero__content reveal">
                <h1>Stories,<br> beautifully shared.</h1>
                <p>A curated collection of essays on design, intelligence, and the future of how we make things.</p>
                <div class="ap-hero__actions">
                    <a class="bp-btn bp-btn--dark" href="<?php echo esc_url( home_url( '/explore/' ) ); ?>">Explore stories</a>
                    <a class="bp-btn bp-btn--gradient" href="<?php echo esc_url( home_url( '/write/' ) ); ?>">Submit a story</a>
                </div>
            </div>
        </div>
    </section>

    <?php if ( $featured ) : ?>
        <section class="bp-shell ap-feature-section">
            <a class="ap-feature-card reveal" href="<?php echo esc_url( $featured['url'] ); ?>" data-tilt>
                <?php if ( ! empty( $featured['id'] ) ) : ?>
                    <?php bp_render_cover_media( $featured['id'], 'home-feature', 'large' ); ?>
                <?php elseif ( ! empty( $featured['cover_url'] ) ) : ?>
                    <img src="<?php echo esc_url( $featured['cover_url'] ); ?>" alt="">
                <?php endif; ?>
                <span class="ap-feature-card__shade" aria-hidden="true"></span>
                <span class="ap-card-kicker">Featured - <?php echo esc_html( $featured['category'] ); ?></span>
                <h2><?php echo esc_html( $featured['title'] ); ?></h2>
                <p><?php echo esc_html( $featured['excerpt'] ); ?></p>
            </a>
        </section>
    <?php endif; ?>

    <section class="bp-shell ap-latest">
        <div class="ap-section-head reveal">
            <div>
                <span class="ap-eyebrow">Latest</span>
                <h2>Fresh from the editors</h2>
            </div>
        </div>

        <div class="ap-story-grid ap-story-grid--home">
            <?php foreach ( $latest as $index => $story ) : ?>
                <article class="ap-story-card reveal" data-delay="<?php echo esc_attr( 70 + $index * 45 ); ?>" data-category="<?php echo esc_attr( strtolower( $story['category'] ) ); ?>">
                    <a class="ap-story-card__link" href="<?php echo esc_url( $story['url'] ); ?>" aria-label="<?php echo esc_attr( $story['title'] ); ?>"></a>
                    <div class="ap-story-card__image">
                        <?php if ( ! empty( $story['id'] ) ) : ?>
                            <?php bp_render_cover_media( $story['id'], 'home-story', 'large' ); ?>
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

        <div class="ap-latest__actions reveal">
            <a class="bp-btn bp-btn--dark bp-btn--lg ap-latest__button" href="<?php echo esc_url( home_url( '/explore/' ) ); ?>">
                <span class="ap-button-shimmer" data-shimmer-text="View all">View all</span>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
    </section>

    <section class="bp-shell ap-interest reveal">
        <div>
            <span class="ap-eyebrow">Categories</span>
            <h2>Wander by interest.</h2>
            <p>Five evolving disciplines. Many more to come, proposed by the community, approved by editors.</p>
            <div class="ap-interest__pills">
                <?php foreach ( $categories as $category ) : ?>
                    <a href="<?php echo esc_url( home_url( '/explore/?cat_name=' . sanitize_title( $category ) ) ); ?>"><?php echo esc_html( $category ); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
