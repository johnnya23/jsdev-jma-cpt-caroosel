<?php
/*
Plugin Name: JMA Carousel
Description: Uses slick jquery plugin to convert minipost grid to carousel
Version: 1.0
Author: John Antonacci
License:
http://kenwheeler.github.io/slick/
*/

function jma_carousel_css()
{
    return    '';
}


function jma_car_detect_shortcode($needle = '', $post_item = 0)
{
    if ($post_item) {
        if (is_object($post_item)) {
            $post = $post_item;
        } else {
            $post = get_post($post_item);
        }
    } else {
        global $post;
    }
    if (is_array($needle)) {
        $pattern = get_shortcode_regex($needle);
    } elseif (is_string($needle)) {
        $pattern = get_shortcode_regex(array($needle));
    } else {
        $pattern = get_shortcode_regex();
    }

    preg_match_all('/'. $pattern .'/s', $post->post_content, $matches);


    if (//if shortcode(s) to be searched for were passed and not found $return false

            array_key_exists(2, $matches) &&
            count($matches[2])
        ) {
        $return = $matches;
    } else {
        $return = false;
    }

    return $return;
}


function jma_car_scripts()
{
    wp_enqueue_script('jma-cpt-carousel-js', plugins_url('/jcarousel.min.js', __FILE__), array('jquery'));
    wp_enqueue_style('jma-cpt-carousel-css', plugins_url('/jma-cpt-carousel.css', __FILE__));
    wp_add_inline_style('jma-carousel-css', jma_carousel_css());
}

function jma_car_template_redirect()
{
    //template_builder.php
    if (is_page_template('template_builder.php') || jma_car_detect_shortcode('jma_car')) {
        add_action('wp_enqueue_scripts', 'jma_car_scripts');
    }
}
add_action('template_redirect', 'jma_car_template_redirect');

function jma_slick_init($item_id)
{
    return    'jQuery(document).ready(function($) {
    $(function() {
        var $jma_car = $("#' . $item_id . '");

        $jma_car
            .on("jcarousel:reload jcarousel:create", function () {
                var carousel = $(this),
                    width = carousel.innerWidth();

                if (width >= 900) {
                    num = 8;
                } else if (width >= 700) {
                    num = 6;
                }else if (width >= 500) {
                    num = 3;
                }else{
                    num = 2;
                }
                width = width / num;

                carousel.jcarousel("items").css("width", Math.ceil(width) + "px");


        carousel.jcarouselAutoscroll({
            interval: 3000,
            target: "+=2",
            autostart: true
        })
            })
            .jcarousel({
                wrap: "circular"
            });

        $(".jma-car-control-prev")
            .jcarouselControl({
                target: "-=1"
            });

        $(".jma-car-control-next")
            .jcarouselControl({
                target: "+=1"
            });

        /*$(".jma-car-pagination")
            .on("jcarouselpagination:active", "a", function() {
                $(this).addClass("active");
            })
            .on("jcarouselpagination:inactive", "a", function() {
                $(this).removeClass("active");
            })
            .on("click", function(e) {
                e.preventDefault();
            })
            .jcarouselPagination({
                perPage: 1,
                item: function(page) {
                    return "<a href="#" + page + "">" + page + "</a>";
                }
            });*/
    });
});';
}

function jma_carousel()
{
    $the_query = new WP_Query('post_type=personnel&posts_per_page=50');

    // The Loop
    if ($the_query->have_posts()) {
        ob_start();
        $item_id = uniqid();
        echo '<div class="jma-car-wrapper">';
        echo '<div id="' . $item_id . '" class="jma-car">';
        echo '<ul>';
        while ($the_query->have_posts()) {
            $the_query->the_post();
            echo '<li class="jma-car-inner">';
            echo '<a href="' . get_the_permalink() . '" class="jma-car-title-wrap header-font"><strong>' . get_the_title() . '</strong></a>';
            echo '<div>' . get_the_post_thumbnail(null, 'jma-car') . '</div>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div><!--jma-car-->';

        echo '<a href="#" class="jma-car-control-prev">&lsaquo;</a>';
        echo '<a href="#" class="jma-car-control-next">&rsaquo;</a>';
        if ($paged) {
            echo '<p class="jma-car-pagination"></p>';
        }
        echo '</div><!--jma-car-wrapper-->';
        echo '<script type="text/javascript">';
        echo jma_slick_init($item_id);
        echo '</script>';
        return ob_get_clean();
        /* Restore original Post Data */
        wp_reset_postdata();
    } else {
        // no posts found
    }
}
add_shortcode('jma_car', 'jma_carousel');

function jma_carousel_image_sizes($sizes)
{
    global $jma_spec_options;

    // image size for header slider
    $sizes['jma-car']['name'] = 'JMA Carousel';
    $sizes['jma-car']['width'] = 250;
    $sizes['jma-car']['height'] = 350;
    $sizes['jma-car']['crop'] = true;
    return $sizes;
}
add_filter('themeblvd_image_sizes', 'jma_carousel_image_sizes');
