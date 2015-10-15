<?php
/**
 * Hide This
 *
 * This plugin provides a shortcode that lets you hide some parts of the content
 * from your posts and pages. You can easily manage inclusions and exclusions
 * for hidden content in three levels: absolute, groups and capabilities, and
 * specific user.
 *
 * @package   Hide_This
 * @version   1.1.3
 * @author    Andrés Villarreal <andrezrv@gmail.com>
 * @license   GPL-2.0
 * @link      http://github.com/andrezrv/hide-this/
 * @copyright 2013-2014 Andrés Villarreal
 *
 * @wordpress-plugin
 * Plugin Name: Hide This
 * Plugin URI: http://wordpress.org/extend/plugins/hide-this/
 * Description: This plugin provides a shortcode that lets you hide some parts of the content from your posts and pages. You can easily manage inclusions and exclusions for hidden content in three levels: absolute, groups and capabilities, and specific user.
 * Author: Andr&eacute;s Villarreal
 * Author URI: http://andrezrv.com
 * Version: 1.1.3
 */
// Load Hide This class
require( dirname( __FILE__ ) . '/hide-this.class.php' );
// Load Hide This Loader class
require( dirname( __FILE__ ) . '/hide-this-loader.class.php' );
// Load Hide This
new Hide_This_Loader;
