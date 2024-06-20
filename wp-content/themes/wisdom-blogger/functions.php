<?php
/**
 * Describe child theme functions
 *
 * @package Wisdom Blog
 * @subpackage Wisdom Blogger
 *
 */

/*-------------------------------------------------------------------------------------------------------------------------------*/

if ( ! function_exists( 'wisdom_blogger_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function wisdom_blogger_setup() {

	    $wisdom_blogger_theme_info = wp_get_theme();
	    $GLOBALS['wisdom_blogger_version'] = $wisdom_blogger_theme_info->get( 'Version' );
	}
	endif;

add_action( 'after_setup_theme', 'wisdom_blogger_setup' );

/*-------------------------------------------------------------------------------------------------------------------------------*/

if ( ! function_exists( 'wisdom_blogger_fonts_url' ) ) :
	/**
	 * Register Google fonts for News Vibrant Mag.
	 *
	 * @return string Google fonts URL for the theme.
	 * @since 1.0.0
	 */
    function wisdom_blogger_fonts_url() {

        $fonts_url = '';
        $font_families = array();

        /*
         * Translators: If there are characters in your language that are not supported
         * by Dosis, translate this to 'off'. Do not translate into your own language.
         */
        if ( 'off' !== _x( 'on', 'Be Vietnam Pro font: on or off', 'wisdom-blogger' ) ) {
            $font_families[] = 'Be Vietnam Pro:300,400,400,500,700';
        }

        if( $font_families ) {
            $query_args = array(
                'family' => urlencode( implode( '|', $font_families ) ),
                'subset' => urlencode( 'latin,latin-ext' ),
            );

            $fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
        }

        return $fonts_url;
    }
endif;

/*-------------------------------------------------------------------------------------------------------------------------------*/

if( ! function_exists( 'wisdom_blogger_customize_register' ) ) :
	/**
	 * Managed the theme default color
	 */
	function wisdom_blogger_customize_register( $wp_customize ) {

		global $wp_customize;

		$wp_customize->get_setting( 'wisdom_blog_theme_color' )->default = '#c2185b';

        $wp_customize->add_control( new Wisdom_Blog_Customize_Control_Radio_Image(
            $wp_customize,
            'wisdom_blog_archive_layout',
                array(
                    'label'         => __( 'Archive Layouts', 'wisdom-blogger' ),
                    'description'   => __( 'Choose layout from available layouts.', 'wisdom-blogger' ),
                    'section'       => 'wisdom_blog_archive_section',
                    'settings'      => 'wisdom_blog_archive_layout',
                    'priority'      => 10,
                    'choices'       => array(
                        'classic'   => array(
                            'label' => __( 'Classic', 'wisdom-blogger' ),
                            'url'   => '%s/assets/images/archive-layout1.png'
                        ),
                        'grid' => array(
                            'label' => __( 'Grid', 'wisdom-blogger' ),
                            'url'   => '%s/assets/images/archive-layout2.png'
                        ),
                        'list' => array(
                            'label' => __( 'List', 'wisdom-blogger' ),
                            'url'   => get_stylesheet_directory_uri(). '/assets/images/archive-layout-3.png'
                        )
                    ),

                )
            )
        );

	}
endif;

add_action( 'customize_register', 'wisdom_blogger_customize_register', 20 );

/*-------------------------------------------------------------------------------------------------------------------------------*/
/**
 * Enqueue child theme styles and scripts
 */
add_action( 'wp_enqueue_scripts', 'wisdom_blogger_scripts', 20 );

function wisdom_blogger_scripts() {

    global $wisdom_blogger_version;

    wp_enqueue_style( 'wisdom-blogger-google-font', wisdom_blogger_fonts_url(), array(), null );

    wp_dequeue_style( 'wisdom-blog-style' );

	wp_enqueue_style( 'wisdom-blog-parent-style', get_template_directory_uri() . '/style.css', array(), esc_attr( $wisdom_blogger_version ) );

    wp_enqueue_style( 'wisdom-blogger', get_stylesheet_uri(), array(), esc_attr( $wisdom_blogger_version ) );

    wp_enqueue_style( 'wisdom-blog-parent-responsive', get_template_directory_uri() . '/assets/css/cv-responsive.css', array(), esc_attr( $wisdom_blogger_version ) );

    wp_enqueue_style( 'wisdom-blog-responsive', get_stylesheet_directory_uri() . '/assets/css/responsive.css', array(), esc_attr( $wisdom_blogger_version ) );

    wp_enqueue_script( 'jquery-imagesloaded', get_stylesheet_directory_uri() . '/assets/library/imagesloaded/imagesloaded.pkgd.min.js', array('jquery'), '4.1.4', true );

    $wisdom_blogger_theme_color = esc_attr( get_theme_mod( 'wisdom_blog_theme_color', '#C2185B' ) );

    $output_css = '';

    $output_css .= " .list-archive-layout .entry-btn a:hover, .single .cat-links a, .edit-link .post-edit-link,.reply .comment-reply-link,.widget_search .search-submit,.widget_search .search-submit,article.sticky:before,.widget_search .search-submit:hover, .navigation.pagination .nav-links .page-numbers.current, .navigation.pagination .nav-links a.page-numbers:hover, #secondary .widget .widget-title::before, .list-archive-layout .cat-links a,#secondary .widget .widget-title::before, .widget .wp-block-heading::before{ background: ". esc_attr( $wisdom_blogger_theme_color ) ."}\n";

    $output_css .= "a,a:hover,a:focus,a:active,.entry-footer a:hover,.comment-author .fn .url:hover,.commentmetadata .comment-edit-link,#cancel-comment-reply-link,#cancel-comment-reply-link:before,.logged-in-as a,.widget a:hover,.widget a:hover::before,.widget li:hover::before,.banner-btn a:hover,.entry-title a:hover,.entry-title a:hover,.wisdom_blog_latest_posts .cv-post-title a:hover, #site-navigation ul li.current-menu-item>a, #site-navigation ul li:hover>a, #site-navigation ul li.current_page_ancestor>a, #site-navigation ul li.current-menu-ancestor >a, #site-navigation ul li.current_page_item>a,.list-archive-layout .entry-btn a,.entry-title a:hover, .cat-links a:hover{ color: ". esc_attr( $wisdom_blogger_theme_color ) ."}\n";

    $output_css .= "widget_search .search-submit,.widget_search .search-submit:hover, .list-archive-layout .entry-btn a:hover , .list-archive-layout .entry-btn a,#secondary .widget{ border-color: ". esc_attr( $wisdom_blogger_theme_color ) ."}\n";

    $output_css .= ".cv-form-close a:hover,.navigation.pagination .nav-links .page-numbers.current, .navigation.pagination .nav-links a.page-numbers:hover{ border-color: ". esc_attr( $wisdom_blogger_theme_color ) ."}\n";

    $refine_output_css = wisdom_blog_css_strip_whitespace( $output_css );

    wp_add_inline_style( 'wisdom-blogger', $refine_output_css );

}

if ( ! function_exists ( 'wisdom_blogger_background_animation' ) ):
    /**
     * Footer Hook Handling
     *
     */
    function wisdom_blogger_background_animation() {
        echo '

          <div class="bg"></div>
          <div class="bg bg2"></div>
          <div class="bg bg3"></div>
          ' ;
    }
endif;

add_action ( 'wisdom_blog_background_animation', 'wisdom_blogger_background_animation', 5 );