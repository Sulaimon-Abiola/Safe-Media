<?php
/*
  Plugin Name: Safe Media
  Plugin URI: https://safemedia.com/
  Description: Safe media assignment plugin.
  Version: 1.0
  Author: Oyaleke Sulaimon
  Author URI: https://www.abiola.com/
  Text Domain: safe-media
*/

/*
 *   Include only when ABSPATH is defined 
 */
if (!defined('ABSPATH')) {
  die('Do not open this file directly.');
}


/*
 *  Require the helper functions file
 */
require_once( dirname(__FILE__) . '/includes/helpers.php' );


/*
 *  Require cmb2 plugin init file
 */
require_once( dirname(dirname(__FILE__)) . '/cmb2/init.php' );


/*
 *  Registering cmb2 image field for the term page
 *  Allowing only JPEG and PNG images alone  
 *  With image preview
 */
add_action( 'cmb2_admin_init', 'smd_taxonomy_meta_box' );
function smd_taxonomy_meta_box() {
    $prefix = 'smd_taxonomy_';
 
    $cmb_term = new_cmb2_box( array(
        'id'               => $prefix . 'edit',
        'title'            => __( 'Safe Media Taxonomy', 'cmb2' ),
        'object_types'     => array( 'term' ),
        'taxonomies'       => array( 'category' ),
        'context'          => 'side',
        'priority'         => 'high',
        'show_names'       => true,
    ) );
 
    $cmb_term->add_field( array(
        'name'       => __( 'Image', 'cmb2' ),
        'desc'       => __( 'Upload an image or enter a URL.', 'cmb2' ),
        'id'         => $prefix . 'image',
        'type'       => 'file',
        'options'    => array(
            'url' => false,
        ),
        'text'       => array(
            'add_upload_file_text' => 'Add Image'
        ),
        'query_args' => array(
            'type' => array(
                'image/jpeg',
                'image/png',
            ),
        ),
    ) );
}


/*
 *  Enqueuing the plugin scripts
 */
add_action( 'admin_enqueue_scripts', 'smd_enqueue_scripts' );
function smd_enqueue_scripts() {
   wp_enqueue_style( 'same-media', plugin_dir_url( __FILE__ ) . 'assets/style.css', array( ), '1.0' );
   wp_enqueue_script( 'same-media', plugin_dir_url( __FILE__ ) . 'assets/script.js', array( 'media-editor' ), '1.0', true );
   wp_localize_script( 'same-media', 'smd_helper_object',
      array( 
         'siteUrl' => site_url(),
      )
   );
}


