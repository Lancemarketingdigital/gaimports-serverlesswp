<?php
/**
 * O template para exibir comentários (comments.php)
 *
 * @package Fast_WebX
 * @since   3.1.3
 */

if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="fwx-comments-area">

    <?php if ( have_comments() ) : ?>
        <h2 class="fwx-comments-title">
            <?php
            $comments_number = get_comments_number();
            if ( '1' === $comments_number ) {
                /* translators: %s: post title. */
                printf( esc_html__( '1 comentário em "%s"', 'fast-webx' ), get_the_title() );
            } else {
                printf(
                    /* translators: 1: number of comments, 2: post title. */
                    esc_html( _n( '%1$s comentário em "%2$s"', '%1$s comentários em "%2$s"', $comments_number, 'fast-webx' ) ),
                    number_format_i18n( $comments_number ),
                    get_the_title()
                );
            }
            ?>
        </h2>

        <ol class="fwx-comment-list">
            <?php
            wp_list_comments( array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 50,
            ) );
            ?>
        </ol>

        <?php
        the_comments_navigation( array(
            'prev_text' => '&larr; ' . esc_html__( 'Comentários anteriores', 'fast-webx' ),
            'next_text' => esc_html__( 'Próximos comentários', 'fast-webx' ) . ' &rarr;',
        ) );
        ?>

        <?php if ( ! comments_open() ) : ?>
            <p class="fwx-no-comments"><?php esc_html_e( 'Os comentários estão fechados.', 'fast-webx' ); ?></p>
        <?php endif; ?>

    <?php endif; // have_comments() ?>

    <?php
    comment_form( array(
        'class_form'         => 'fwx-comment-form',
        'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title">',
        'title_reply_after'  => '</h3>',
    ) );
    ?>

</div><!-- #comments -->
