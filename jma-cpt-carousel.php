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
    return    '.slick-prev, .slick-next {
        right: 30px!important;
        z-index: 1;
        -webkit-box-shadow: none;
        box-shadow: none;
    }

    .slick-prev {
        left: 10px!important;
    }

    .slick-prev:before, .slick-next:before {
        font-size: 40px!important;
    }

    .jma-slick-inner {
        position: relative;
    }

    .carousel-title-wrap {
        display: block;
        position: absolute;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.7);
        top: 0;
        bottom: 100%;
        overflow: hidden;
        -webkit-transition: all 0.3s;
        /* Safari */
        transition: all 0.3s;
        text-align: center;
    }

    .jma-slick-inner:hover>.carousel-title-wrap {
        bottom: 0
    }

    .carousel-title-wrap strong {
        display: block;
        top: 50%;
        transform: translate(0, -50%);
        position: absolute;
        width: 100%;
    }';
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


function jma_slick_carousel_scripts()
{
    wp_enqueue_script('jma-cpt-carousel-js', plugins_url('/jcarousel.min.js', __FILE__), array('jquery'));
    wp_enqueue_style('jma-cpt-carousel-css', plugins_url('/jma-cpt-carousel.css', __FILE__));
    wp_add_inline_style('jma-jma-carousel-css', jma_carousel_css());
}

function jma_slick_carousel_template_redirect()
{
    //template_builder.php
    if (is_page_template('template_builder.php') || jma_car_detect_shortcode('jma_car')) {
        add_action('wp_enqueue_scripts', 'jma_slick_carousel_scripts');
    }
}
add_action('template_redirect', 'jma_slick_carousel_template_redirect');

function jma_slick_init()
{
    return    'jQuery(document).ready(function($) {
  $(".jma-slick-wrap").slick({
  slidesToShow: 8,
  //infinite: false,
  autoplay: true,
  autoplaySpeed: 3000,
  slidesToScroll: 2,
  responsive: [
    {
      breakpoint: 1200,
      settings: {
        slidesToShow: 6,
      }
    },
    {
      breakpoint: 992,
      settings: {
        slidesToShow: 4,
      }
    },
    {
      breakpoint: 768,
      settings: {
        slidesToShow: 3,
        slidesToScroll: 3,
      }
    }
    // You can unslick at a given breakpoint now by adding: settings: "unslick" instead of a settings object
  ]
  });
});';
}

function jma_carousel()
{
    $the_query = new WP_Query('post_type=personnel&posts_per_page=50');

    // The Loop
    if ($the_query->have_posts()) {
        echo '<div class="jma-slick-wrap">';
        while ($the_query->have_posts()) {
            $the_query->the_post();
            echo '<div class="jma-slick-inner">';
            echo '<a href="' . get_the_permalink() . '" class="carousel-title-wrap header-font"><strong>' . get_the_title() . '</strong></a>';
            echo '<div>' . get_the_post_thumbnail(null, 'jma-carousel') . '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '<script type="text/javascript">';
        echo jma_slick_init();
        echo '</script>';
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
    $sizes['jma-carousel']['name'] = 'JMA Carousel';
    $sizes['jma-carousel']['width'] = 250;
    $sizes['jma-carousel']['height'] = 350;
    $sizes['jma-carousel']['crop'] = true;
    return $sizes;
}
add_filter('themeblvd_image_sizes', 'jma_carousel_image_sizes');
