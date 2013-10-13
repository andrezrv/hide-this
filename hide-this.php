<?php

/**
 * @package Hide_This
 * @version 1.0
 */

/*
Plugin Name: Hide This
Plugin URI: http://wordpress.org/extend/plugins/hide-this/
Description: This plugin provides a shortcode that lets you hide some parts of the content from your posts and pages. You can easily manage inclusions and exclusions for hidden content in three levels: absolute, groups and capabilities, and specific user.
Author: Andr&eacute;s Villarreal
Author URI: https://github.com/andrezrv/
Version: 1.0
*/


// Main shortcode.
add_shortcode( 'hide', 'hide_this' );

// Alternative shortcode, in case you have to deal with compatibility issues.
add_shortcode( 'hidethis', 'hide_this' );


/**
 * Hide content by given attributes.
 * 
 * @param array $atts Rules for inclusions and exclusions.
 * @param string $orig_content HTML content, normally the one inside the [hide] shortcode.
 * 
 * @return string
 */
function hide_this( $atts, $original_content ) {

	// Define data by given attributes.
	// Both attributes allow the following values:
	// Absolute values: 'all', 'none', '[!]logged' 
	// Rolers and capabilities values: '[!]{role}', '[!]{role}:[!]{capability}', ':[!]{capability}'
	// User-specific values: 'userid:[!]{ID}', 'useremail:[!]{email}', 'username:[!]{username}'
	$atts = shortcode_atts( array(
		'for' => 'all',
		'exclude' => '',
		'test' => ''
	), $atts );

	// Make arrays with rules for inclusions and exclusions.
	$hide_rules = ht_make_rules_array( ht_make_attr_array( $atts['for'] ) );
	$show_rules = ht_make_rules_array( ht_make_attr_array( $atts['exclude'] ) );

	// Process content for inclusions.
	$content = ht_process_content( $atts['for'], $hide_rules, '', $original_content );
	// Process content for exclusions.
	$content = ht_process_content( $atts['exclude'], $show_rules, $original_content, $content );
	// Test content.
	$content = ht_test( $atts['test'], $original_content, $content );

	return $content;
	
}


/**
 * Test content when required.
 * 
 * @param string $test_value
 * @param string $original_content
 * @param string $content
 * 
 * @return string
 */
function ht_test( $test_value , $original_content, $content ) {

	if ( $test_value ) {

		switch ( $test_value ) {

			case 'content':

				if ( $original_content == $content ) {
					$content .= ' TEST PASSED!';
				}
				else {
					$content .= ' TEST FAILED!';
				}

				break;

			case 'empty':

				if ( !$content ) {
					$content .= ' TEST PASSED!';
				}
				else {
					$content .= ' TEST FAILED!';
				}
				
				break;
			
			default:
				
				break;

		}

	}

	return $content;

}


/**
 * Process content for a given combination of attribute and roles.
 * 
 * @param string $attribute The content of a received attribute.
 * @param array $roles The set of roles to process the attributes with.
 * @param string $default_content The HTML content to return in case $attribute equals "all".
 * @param string $alternative_content The HTML content to return in case $attribute is not "all". 
 * 
 * @return string
 */
function ht_process_content( $attribute, $roles, $default_content, $alternative_content ) {

	if ( 'all' == $attribute ) {

		$content = $default_content;

	}
	elseif ( 'none' == $attribute ) {

		$content = $alternative_content;

	}
	elseif ( 'logged' == $attribute ) {

		if ( is_user_logged_in() ) {
			$content = $default_content;
		}
		else {
			$content = $alternative_content;
		}

	}
	elseif ( '!logged' == $attribute ) {

		if ( !is_user_logged_in() ) {
			$content = $default_content;
		}
		else {
			$content = $alternative_content;
		}

	}
	else {
		$content = ht_process_array( $roles, $alternative_content, $default_content );
	}

	return $content;

}


/**
 * Process an array of rules and return corresponding content.
 * 
 * @param array $rules Array of rules.
 * @param string $content Default content to show if no rule is evaluated as true.
 * @param string $altered_content Content to show if some rule is evaluated as true.
 * 
 * @return string
 * 
 */
