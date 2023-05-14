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
 *   Include only file
 */
if (!defined('ABSPATH')) {
  die('Do not open this file directly.');
}

/*
 *  Requiring cmb2 plugin init file
 */
require_once( dirname(dirname(__FILE__)) . '/cmb2/init.php' );
