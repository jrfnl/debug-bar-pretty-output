<?php
/**
 * Debug Bar Pretty Output - Helper class for Debug Bar plugins
 *
 * Used by the following plugins:
 * - Debug Bar Constants
 * - Debug Bar Post Types
 * - Debug Bar WP Objects (unreleased)
 * - Debug Bar Screen Info
 *
 * @package		Debug Bar Pretty Output
 * @author		Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link		https://github.com/jrfnl/debug-bar-pretty-output
 * @version		1.4
 *
 * @copyright	2013 Juliette Reinders Folmer
 * @license		http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 */

if ( ! class_exists( 'Debug_Bar_Pretty_Output' ) && class_exists( 'Debug_Bar_Panel' ) ) {
	/**
	 * Class Debug_Bar_Pretty_Output
	 */
	class Debug_Bar_Pretty_Output {

		const VERSION = '1.4';

		const NAME = 'db-pretty-output';

		const TBODY_MAX = 10;

		/**
		 * @var bool|int Whether to limit how deep the variable printing will recurse into an array/object
		 *               Set to a positive integer to limit the recursion depth to that depth.
		 *               Defaults to false.
		 *
		 * @since 1.4
		 */
		protected static $limit_recursion = false;


		/**
		 * Set the recursion limit.
		 *
		 * Always make sure you also unset the limit after you're done with this class so as not to impact
		 * other plugins which may be using this printing class.
		 *
		 * @since 1.4
		 *
		 * @param int $depth
		 */
		public static function limit_recursion( $depth ) {
			if ( is_int( $depth ) && $depth > 0 ) {
				self::$limit_recursion = $depth;
			}
		}


		/**
		 * Reset the recusion limit to it's default (unlimited).
		 *
		 * @since 1.4
		 */
		public static function unset_recursion_limit() {
			self::$limit_recursion = false;
		}


		/**
		 * A not-so-pretty method to show pretty output ;-)
		 *
		 * @since	1.3
		 *
		 * @param   mixed   $var        Variable to show
		 * @param   string  $title      (optional) Variable title
		 * @param   bool    $escape     (optional) Whether to character escape the textual output
		 * @param   string  $space      (internal) Indentation spacing
		 * @param   bool    $short      (internal) Short or normal annotation
		 * @param   int     $depth      (internal) The depth of the current recursion
		 * @return	string
		 */
		public static function get_output( $var, $title = '', $escape = true, $space = '', $short = false, $depth = 0 ) {

			$output = '';

			if ( $space === '' ) {
				$output .= '<div class="db-pretty-var">';
			}
			if ( is_string( $title ) && $title !== '' ) {
				$output .= '<h4 style="clear: both;">' . ( ( $escape === true ) ? esc_html( $title ) : $title ) . "</h4>\n";
			}

			if ( is_array( $var ) ) {
				if ( $var !== array() ) {
					$output .= 'Array: <br />' . $space . '(<br />';
					if ( is_int( self::$limit_recursion ) && $depth > self::$limit_recursion ) {
						$output .= '... ( ' . sprintf( __( 'output limited at recursion depth %d', self::NAME ), self::$limit_recursion ) . ')<br />';
					}
					else {
						if ( $short !== true ) {
							$spacing = $space . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
						}
						else {
							$spacing = $space . '&nbsp;&nbsp;';
						}
						foreach ( $var as $key => $value ) {
							$output .= $spacing . '[' . ( ( $escape === true ) ? esc_html( $key ) : $key );
							if ( $short !== true ) {
								$output .= ' ';
								switch ( true ) {
									case ( is_string( $key ) ) :
										$output .= '<span style="color: #336600;;"><b><i>(string)</i></b></span>';
										break;

									case ( is_int( $key ) ) :
										$output .= '<span style="color: #FF0000;"><b><i>(int)</i></b></span>';
										break;

									case ( is_float( $key ) ) :
										$output .= '<span style="color: #990033;"><b><i>(float)</i></b></span>';
										break;

									default:
										$output .= '(' . __( 'unknown', self::NAME ) .')';
										break;
								}
							}
							$output .= '] => ';
							$output .= self::get_output( $value, '', $escape, $spacing, $short, ++$depth );
						}
						unset( $key, $value );
					}

					$output .= $space . ')<br />';
				}
				else {
					$output .= 'array()<br />';
				}
			}
			else if ( is_string( $var ) ) {
				$output .= '<span style="color: #336600;">';
				if ( $short !== true ) {
					$output .= '<b><i>string[' . strlen( $var ) . ']</i></b> : ';
				}
				$output .= '&lsquo;'
					. ( ( $escape === true ) ? str_replace( '  ', ' &nbsp;', esc_html( $var ) ) : str_replace( '  ', ' &nbsp;', $var ) )
					. '&rsquo;</span><br />';
			}
			else if ( is_bool( $var ) ) {
				$output .= '<span style="color: #000099;">';
				if ( $short !== true ) {
					$output .= '<b><i>bool</i></b> : ' . $var . ' ( = ';
				}
				else {
					$output .= '<b><i>b</i></b> ';
				}
				$output .= '<i>'
					. ( ( $var === false ) ? '<span style="color: #FF0000;">false</span>' : ( ( $var === true ) ? '<span style="color: #336600;">true</span>' : __( 'undetermined', self::NAME ) ) ) . ' </i>';
				if ( $short !== true ) {
					$output .= ')';
				}
				$output .= '</span><br />';
			}
			else if ( is_int( $var ) ) {
				$output .= '<span style="color: #FF0000;">';
				if ( $short !== true ) {
					$output .= '<b><i>int</i></b> : ';
				}
				$output .= ( ( $var === 0 ) ? '<b>' . $var . '</b>' : $var ) . "</span><br />\n";
			}
			else if ( is_float( $var ) ) {
				$output .= '<span style="color: #990033;">';
				if ( $short !== true ) {
					$output .= '<b><i>float</i></b> : ';
				}
				$output .= $var
					. '</span><br />';
			}
			else if ( is_null( $var ) ) {
				$output .= '<span style="color: #666666;">';
				if ( $short !== true ) {
					$output .= '<b><i>';
				}
				$output .= 'null';
				if ( $short !== true ) {
					$output .= '</i></b> : ' . $var . ' ( = <i>NULL</i> )';
				}
				$output .= '</span><br />';
			}
			else if ( is_resource( $var ) ) {
				$output .= '<span style="color: #666666;">';
				if ( $short !== true ) {
					$output .= '<b><i>resource</i></b> : ';
				}
				$output .= $var;
				if ( $short !== true ) {
					$output .= ' ( = <i>RESOURCE</i> )';
				}
				$output .= '</span><br />';
			}
			else if ( is_object( $var ) ) {
				$output .= 'Object: <br />' . $space . '(<br />';
				if ( is_int( self::$limit_recursion ) && $depth > self::$limit_recursion ) {
					$output .= '... ( ' . sprintf( __( 'output limited at recursion depth %d', self::NAME ), self::$limit_recursion ) . ')<br />';
				}
				else {
					if ( $short !== true ) {
						$spacing = $space . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					}
					else {
						$spacing = $space . '&nbsp;&nbsp;';
					}
					$output .= self::get_object_info( $var, $escape, $spacing, $short, ++$depth );
				}
				$output .= $space . ')<br /><br />';
			}
			else {
				$output .= esc_html__( 'I haven\'t got a clue what this is: ', self::NAME ) . gettype( $var ) . '<br />';
			}
			if ( $space === '' ) {
				$output .= '</div>';
			}

			return $output;
		}


		/**
		 * Retrieve pretty output about objects
		 *
		 * @todo: get object properties to show the variable type on one line with the 'property'
		 * @todo: get scope of methods and properties
		 *
		 * @since	1.3
		 *
		 * @param   object  $obj        Object to show
		 * @param   bool    $escape     (internal) Whether to character escape the textual output
		 * @param   string  $space      (internal) Indentation spacing
		 * @param   bool    $short      (internal) Short or normal annotation
		 * @param   int     $depth      (internal) The depth of the current recursion
		 * @return	string
		 */
		private static function get_object_info( $obj, $escape, $space, $short, $depth = 0 ) {

			$output = '';

			$output .= $space . '<b><i>Class</i></b>: ' . esc_html( get_class( $obj ) ) . ' (<br />';
			if ( $short !== true ) {
				$spacing = $space . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			else {
				$spacing = $space . '&nbsp;&nbsp;';
			}
			$properties = get_object_vars( $obj );
			if ( is_array( $properties ) && $properties !== array() ) {
				foreach ( $properties as $var => $val ) {
					if ( is_array( $val ) ) {
						$output .= $spacing . '<b><i>property</i></b>: ' . esc_html( $var ) . "<b><i> (array)</i></b>\n";
						$output .= self::get_output( $val, '', $escape, $spacing, $short, $depth );
					}
					else {
						$output .= $spacing . '<b><i>property</i></b>: ' . esc_html( $var ) . ' = ';
						$output .= self::get_output( $val, '', $escape, $spacing, $short, $depth );
					}
				}
			}
			unset( $properties, $var, $val );

			$methods = get_class_methods( $obj );
			if ( is_array( $methods ) && $methods !== array() ) {
				foreach ( $methods as $method ) {
					$output .= $spacing . '<b><i>method</i></b>: ' . esc_html( $method ) . "<br />\n";
				}
			}
			unset( $methods, $method );

			$output .= $space . ')<br /><br />';

			return $output;
		}


		/**
		 * Helper Function specific to the Debug bar plugin
		 * Retrieves html string of properties in a table and methods in an unordered list
		 *
		 * @since	1.3
		 *
		 * @param   object  $obj		Object for which to show the properties and methods
		 * @param   bool    $is_sub		(internal) Top level or nested object
		 * @reurn	string
		 */
		public static function get_ooutput( $obj, $is_sub = false ) {
			$properties = get_object_vars( $obj );
			$methods    = get_class_methods( $obj );

			$output = '';

			if ( $is_sub === false ) {
				$output .= '
		<h2><span>' . esc_html__( 'Properties:', self::NAME ) . '</span>' . count( $properties ) . '</h2>';

				$output .= '
		<h2><span>' . esc_html__( 'Methods:', self::NAME ) . '</span>' . count( $methods ) . '</h2>';
			}

			// Properties
			if ( is_array( $properties ) && $properties !== array() ) {
				$h = 'h4';
				if ( $is_sub === false ) {
					$h = 'h3';
				}

				$output .= '
		<' . $h . '>' . esc_html__( 'Object Properties:', self::NAME ) . '</' . $h . '>';

				uksort( $properties, 'strnatcasecmp' );
				$output .= self::get_table( $properties, __( 'Property', self::NAME ), __( 'Value', self::NAME ) );
			}

			// Methods
			if ( is_array( $methods ) && $methods !== array() ) {
				$output .= '
		<h3>' . esc_html__( 'Object Methods:', self::NAME ) . '</h3>
		<ul class="' . sanitize_html_class( self::NAME ) . '">';

				uksort( $methods, 'strnatcasecmp' );

				foreach ( $methods as $method ) {
					$output .= '<li>' . esc_html( $method ) . '()</li>';
				}
				unset( $method );
				$output .= '</ul>';
			}

			return $output;
		}


		/**
		 * Retrieve the table output
		 *
		 * @since	1.3
		 *
		 * @param   array           $array  	Array to be shown in the table
		 * @param   string          $col1   	Label for the first table column
		 * @param   string          $col2   	Label for the second table column
		 * @param   string|array    $class  	One or more CSS classes to add to the table
		 * @return	string
		 */
		public static function get_table( $array, $col1, $col2, $class = null ) {

			$classes = 'debug-bar-table ' . sanitize_html_class( self::NAME );
			if ( isset( $class ) ) {
				if ( is_string( $class ) && $class !== '' ) {
					$classes .= ' ' . sanitize_html_class( $class );
				}
				else if ( is_array( $class ) && $class !== array() ) {
					$class   = array_map( $class, 'sanitize_html_class' );
					$classes = $classes . ' ' . implode( ' ', $class );
				}
			}
			$col1 = ( is_string( $col1 ) ) ? $col1 : __( 'Key', self::NAME );
			$col2 = ( is_string( $col2 ) ) ? $col2 : __( 'Value', self::NAME );

			$double_it = false;
			if ( count( $array ) > self::TBODY_MAX ) {
				$double_it = true;
			}

			$return  = self::get_table_start( $col1, $col2, $classes, $double_it );
			$return .= self::get_table_rows( $array );
			$return .= self::get_table_end();
			return $return;
		}


		/**
		 * Generate the table header
		 *
		 * @param   string        $col1      Label for the first table column
		 * @param   string        $col2      Label for the second table column
		 * @param   string|array  $class     One or more CSS classes to add to the table
		 * @param   bool          $double_it Whether to repeat the table headers as table footer
		 */
		private static function get_table_start( $col1, $col2, $class = null, $double_it = false ) {
			$class_string = '';
			if ( is_string( $class ) && $class !== '' ) {
				$class_string = ' class="' . esc_attr( $class ) . '"';
			}
			$output = '
		<table' . $class_string . '>
			<thead>
			<tr>
				<th>' . esc_html( $col1 ) . '</th>
				<th>' . esc_html( $col2 ) . '</th>
			</tr>
			</thead>';

			if ( $double_it === true ) {
				$output .= '
				<tfoot>
				<tr>
					<th>' . esc_html( $col1 ) . '</th>
					<th>' . esc_html( $col2 ) . '</th>
				</tr>
				</tfoot>';
			}
			$output .= '
			<tbody>';

			return apply_filters( 'db_pretty_output_table_header', $output );
		}


		/**
		 * Generate table rows
		 *
		 * @param   array           $array  Array to be shown in the table
		 * @return	string
		 */
		private static function get_table_rows( $array ) {
			$output = '';
			foreach ( $array as $key => $value ) {
				$output .= self::get_table_row( $key, $value );
			}
			return $output;
		}


		/**
		 * Generate individual table row
		 *
		 * @param   mixed   $key    Item key to use a row label
		 * @param   mixed   $value  Value to show
		 * @return	string
		 */
		private static function get_table_row( $key, $value ) {
			$output = '
			<tr>
				<th>' . esc_html( $key ) . '</th>
				<td>';

			if ( is_object( $value ) ) {
				$output .= self::get_ooutput( $value, true );
			}
			else {
				$output .= self::get_output( $value, '', true, '', false );
			}

			$output .= '</td>
			</tr>';

			return apply_filters( 'db_pretty_output_table_body_row', $output, $key );
		}


		/**
		 * Generate table closing
		 * @return	string
		 */
		private static function get_table_end() {
			return '
			</tbody>
		</table>
';
		}


		/**
		 * Print pretty output
		 *
		 * @deprecated since v1.3 in favour of get_output()
		 *
		 * @param   mixed   $var        Variable to show
		 * @param   string  $title      (optional) Variable title
		 * @param   bool    $escape     (optional) Whether to character escape the textual output
		 * @param   string  $space      (internal) Indentation spacing
		 * @param   bool    $short      (internal) Short or normal annotation
		 * @param   string  $deprecated
		 */
		public static function output( $var, $title = '', $escape = false, $space = '', $short = false, $deprecated = null ) {
			_deprecated_function( __CLASS__ . '::' . __METHOD__, __CLASS__ . ' 1.3', __CLASS__ . '::get_output() ' . esc_html__( 'or even better: upgrade your Debug Bar plugins to their current version', self::NAME ) );
			echo self::get_output( $var, $title, $escape, $space, $short ); // xss: ok
		}


		/**
		 * Print pretty output about objects
		 *
		 * @deprecated since v1.3 in favour of get_object_info()
		 *
		 * @param   object  $obj        Object to show
		 * @param   bool    $escape     (internal) Whether to character escape the textual output
		 * @param   string  $space      (internal) Indentation spacing
		 * @param   bool    $short      (internal) Short or normal annotation
		 * @param   string  $deprecated
		 * @return	void
		 */
		private static function object_info( $obj, $escape, $space, $short, $deprecated = null ) {
			_deprecated_function( __CLASS__ . '::' . __METHOD__, __CLASS__ . ' 1.3', __CLASS__ . '::get_object_info() ' . esc_html__( 'or even better: upgrade your Debug Bar plugins to their current version', self::NAME ) );
			echo self::get_object_info( $obj, $escape, $space, $short ); // xss: ok
		}


		/**
		 * Helper Function specific to the Debug bar plugin
		 * Outputs properties in a table and methods in an unordered list
		 *
		 * @deprecated since v1.3 in favour of get_ooutput()
		 *
		 * @param   object  $obj		Object for which to show the properties and methods
		 * @param   string  $deprecated
		 * @param   bool    $is_sub		(internal) Top level or nested object
		 * @return	void
		 */
		public static function ooutput( $obj, $deprecated = null, $is_sub = false ) {
			_deprecated_function( __CLASS__ . '::' . __METHOD__, __CLASS__ . ' 1.3', __CLASS__ . '::get_ooutput() ' . esc_html__( 'or even better: upgrade your Debug Bar plugins to their current version', self::NAME ) );
			echo self::get_ooutput( $obj, $is_sub ); // xss: ok
		}


		/**
		 * Render the table output
		 *
		 * @deprecated since v1.3 in favour of get_table()
		 *
		 * @param   array           $array  	Array to be shown in the table
		 * @param   string          $col1   	Label for the first table column
		 * @param   string          $col2   	Label for the second table column
		 * @param   string|array    $class  	One or more CSS classes to add to the table
		 * @param   string          $deprecated
		 * @return	void
		 */
		public static function render_table( $array, $col1, $col2, $class = null, $deprecated = null ) {
			_deprecated_function( __CLASS__ . '::' . __METHOD__, __CLASS__ . ' 1.3', __CLASS__ . '::get_table() ' . esc_html__( 'or even better: upgrade your Debug Bar plugins to their current version', self::NAME ) );
			echo self::get_table( $array, $col1, $col2, $class ); // xss: ok
		}
	} // End of class Debug_Bar_Pretty_Output

	/* Load text strings for this class */
	load_plugin_textdomain( Debug_Bar_Pretty_Output::NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

} // End of if class_exists wrapper
