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
            'siteUrl'           =>  site_url(),
            'currentScreen'     =>  get_current_screen()
        )
    );
}


/*
 *  Hook in to the delete attachment hook 
 *  To prevent image deletion 
 *  Based on the scenarios given in the test
 */
add_action( 'delete_attachment', 'prevent_image_deletion', 10, 1 );
function prevent_image_deletion( $attachment_id ) {
   // Check if attachment is an image
   $attachment = get_post( $attachment_id );
   if ( ! $attachment || ! wp_attachment_is_image( $attachment ) ) {
      return;
   }

   $attached_objects =  get_attached_objects($attachment_id);

   if(empty($attached_objects)) return;

   if(!empty($attached_objects['featured'])) 
      wp_send_json(
         array(
            'success'   => false, 
            'data'      =>    __(' This image is being used as a featured image for the following posts, try removing it first: ') . $attached_objects['featured']
         ),
         403
      );


   if(!empty($attached_objects['content'])) 
      wp_send_json(
         array(
            'success'   =>    false, 
            'data'      =>    __('This image is being used inside the following posts contents, try removing it first: ') . $attached_objects['content']
         ),
         403
      );
     

   if(!empty($attached_objects['term'])) 
      wp_send_json(
         array(
            'success'   =>    false, 
            'data'      =>    __('This image is being used as a featured image for the following terms, try removing it first: ') . $attached_objects['term']
         ),
         403
      );
}


/*
 *  Register custom attachement rest api endpoints
 */
add_action( 'rest_api_init',  'smd_attachments_rest_api');
function smd_attachments_rest_api () {
   register_rest_route( 'assignment/v1', '/attachments/(?P<id>\d+)', array(
         'methods' => 'GET',
         'callback' => 'smd_get_attachment',
      ) 
   );

   register_rest_route( 'assignment/v1', '/attachments/(?P<id>\d+)', array(
         'methods' => 'DELETE',
         'callback' => 'smd_delete_attachment',
      ) 
   );
}

function smd_get_attachment( $request ) {
   $media_id = (int) $request->get_param( 'id' );
   $media_item = wp_prepare_attachment_for_js( $media_id );

   if(!$media_item) return wp_send_json(array('message' => 'Media not found'), 404);

   return [
      'id'                 =>    $media_item['id'], 
      'date'               =>    $media_item['date'], 
      'slug'               =>    $media_item['name'], 
      'type'               =>    $media_item['type'], 
      'link'               =>    $media_item['link'], 
      'alt'                =>    $media_item['alt'], 
      'attachedObjects'    =>    get_attached_objects($media_id), 
   ];
}

function smd_delete_attachment( $request ) {
   $media_id = (int) $request->get_param( 'id' );

    $attachment = get_post( $media_id );
    if ( ! $attachment || ! wp_attachment_is_image( $attachment ) ) {
        return wp_send_json(array('message' => 'Media not found'), 404);
    }

   wp_delete_attachment( $media_id, true );   

   return wp_send_json(array( 'message' => 'Media item deleted successfully' ));
}
