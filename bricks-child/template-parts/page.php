<?php if ( is_account_page() ) : ?>
    <?php 
    the_content();
    echo Bricks\Helpers::page_break_navigation(); 
    ?>
<?php else : ?>
    <?php if ( function_exists( 'is_checkout' ) && is_checkout() ) : ?>
        <div class="checkout-page-wrapper">
    <?php endif; ?>

    <article id="brx-content" <?php post_class( 'wordpress' ); ?>>
        <?php
        $default_page_title = '<h1>' . get_the_title() . '</h1>';
        $default_page_title = apply_filters( 'bricks/default_page_title', $default_page_title, get_the_ID() );

        if ( ! empty( $default_page_title ) ) {
            echo $default_page_title;
        }

        the_content();
        echo Bricks\Helpers::page_break_navigation();
        ?>
    </article>

    <?php if ( function_exists( 'is_checkout' ) && is_checkout() ) : ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
