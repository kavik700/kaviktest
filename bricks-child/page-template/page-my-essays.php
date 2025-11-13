<?php
/*
Template Name: My Essays Template
*/
get_header();

if (!is_user_logged_in()) { wp_redirect(home_url('/mein-konto/')); exit; }
    

?>
<section class="course_listing_section">
    <div class="brxe-container">        
        <h1><?php _e('Meine Essays','bricks-child'); ?></h1>
        <div class="essay_list">
            <?php
                while ( have_posts() ) : the_post();
                    the_content();
                endwhile;
            ?>
        </div>
    </div>
</section>
<?php get_footer(); ?>