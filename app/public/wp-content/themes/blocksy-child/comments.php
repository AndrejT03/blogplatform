<?php
/**
 * Comments template
 */
if ( post_password_required() ) {
    return;
}

$count = (int) get_comments_number();

if ( ! function_exists( 'bp_comment_layout' ) ) {
    function bp_comment_layout( $comment, $args, $depth ) {
        $comment_author = $comment->comment_author ?: 'Reader';
        $comment_initial = strtoupper( function_exists( 'mb_substr' ) ? mb_substr( $comment_author, 0, 1 ) : substr( $comment_author, 0, 1 ) );
        $comment_classes = [
            'bp-comment-item',
            $depth > 1 ? 'bp-comment-item--reply' : 'bp-comment-item--parent',
        ];

        if ( '0' === (string) $comment->comment_approved ) {
            $comment_classes[] = 'bp-comment-item--pending';
        }
        ?>
        <li <?php comment_class( $comment_classes ); ?> id="comment-<?php comment_ID(); ?>">
            <article class="comment-body">
                <header class="comment-author">
                    <span class="bp-comment-avatar" aria-hidden="true"><?php echo esc_html( $comment_initial ); ?></span>
                    <div>
                        <cite><?php echo esc_html( $comment_author ); ?></cite>
                        <div class="comment-metadata">
                            <a class="comment-metadata__link" href="<?php echo esc_url( get_comment_link( $comment ) ); ?>">
                                <?php echo esc_html( get_comment_date( 'Y-m-d' ) ); ?>
                            </a>
                        </div>
                    </div>
                </header>
                <div class="comment-content">
                    <?php comment_text(); ?>
                </div>
                <?php if ( '0' == $comment->comment_approved ) : ?>
                    <p class="bp-comment-pending">In review</p>
                <?php endif; ?>
                <?php if ( $depth < (int) $args['max_depth'] ) : ?>
                    <div class="reply">
                        <?php
                        comment_reply_link( array_merge( $args, [
                            'depth'      => $depth,
                            'max_depth'  => $args['max_depth'],
                            'reply_text' => 'Reply',
                        ] ) );
                        ?>
                    </div>
                <?php endif; ?>
        <?php
    }
}

if ( ! function_exists( 'bp_comment_layout_end' ) ) {
    function bp_comment_layout_end( $comment, $args, $depth ) {
        ?>
            </article>
        </li>
        <?php
    }
}
?>

<h2 class="bp-comments__title">Conversation</h2>
<div class="bp-comments__count">
    <?php if ( $count > 0 ) : ?>
        <?php echo $count === 1 ? '1 thought shared.' : esc_html( $count ) . ' thoughts shared.'; ?>
    <?php else : ?>
        Be the first to share a thought.
    <?php endif; ?>
</div>

<?php if ( have_comments() ) : ?>

    <ol class="comment-list">
        <?php
        wp_list_comments( [
            'style'       => 'ol',
            'short_ping'  => true,
            'avatar_size' => 40,
            'callback'    => 'bp_comment_layout',
            'end-callback' => 'bp_comment_layout_end',
            'max_depth'   => 2,
        ] );
        ?>
    </ol>

    <?php
    the_comments_pagination( [
        'prev_text' => 'Previous',
        'next_text' => 'Next',
        'class'     => 'bp-pagination',
    ] );
    ?>

<?php endif; ?>

<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
    <div class="bp-alert"><strong>Closed</strong>Comments are closed on this story.</div>
<?php endif; ?>

<?php
comment_form( [
    'class_form'        => 'bp-comment-form',
    'class_submit'      => 'bp-btn bp-btn--accent',
    'title_reply'       => 'Leave a comment',
    'label_submit'      => 'Submit',
    'comment_notes_before' => '',
    'comment_notes_after'  => '<p class="bp-comment-note">Comments are reviewed before publishing.</p>',
    'fields'            => [
        'author' => '<p class="comment-form-author"><input id="author" name="author" type="text" placeholder="Your name" value="" required></p>',
        'email'  => '<p class="comment-form-email"><input id="email" name="email" type="email" placeholder="Your email" value="" required></p>',
        'url'    => '',
    ],
    'comment_field'     => '<p><textarea name="comment" id="comment" placeholder="Share a thought..." required></textarea></p>',
] );
?>
