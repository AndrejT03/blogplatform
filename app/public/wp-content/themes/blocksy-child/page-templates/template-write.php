<?php
/**
 * Template Name: Write - Editor
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! is_user_logged_in() ) {
    get_header();
    ?>
    <main class="ap-page ap-contribute ap-contribute--guest">
        <section class="ap-hero ap-hero--center ap-contribute-guest-hero">
            <div class="ap-hero__glow" aria-hidden="true"></div>
            <div class="bp-shell">
                <div class="ap-hero__content reveal">
                    <span class="ap-eyebrow">Contribute</span>
                    <h1 class="ap-shimmer-text">Write some<span class="ap-gradient-text">thing</span><br> worth keeping.</h1>
                    <p>Contributor accounts can submit essays, suggest categories, and track editorial review.</p>
                    <div class="ap-hero__actions">
                        <a class="bp-btn bp-btn--dark" href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Sign in</a>
                        <a class="bp-btn bp-btn--ghost" href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create account</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php
    get_footer();
    return;
}

get_header();

$editing_id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
$editing    = null;
if ( $editing_id ) {
    $maybe = get_post( $editing_id );
    $is_declined_post = $maybe && $maybe->post_status === 'trash' && get_post_meta( $maybe->ID, '_bp_declined_at', true );
    if ( $maybe && intval( $maybe->post_author ) === get_current_user_id() && ( $maybe->post_status !== 'trash' || $is_declined_post ) ) {
        $editing = $maybe;
    }
}

$editing_declined = $editing && get_post_status( $editing ) === 'trash' && get_post_meta( $editing->ID, '_bp_declined_at', true );

$success_id = get_transient( 'bp_submit_success_' . get_current_user_id() );
$category_success = get_transient( 'bp_category_success_' . get_current_user_id() );
$error_msg  = get_transient( 'bp_submit_error_' . get_current_user_id() );
if ( $success_id ) { delete_transient( 'bp_submit_success_' . get_current_user_id() ); }
if ( $category_success ) { delete_transient( 'bp_category_success_' . get_current_user_id() ); }
if ( $error_msg )  { delete_transient( 'bp_submit_error_' . get_current_user_id() ); }

$preset_title   = $editing ? $editing->post_title : '';
$preset_excerpt = $editing ? $editing->post_excerpt : '';
$preset_content = $editing ? $editing->post_content : '';
$preset_cat     = 0;
if ( $editing ) {
    $tax = get_the_category( $editing->ID );
    if ( ! empty( $tax ) ) { $preset_cat = $tax[0]->term_id; }
}
$preset_link = $editing ? get_post_meta( $editing->ID, '_bp_external_link', true ) : '';
$preset_external_cover_url = $editing ? get_post_meta( $editing->ID, '_bp_external_cover_url', true ) : '';
$preset_cover_url = ( $editing && has_post_thumbnail( $editing->ID ) ) ? get_the_post_thumbnail_url( $editing->ID, 'large' ) : $preset_external_cover_url;
$user = wp_get_current_user();

$categories = get_terms( [
    'taxonomy'   => 'category',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
    'meta_query' => [
        'relation' => 'OR',
        [ 'key' => '_bp_term_status', 'value' => 'approved' ],
        [ 'key' => '_bp_term_status', 'compare' => 'NOT EXISTS' ],
    ],
] );
?>

<main class="ap-page ap-contribute">
    <section class="ap-hero ap-hero--center">
        <div class="ap-hero__glow" aria-hidden="true"></div>
        <div class="bp-shell">
            <div class="ap-hero__content reveal">
                <span class="ap-eyebrow">Contribute</span>
                <h1 class="ap-shimmer-text">Write some<span class="ap-gradient-text">thing</span><br> worth keeping.</h1>
                <p class="ap-hero-lines">
                    <span>Submit an essay or propose a new category.</span>
                    <span>Everything is reviewed by editors before publishing.</span>
                </p>
            </div>
        </div>
    </section>

    <section class="bp-shell ap-submit-wrap">
        <div class="ap-submit-tabs reveal" role="tablist" aria-label="<?php esc_attr_e( 'Contribution type', 'blocksy-child' ); ?>">
            <button class="is-active" type="button" data-contribute-tab="essay">New essay</button>
            <button type="button" data-contribute-tab="category">Suggest category</button>
        </div>

        <?php if ( $success_id ) : ?>
            <div class="bp-alert bp-alert--success reveal">
                <strong>Sent to the editorial desk.</strong>
                Your story "<?php echo esc_html( get_the_title( $success_id ) ); ?>" is in review.
            </div>
        <?php endif; ?>
        <?php if ( $category_success ) : ?>
            <div class="bp-alert bp-alert--success reveal">
                <strong>Category suggested.</strong>
                "<?php echo esc_html( $category_success ); ?>" is waiting for editor approval.
            </div>
        <?php endif; ?>
        <?php if ( $error_msg ) : ?>
            <div class="bp-alert bp-alert--error reveal">
                <strong>Something went wrong.</strong>
                <?php echo esc_html( $error_msg ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $editing_declined ) : ?>
            <article class="ap-submit-card ap-submit-card--readonly ap-declined-view reveal">
                <div class="ap-declined-view__status">
                    <strong>Declined</strong>
                    <span>
                        <?php
                        $declined_at = get_post_meta( $editing->ID, '_bp_declined_at', true );
                        echo esc_html( $declined_at ? mysql2date( 'Y-m-d', $declined_at ) : mysql2date( 'Y-m-d', $editing->post_modified ) );
                        ?>
                    </span>
                </div>

                <div class="ap-declined-view__head">
                    <span><?php esc_html_e( 'Read-only blog', 'blocksy-child' ); ?></span>
                    <h2><?php echo esc_html( $preset_title ?: 'Untitled blog' ); ?></h2>
                    <p><?php esc_html_e( 'This blog was declined by the editorial team and is kept here for your records.', 'blocksy-child' ); ?></p>
                </div>

                <?php if ( $preset_cover_url ) : ?>
                    <figure class="ap-declined-view__cover">
                        <img src="<?php echo esc_url( $preset_cover_url ); ?>" alt="">
                    </figure>
                <?php endif; ?>

                <div class="ap-declined-view__meta">
                    <span><strong>Author</strong><?php echo esc_html( $user->display_name ?: $user->user_login ); ?></span>
                    <span><strong>Category</strong><?php echo esc_html( $preset_cat ? get_cat_name( $preset_cat ) : 'Uncategorized' ); ?></span>
                </div>

                <?php if ( $preset_excerpt ) : ?>
                    <p class="ap-declined-view__excerpt"><?php echo esc_html( $preset_excerpt ); ?></p>
                <?php endif; ?>

                <div class="ap-declined-view__content">
                    <?php echo wp_kses_post( wpautop( $preset_content ) ); ?>
                </div>

                <?php if ( $preset_link ) : ?>
                    <a class="ap-declined-view__source" href="<?php echo esc_url( $preset_link ); ?>" target="_blank" rel="noopener">
                        <?php esc_html_e( 'Open external source', 'blocksy-child' ); ?>
                    </a>
                <?php endif; ?>

                <div class="ap-submit-card__bottom">
                    <span><?php esc_html_e( 'Editing is disabled for declined blogs.', 'blocksy-child' ); ?></span>
                    <a class="bp-btn bp-btn--dark" href="<?php echo esc_url( home_url( '/my-desk/' ) ); ?>">Back to My Desk</a>
                </div>
            </article>
        <?php else : ?>
        <form class="ap-submit-card reveal" method="post" enctype="multipart/form-data" action="<?php echo esc_url( get_permalink() ); ?>" id="bp-editor-form">
            <?php wp_nonce_field( 'bp_submit_post', 'bp_submit_post_nonce' ); ?>
            <?php if ( $editing ) : ?>
                <input type="hidden" name="bp_edit_id" value="<?php echo esc_attr( $editing->ID ); ?>">
            <?php endif; ?>
            <input type="hidden" name="bp_cover_variant" id="bp_cover_variant" value="blue">
            <input type="hidden" name="bp_remove_thumbnail" id="bp_remove_thumbnail" value="0">
            <input type="hidden" name="bp_contribution_mode" id="bp_contribution_mode" value="essay">
            <input type="hidden" name="bp_tags" id="bp_tags" value="">
            <input type="hidden" name="bp_visibility" value="public">

            <div class="ap-field ap-field--full" data-essay-field>
                <label for="bp-title">Title</label>
                <input id="bp-title" class="bp-input" type="text" name="bp_title" value="<?php echo esc_attr( $preset_title ); ?>" placeholder="A clear, considered headline" required>
            </div>

            <div class="ap-field" data-essay-field>
                <label for="bp-author">Author</label>
                <input id="bp-author" class="bp-input" type="text" value="<?php echo esc_attr( $user->display_name ?: $user->user_login ); ?>" readonly>
            </div>

            <div class="ap-field" data-essay-field>
                <label for="bp_category">Category</label>
                <span class="ap-select-wrap">
                    <select id="bp_category" class="bp-input" name="bp_category">
                        <option value="0" <?php selected( $preset_cat, 0 ); ?>>Choose category</option>
                        <?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
                            <?php foreach ( $categories as $index => $term ) : ?>
                                <option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( $preset_cat, $term->term_id ); ?>>
                                    <?php echo esc_html( $term->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <option value="0">Suggest new category</option>
                    </select>
                    <span class="ap-select-wrap__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">
                            <path d="m7 10 5 5 5-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </span>
            </div>

            <div class="ap-field ap-field--full ap-cover-field" data-essay-field>
                <div class="ap-label-row">
                    <label>Cover image</label>
                </div>
                <div class="ap-upload-tabs" aria-label="<?php esc_attr_e( 'Cover image source', 'blocksy-child' ); ?>">
                    <button class="is-active" type="button" data-cover-source="upload" aria-pressed="true">Upload from computer</button>
                    <button type="button" data-cover-source="url" aria-pressed="false">Use a URL</button>
                </div>
                <div class="ap-cover-drop bp-cover-drop" id="bp-cover-drop" data-cover-upload-panel>
                    <input type="file" name="bp_thumbnail" id="bp_thumbnail" accept="image/jpeg,image/png,image/webp">
                    <div class="bp-cover-upload-preview <?php echo $preset_cover_url ? 'is-visible' : ''; ?>" data-cover-preview>
                        <?php if ( $preset_cover_url ) : ?>
                            <img src="<?php echo esc_url( $preset_cover_url ); ?>" alt="">
                        <?php endif; ?>
                    </div>
                    <label for="bp_thumbnail" class="ap-cover-drop__label">
                        <span aria-hidden="true">&uarr;</span>
                        <strong>Click to upload or drag & drop</strong>
                        <small>PNG, JPG, or WebP - up to ~5MB</small>
                    </label>
                </div>
                <div class="ap-cover-url-panel" data-cover-url-panel hidden>
                    <label class="screen-reader-text" for="bp_thumbnail_url"><?php esc_html_e( 'Cover image URL', 'blocksy-child' ); ?></label>
                    <input id="bp_thumbnail_url" class="bp-input" type="url" name="bp_thumbnail_url" value="<?php echo esc_attr( $preset_external_cover_url ); ?>" placeholder="https://example.com/cover.jpg" data-cover-url-input>
                    <div class="ap-cover-url-preview" data-cover-url-preview hidden>
                        <img src="" alt="">
                    </div>
                    <small>Paste a direct image URL ending in JPG, PNG, WebP, or GIF.</small>
                </div>
            </div>

            <div class="ap-field ap-field--full" data-essay-field>
                <div class="ap-label-row">
                    <label for="bp_external_link">External link</label>
                    <span>optional</span>
                </div>
                <input id="bp_external_link" class="bp-input" type="url" name="bp_external_link" value="<?php echo esc_attr( $preset_link ); ?>" placeholder="https://your-source.com/article">
            </div>

            <div class="ap-field ap-field--full" data-essay-field>
                <div class="ap-label-row">
                    <label for="bp-subtitle">Excerpt</label>
                    <span>optional - 1-2 sentences</span>
                </div>
                <input id="bp-subtitle" class="bp-input" type="text" name="bp_excerpt" value="<?php echo esc_attr( $preset_excerpt ); ?>">
            </div>

            <div class="ap-field ap-field--full" data-essay-field>
                <label for="bp-content">Essay</label>
                <textarea id="bp-content" class="bp-input" name="bp_content" placeholder="Write something worth slowing down for..." required><?php echo esc_textarea( $preset_content ); ?></textarea>
            </div>

            <div class="ap-category-panel" data-category-panel hidden>
                <div class="ap-field ap-field--full">
                    <label for="bp_suggest_category">Category name</label>
                    <input id="bp_suggest_category" class="bp-input" type="text" name="bp_suggest_category" value="<?php echo isset( $_GET['suggest_topic'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['suggest_topic'] ) ) ) : ''; ?>" placeholder="Example: Spatial computing">
                </div>
                <p>Suggested categories are reviewed by editors before they appear publicly.</p>
            </div>

            <div class="ap-submit-card__bottom">
                <span>Goes live after an editor approves.</span>
                <button class="bp-btn bp-btn--dark" type="submit" name="bp_action" value="submit">Submit essay</button>
            </div>
        </form>
        <?php endif; ?>
    </section>
</main>

<?php get_footer(); ?>