function ht_process_array( $rules, $content, $altered_content ) {

	if ( is_array( $rules ) and !empty( $rules ) ) {
	
		foreach ( $rules as $rule ) {

			// Get nicer variable names.
			$role = $rule['role'];
			$capability = $rule['capability'];

			// Evaluate for user-specific criteria first.
			//if ( ( 'userid' or 'username' or 'useremail' ) == $role ) {
			if ( 'userid' == $role
				or 'username' == $role
				or 'useremail' == $role
			) {

				if ( ht_evaluate_user( $role, $capability ) ) {

					$content = $altered_content;
					break;

				}

			}
			else { // Evaluate for roles and capabilities.

				if ( $role and $capability ) {  // Both role and capability are specified in the rule.

					if ( ht_evaluate_role( $role ) and ht_evaluate_capability( $capability ) ) {
						$content = $altered_content;
						break;
					}

				}
				elseif ( $capability ) { // Only capability is specified in the rule.

					if ( ht_evaluate_capability( $capability ) ) {
						$content = $altered_content;
						break;					
					}

				}
				elseif ( $role ) { // Only role is specified in the rule.

					if ( ht_evaluate_role( $role ) ) {
						$content = $altered_content;
						break;
					}

				}
				
			}

		}

	}

	return $content;

}


/**
 * Check if the user has the given role, and if that role is not a negated one.
 * 
 * @param string $role
 * 
 * @return true|false 
 */
function ht_evaluate_role( $role ) {

	if ( ht_check_user_role( ht_real_name( $role ) ) == ht_expected_value( $role ) ) {
		return true;
	}

	return false;

}


/**
 * Check if the current user has the given capability,
 * and if that capability is not a negated one.
 * 
 * @param string $capability
 * 
 * @return true|false
 */
function ht_evaluate_capability( $capability ) {


	if ( current_user_can( ht_real_name( $capability ) ) == ht_expected_value( $capability ) ) {
		return true;
	}

	return false;

}


/**
 * Returns an array given a string.
 * 
 * @param string $string A string (doh-doy :B). 
 * 
 * @return array
 */
function ht_make_attr_array( $string ) {

	$array = explode( ',', $string );
	$new_array = array();
	
	// Remove white spaces.
	foreach ( $array as $element ) {
		$new_array[] = trim( $element );
	}
	
	return $new_array;

}


/**
 * Given an array containing strings of rules,
 * returns an array containing arrayed rules.
 * 
 * @param array $array An array. What else?
 * 
 * @return array|false
 */
function ht_make_rules_array( $array ) {

	$new_array = array();

	if ( is_array( $array ) and !empty( $array ) ) {
		
		$i = 0;

		foreach ( $array as $key => $value ) {

			$single_rule_array = explode( ':', $value );

			$new_array[$i]['role'] = $single_rule_array[0];
			$new_array[$i]['capability'] = $single_rule_array[1];

			$i++;

		}

	}

	return $new_array;

}


/**
 * Returns the expected value for a rule segment evaluation.
 * 
 * @param string $rule_segment
 * 
 * @return true|false
 */
function ht_expected_value( $rule_segment ) {

	if ( ht_is_negative( $rule_segment ) ) {
		return false;
	}

	return true;

}


/**
 * Checks if a rule segment is negative.
 * 
 * @param string $rule_segment
 * 
 * @return true|false
 */
function ht_is_negative( $rule_segment ) {

	if ( '!' == substr( $rule_segment, 0, 1 ) ) {
		return true;
	}

	return false;

}


/**
 * Returns the real name of a rule segment.
 * Useful because negated segments can be evaluated as they are.
 * 
 * @param string $rule_segment
 * 
 * @return string
 */
function ht_real_name( $rule_segment ) {

	return str_replace( '!', '', $rule_segment );

}


/**
 * Checks if the current user has the given combination of key and value.
 * 
 * @param string $key
 * @param string $value
 * 
 * @return true|false
 */
function ht_evaluate_user( $key, $value ) {

	$user = wp_get_current_user();
	$user = (array)$user;
	$userdata = (array)$user['data'];

	$expected_value = ht_expected_value( $value );

	switch ( $key ) {

		case 'userid':

			if ( ( $userdata['ID'] == ht_real_name( $value ) ) == $expected_value ) {
				return true;
			}

			break;

		case 'username':

			if ( ( $userdata['user_login'] == ht_real_name( $value ) ) == $expected_value ) {
				return true;
			}

			break;
		
		case 'useremail':

			if ( ( $userdata['user_email'] == ht_real_name( $value ) ) == $expected_value ) {
				return true;
			}

			break;
		
		default:
			
			return false;
			
			break;

	}

}


/**
 * Checks if a particular user has a role. 
 * Returns true if a match was found.
 *
 * @param string $role Role name.
 * 
 * @return true|false
 */
function ht_check_user_role( $role ) {
 
    $user = wp_get_current_user();
    $user = (array)$user;
 
    if ( empty( $user ) or !in_array( $role, $user['roles'] ) ) {
    	return false;	
    }	
 
    return true;

}