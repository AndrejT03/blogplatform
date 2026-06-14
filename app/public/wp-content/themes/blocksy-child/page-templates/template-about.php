<?php
/**
 * Template Name: About / Manifesto
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$cards = [
    [ 'icon' => '🤝', 'title' => 'Community-led', 'text' => 'Anyone can submit an essay, a comment, or propose a new category. Voices are gathered, not filtered by hype.' ],
    [ 'icon' => '✨', 'title' => 'Editorially curated', 'text' => 'Every contribution is reviewed by editors before publishing. Quality is the only ranking signal that matters.' ],
    [ 'icon' => '🧭', 'title' => 'Human-guided', 'text' => 'Editors surface connections between ideas - quietly, never intrusively. The reader remains in command.' ],
];

$stats = [
    [ 'icon' => '📝', 'value' => '84', 'label' => 'Essays published' ],
    [ 'icon' => '🗂️', 'value' => '5', 'label' => 'Categories' ],
    [ 'icon' => '💬', 'value' => '1.2k', 'label' => 'Conversations' ],
    [ 'icon' => '👀', 'value' => '100%', 'label' => 'Human-reviewed' ],
];

$principles = [
    [ 'Slowness is a feature.', 'We publish when something is ready, never when it is loud.' ],
    [ 'The reader is the work.', 'Layout, pace, and silence matter as much as the words.' ],
    [ 'Context helps; humans decide.', 'Useful signals can suggest structure. Taste belongs to people.' ],
];
?>

<main class="ap-page ap-about">
    <section class="ap-hero ap-hero--center ap-hero--tall">
        <div class="ap-hero__glow" aria-hidden="true"></div>
        <div class="bp-shell">
            <div class="ap-hero__content reveal">
                <span class="ap-eyebrow">About</span>
                <h1>A qui<span class="ap-gradient-text">et</span> place<br> for considered ideas.</h1>
                <p>Aperture is a community blog devoted to the craft of thinking clearly about technology, design, and what it means to build well.</p>
            </div>
        </div>
    </section>

    <section class="bp-shell ap-about-cards">
        <?php foreach ( $cards as $index => $card ) : ?>
            <article class="ap-info-card reveal" data-delay="<?php echo esc_attr( 70 + $index * 55 ); ?>">
                <span class="ap-info-card__icon" aria-hidden="true">
                    <span><?php echo esc_html( $card['icon'] ); ?></span>
                </span>
                <h2><?php echo esc_html( $card['title'] ); ?></h2>
                <p><?php echo esc_html( $card['text'] ); ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="bp-shell ap-stats-band" aria-label="Aperture in numbers">
        <?php foreach ( $stats as $index => $stat ) : ?>
            <div class="ap-stat reveal" data-delay="<?php echo esc_attr( 80 + $index * 70 ); ?>">
                <span class="ap-stat__icon" aria-hidden="true"><?php echo esc_html( $stat['icon'] ); ?></span>
                <strong class="ap-stat__value ap-shimmer-text" data-shimmer-text="<?php echo esc_attr( $stat['value'] ); ?>"><?php echo esc_html( $stat['value'] ); ?></strong>
                <span class="ap-stat__label"><?php echo esc_html( $stat['label'] ); ?></span>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="bp-shell ap-principles" id="principles">
        <span class="ap-eyebrow reveal">Principles</span>
        <h2 class="reveal" data-delay="60">What we believe.</h2>
        <div class="ap-principles__list">
            <?php foreach ( $principles as $index => $principle ) : ?>
                <article class="reveal" data-delay="<?php echo esc_attr( 90 + $index * 55 ); ?>">
                    <h3><?php echo esc_html( $principle[0] ); ?></h3>
                    <p><?php echo esc_html( $principle[1] ); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
