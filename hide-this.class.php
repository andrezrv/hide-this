<?php
/**
 * Hide_This
 *
 * Hide some parts of the content from your posts and pages. You can easily
 * manage inclusions and exclusions for hidden content in three levels:
 * absolute, groups and capabilities, and specific user.
 *
 * @package Hide_This
 * @since   1.1
 */
class Hide_This {

	private $attributes;
	private $hide_rules;
	private $show_rules;
	private $content;

	function __construct( $atts, $original_content ) {
		$this->attributes = $this->get_attributes( $atts );
		$this->hide_rules = $this->get_hide_rules();
		$this->show_rules = $this->get_show_rules();
		$this->content    = $this->get_content( $original_content );
	}

	public function __get( $property ) {
		if ( property_exists( $this, $property ) ) {
			return $this->$property;
		}

		return null;
	}

	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			$this->$property = $value;
		}
		return $this;
	}

	/**
	 * Process and hide or show content by $this->attributes.
	 * 
	 * @param  string $original_content Original HTML content, normally the one inside the [hide] shortcode.
	 * @return string           HTML result.
	 */
	function get_content( $original_content ) {

		// Process content for inclusions.
		$content = $this->process_content( 
			'inclusions',
			'',
			$original_content
		);

		// Process content for exclusions.
		$content = $this->process_content(
			'exclusions',
			$original_content,
			$content
		);

		// Test content.
		$content = $this->test(
			$this->attributes['test'],
			$original_content,
			$content
		);

		// Processing other shortcodes.
		$content = do_shortcode( $content );

		// Apply filters.
		$content = apply_filters( 'hide_this_content', $content );

		return $content;
	}

	/**
	 * Define data by given attributes.
	 * 
	 * Both attributes allow the following values:
	 * 
	 * Absolute values: 'all', 'none', '[!]logged'
	 * Rolers and capabilities values: '[!]{role}', '[!]{role}:[!]{capability}', ':[!]{capability}'
	 * User-specific values: 'userid:[!]{ID}', 'useremail:[!]{email}', 'username:[!]{username}'
	 * 
	 * @param  array $atts Attributes received by shortcode.
	 * @return array       Mixed default and received attributes.
	 */
	function get_attributes( $atts ) {
		$attributes = shortcode_atts( array(
			'for' => 'all',
			'exclude' => '',
			'test' => ''
		), $atts );
		$attributes = apply_filters( 'hide_this_attributes', $attributes );
		return $attributes;
	}

	/**
	 * Get rules for hiding content.
	 * 
	 * @return array Rules for hiding content.
	 */
	function get_hide_rules() {
		$hide_rules = $this->make_rules_array( 
			$this->make_attr_array( $this->attributes['for'] ) 
		);
		$hide_rules = apply_filters( 'hide_this_hide_rules', $hide_rules );
		return $hide_rules;
	}

	/**
	 * Rules for showing content.
	 * 
	 * @return array Rules for showing content.
	 */
	function get_show_rules() {
		$show_rules = $this->make_rules_array( 
			$this->make_attr_array( $this->attributes['exclude'] ) 
		);
		$show_rules = apply_filters( 'hide_this_show_rules', $show_rules );
		return $show_rules;
	}

	/**
	 * Test content when required.
	 * 
	 * @param  string $test_criteria    Testing criteria.
	 * @param  string $original_content Original content.
	 * @param  string $new_content      Modified content.
	 * @return string                   Test result.
	 */
	function test( $test_criteria, $original_content, $new_content ) {
		$content = $new_content;
		if ( $test_criteria ) {
			switch ( $test_criteria ) {
				case 'content':
					if ( $original_content == $content ) {
						$content .= ' TEST PASSED!';
					} else {
						$content .= ' TEST FAILED!';
					}
					break;
				case 'empty':
					if ( '' == $content ) {
						$content .= ' TEST PASSED!';
					} else {
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
	 * @param  string $for                 Type of process. Allows "inclusions" and "exclusions".
	 * @param  string $default_content     The HTML content to return in case $attribute equals "all".
	 * @param  string $alternative_content The HTML content to return in case $attribute is not "all". 
	 * @return string                      Modified content.
	 */
	function process_content( $for, $default_content, $alternative_content ) {
		$content = '';
		switch ( $for ) {
			case 'inclusions':
				$attribute = $this->attributes['for'];
				$rules = $this->hide_rules;
				break;
			case 'exclusions':
				$attribute = $this->attributes['exclude'];
				$rules = $this->show_rules;
				break;
			default:
				return $content;
				break;
		}
		switch ( $attribute ) {
			case 'all':
				$content = $default_content;
				break;
			case 'logged':
				if ( is_user_logged_in() ) {
					$content = $default_content;
				} else {
					$content = $alternative_content;
				}
				break;
			case '!logged':
				if ( !is_user_logged_in() ) {
					$content = $default_content;
				} else {
					$content = $alternative_content;
				}
				break;
			default:
				$content = $this->process_array( 
					$rules,
					$alternative_content,
					$default_content
				);
				break;
		}
		return $content;
	}

	/**
	 * Process an array of rules and return corresponding content.
	 * 
	 * @param  array  $rules           Array of rules.
	 * @param  string $content         Default content to show if no rule is evaluated as true.
	 * @param  string $altered_content Content to show if some rule is evaluated as true.
	 * 
	 * @return string
	 *
	 */
	function process_array( $rules, $content, $altered_content ) {
        $new_content = $content;

		if ( is_array( $rules ) && !empty( $rules ) ) {
			foreach ( $rules as $rule ) {
                if ( empty( $new_content ) ) {
                    $new_content = $content;
                }

				// Get nicer variable names.
				$role = $rule['role'];
				$capability = $rule['capability'];

				// Evaluate for user-specific criteria first.
				if (   (   'userid'    == $role
					    || 'username'  == $role
					    || 'useremail' == $role 
					)
					&& $this->evaluate_user( $role, $capability )
				) {

					$new_content = $altered_content;
					break;

				} else { // Evaluate for roles and capabilities.

					if ( $role && $capability ) {  // Both role and capability are specified in the rule.
						if (   $this->evaluate_role( $role )
							&& $this->evaluate_capability( $capability ) 
						) {
                            $new_content = $altered_content;
							break;
						}
					} elseif ( $capability ) { // Only capability is specified in the rule.
						if ( $this->evaluate_capability( $capability ) ) {
                            $new_content = $altered_content;
							break;
						}
					} elseif ( $role ) { // Only role is specified in the rule.
						if ( $this->evaluate_role( $role ) ) {
                            $new_content = $altered_content;
                            break;
						}
					}
				}
			}
		}

		return $new_content;
	}

	/**
	 * Check if the user has the given role, and if that role is not negative.
	 * 
	 * @param  string   $role A role to be evaluated.
	 * @return boolean        Whether the role was validated or not.
	 */
	function evaluate_role( $role ) {
        $role_name = $this->real_name( $role );
        $checked = $this->check_user_role( $role_name );
        $expected = $this->expected_value( $role );

		if ( $checked == $expected ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the current user has the given capability,
	 * and if that capability is not a negated one.
	 * 
	 * @param  string  $capability A capability to be evaluated.
	 * @return boolean             Wether the capability was validated or not.
	 */
	function evaluate_capability( $capability ) {
		if (   current_user_can( $this->real_name( $capability ) )
			== $this->expected_value( $capability )
		) {
			return true;
		}
		return false;
	}

	/**
	 * Returns an array given a string.
	 * 
	 * @param  string $string A comma-separated list of attributes.
	 * @return array          An array of attributes.
	 */
	function make_attr_array( $string ) {
        if ( $string ) {
            $array = explode( ',', $string );
            $new_array = array();
            // Remove white spaces.
            foreach ( $array as $element ) {
                $new_array[] = trim( $element );
            }
            return $new_array;
        }

        return null;
	}

	/**
	 * Given an array containing strings of rules,
	 * returns an array containing arrayed rules.
	 * 
	 * @param  array $array An array containing lists of rules as strings.
	 * @return array        An array containing lists of rules as arrays.
	 */
	function make_rules_array( $array ) {
        if ( is_array( $array ) && !empty( $array ) ) {
            $new_array = array();
            $i = 0;

			foreach ( $array as $key => $value ) {
				$single_rule_array = explode( ':', $value );
				$new_array[$i]['role'] = isset( $single_rule_array[0] ) ? $single_rule_array[0] : '';
				$new_array[$i]['capability'] = isset( $single_rule_array[1] ) ? $single_rule_array[1] : '';
				$i++;
			}

            return $new_array;
        }

        return null;
	}

	/**
	 * Returns the expected value for a rule segment evaluation.
	 * 
	 * @param  string  $rule_segment A segment of a rule.
	 * @return boolean               The expected value for the rule segment.
	 */
	function expected_value( $rule_segment ) {
		if ( $this->is_negative( $rule_segment ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Checks if a rule segment is negative.
	 * 
	 * @param  string  $rule_segment A segment of a rule.
	 * @return boolean               Wether the rule segment is negative or not.
	 */
	function is_negative( $rule_segment ) {
		if ( '!' == substr( $rule_segment, 0, 1 ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Returns the real name of a rule segment.
	 * Useful because negated segments can be evaluated as they are.
	 * 
	 * @param  string $rule_segment A segment of a rule.
	 * @return string               The real name of the rule segment.
	 */
	function real_name( $rule_segment ) {
		return str_replace( '!', '', $rule_segment );
	}

	/**
	 * Checks if the current user has the given combination of key and value.
	 * 
	 * @param  string  $key   Key name of a rule.
	 * @param  string  $value Value of a rule.
	 * @return boolean        Wether the current user applies to the rule or not.
	 */
	function evaluate_user( $key, $value ) {
		$user = wp_get_current_user();
		$user = (array)$user;
		$userdata = (array)$user['data'];
		$expected_value = $this->expected_value( $value );
		switch ( $key ) {
			case 'userid':
				if (   ( $userdata['ID'] == $this->real_name( $value ) ) 
					== $expected_value 
				) {
					return true;
				}
				break;
			case 'username':
				if (   ( $userdata['user_login'] == $this->real_name( $value ) )
					== $expected_value 
				) {
					return true;
				}
				break;
			case 'useremail':
				if (   ( $userdata['user_email'] == $this->real_name( $value ) ) 
					== $expected_value 
				) {
					return true;
				}
				break;
			default:
				return false;
				break;
		}

		return false;
	}

	/**
	 * Checks if current user has a role. 
	 *
	 * @param  string  $role Role name.
	 * @return boolean       Wether the current user has the given role or not.
	 */
	function check_user_role( $role ) {
	    $user = wp_get_current_user();
	    $user = (array)$user;
	    if ( empty( $user ) || !in_array( $role, $user['roles'] ) ) {
	    	return false;	
	    }	
	    return true;
	}

}
