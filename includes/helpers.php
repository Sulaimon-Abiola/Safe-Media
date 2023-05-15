<?php

/*
 *  Return the list of attached posts or terms for a given attachment id
 */
function get_attached_objects( $attachment_id ) {
   $attached = array();

   // check if image is being used as a featured image
   $posts_with_image = get_posts( 
      array(
        'meta_key'         => '_thumbnail_id',
        'meta_value'       => $attachment_id,
        'post_type'        => 'post',
        'post_status'      => 'publish',
        'fields'           => 'ids',
        'posts_per_page'   => -1,
      ) 
   );
   
   if ( $posts_with_image ) {

      $posts_list = '';

      foreach (  $posts_with_image as $post_id ) {
         $edit_post_link = get_edit_post_link( $post_id );

         $posts_list .= sprintf('<a href="%s">%s</a>, ', esc_url( $edit_post_link ), $post_id);

      }

      $posts_list = rtrim($posts_list, ', ');

      $attached['featured'] = $posts_list;
   }

   // check if image is being used in the content of a post
   $image_in_posts = get_posts(array(
      'meta_query' => array(
            array(
               'key' => '_thumbnail_id',
               'value' => $attachment_id,
               'compare' => 'NOT EXISTS',
            ),
      ),
      'post_type' => 'post',
      'post_status' => 'any',
      's' => 'wp-image-'.$attachment_id,
      'fields' => 'ids',
   ));

   if ( $image_in_posts ) {

      $posts_list = '';

      foreach (  $image_in_posts as $post_id ) {
         $edit_post_link = get_edit_post_link( $post_id );
         $posts_list .= sprintf('<a href="%s">%s</a>, ', esc_url( $edit_post_link ), $post_id);

      }

      $posts_list = rtrim($posts_list, ', ');

      $attached['content'] = $posts_list;

   }
  
   // check if image is being used in a taxonomy term
   $terms_with_image = get_terms(array(
      'taxonomy' => 'category',
      'meta_query' => array(
            array(
               'key'       =>    'smd_taxonomy_image_id',
               'value'     =>    $attachment_id,
               'compare'   =>    'LIKE',
            ),
      ),
   ));

   if($terms_with_image) {
      $terms_list = '';

      foreach($terms_with_image as $term) {
         $edit_link = get_edit_term_link($term->term_id, $term->taxonomy);

         $terms_list .= sprintf('<a href="%s">%s</a>, ', $edit_link, $term->term_id);
      }

      $terms_list = rtrim($terms_list, ', ');

      $attached['term'] = $terms_list;
   }

   return $attached;
}

// function get_current_screen() {
// 	global $current_screen;

// 	if ( ! isset( $current_screen ) ) {
// 		return null;
// 	}

// 	return $current_screen;
// }