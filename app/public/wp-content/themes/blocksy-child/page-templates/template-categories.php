<?php
/**
 * Template Name: Categories
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$following_topics = [ 'technology', 'culture', 'travel' ];

$categories = get_terms( [
    'taxonomy'   => 'category',
    'hide_empty' => false,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'meta_query' => [
        'relation' => 'OR',
        [ 'key' => '_bp_term_status', 'value' => 'approved' ],
        [ 'key' => '_bp_term_status', 'compare' => 'NOT EXISTS' ],
    ],
] );

$following_count = 0;
if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
    foreach ( $categories as $term ) {
        if ( in_array( strtolower( $term->name ), $following_topics, true ) ) {
            $following_count++;
        }
    }
}

$pending = get_terms( [
    'taxonomy'   => 'category',
    'hide_empty' => false,
    'number'     => 3,
    'meta_query' => [
        [ 'key' => '_bp_term_status', 'value' => 'pending' ],
    ],
] );
?>

<main class="meridian-topics">
    <div class="bp-shell">
        <header class="meridian-page-head reveal">
            <div>
                <h1>Topics</h1>
                <p>Follow the subjects that move you.</p>
            </div>
            <button class="bp-btn bp-btn--ghost meridian-suggest-btn" type="button" data-topic-suggest-toggle>
                <span aria-hidden="true">+</span>
                Suggest a topic
            </button>
        </header>

        <form class="meridian-topic-suggest reveal" method="get" action="<?php echo esc_url( home_url( '/write/' ) ); ?>" data-topic-suggest-panel>
            <input class="bp-input" type="text" name="suggest_topic" placeholder="Topic name, e.g. Gaming or Cooking">
            <button class="bp-btn bp-btn--accent" type="submit">Continue</button>
        </form>

        <section class="meridian-review-note reveal" data-delay="80">
            <span class="meridian-review-note__icon" aria-hidden="true">i</span>
            <span>
                Pending editorial review:
                <?php if ( ! is_wp_error( $pending ) && ! empty( $pending ) ) : ?>
                    <?php
                    $pending_links = [];
                    foreach ( $pending as $term ) {
                        $pending_links[] = '<strong>' . esc_html( $term->name ) . '</strong>';
                    }
                    echo wp_kses_post( implode( ', ', $pending_links ) );
                    ?>
                    <span class="muted">&mdash; suggested by community members</span>
                <?php else : ?>
                    <strong>Gaming</strong>, <strong>Cooking</strong>
                    <span class="muted">&mdash; suggested by community members</span>
                <?php endif; ?>
            </span>
        </section>

        <div class="meridian-topic-toolbar reveal" data-delay="120">
            <label class="meridian-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" placeholder="Search topics..." data-topic-search>
            </label>
            <div class="meridian-segmented" data-topic-filter-controls>
                <button class="is-active" type="button" data-topic-filter="all">All topics</button>
                <button type="button" data-topic-filter="following">Following (<?php echo intval( $following_count ); ?>)</button>
            </div>
        </div>

        <section class="meridian-topic-grid" data-topic-grid>
            <?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
                <?php $i = 0; foreach ( $categories as $term ) : $i++;
                    $desc = $term->description ?: bp_default_category_blurb( $term->name );
                    $tone = 'tone-' . ( ( $i - 1 ) % 8 + 1 );
                    $following = in_array( strtolower( $term->name ), $following_topics, true );
                    ?>
                    <article class="meridian-topic-card reveal <?php echo esc_attr( $tone ); ?>" data-delay="<?php echo esc_attr( min( $i * 35, 240 ) ); ?>" data-topic-name="<?php echo esc_attr( strtolower( $term->name ) ); ?>" data-following="<?php echo $following ? '1' : '0'; ?>">
                        <a class="meridian-topic-card__link" href="<?php echo esc_url( get_category_link( $term ) ); ?>" aria-label="<?php echo esc_attr( $term->name ); ?>"></a>
                        <span class="meridian-topic-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="5" width="14" height="14" rx="3"/>
                                <path d="M9 9h6M9 15h6"/>
                            </svg>
                        </span>
                        <div>
                            <h2><?php echo esc_html( $term->name ); ?></h2>
                            <p><?php echo esc_html( $desc ); ?></p>
                            <div class="meridian-topic-meta">
                                <strong><?php echo intval( $term->count ); ?></strong> posts
                                <strong><?php echo number_format_i18n( max( 420, $term->count * 24 + 1180 ) ); ?></strong> followers
                            </div>
                        </div>
                        <button class="meridian-follow <?php echo $following ? 'is-following' : ''; ?>" type="button">
                            <?php echo $following ? 'Following' : 'Follow'; ?>
                        </button>
                    </article>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="bp-empty reveal">
                    <h2>No topics yet.</h2>
                    <p>They will arrive with the first wave of essays.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php get_footer(); ?>
