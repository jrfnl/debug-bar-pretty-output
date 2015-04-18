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




if ( ! class_exists( 'Debug_Bar_List_PHP_Classes' ) ) {
	/**
	 * This class does nothing, just a way to keep the list of php classes out of the global namespace
	 * You can retrieve the list by using the static variable Debug_Bar_List_PHP_Classes::$PHP_classes
	 * List last updated: 2015-04-18
	 *
	 * @todo - maybe make parts of the list flexible based on extension_loaded()
	 */
	class Debug_Bar_List_PHP_Classes {

		/**
		 * @var     array    List of all PHP native class names
		 * @static
		 */
		public static $PHP_classes = array(

			/* == "Core" == */
			'stdClass',
			'__PHP_Incomplete_Class',
			'php_user_filter',

			// Interfaces
			'Traversable',
			'Iterator',
			'IteratorAggregate',
			'ArrayAccess',
			'Serializable',
			'Closure', // PHP 5.3.0+
			'Generator', // PHP 5.5.0+

			// Exceptions
			'Exception',
			'ErrorException', // PHP 5.1.0+


			/* == Affecting PHPs Behaviour == */
			// APC
			'APCIterator',

			// Weakref
			'WeakRef',
			'WeakMap',


			/* == Audio Formats Manipulation == */
			// KTaglib
			'KTaglib_MPEG_File',
			'KTaglib_MPEG_AudioProperties',
			'KTaglib_Tag',
			'KTaglib_ID3v2_Tag',
			'KTaglib_ID3v2_Frame',
			'KTaglib_ID3v2_AttachedPictureFrame',


			/* == Authentication Services == */

			/* == Command Line Specific Extensions == */

			/* == Compression and Archive Extensions == */
			// Phar
			'Phar',
			'PharData',
			'PharFileInfo',
			'PharException',

			// Rar
			'RarArchive',
			'RarEntry',
			'RarException',

			// Zip
			'ZipArchive',


			/* == Credit Card Processing == */

			/* == Cryptography Extensions == */

			/* == Database Extensions == */

				/* = Abstraction Layers = */
				// PDO
				'PDO',
				'PDOStatement',
				'PDOException',
				'PDORow',  // Not in PHP docs


				/* = Vendor Specific Database Extensions = */
				// Mongo
					// Mongo Core Classes
					'MongoClient',
					'MongoDB',
					'MongoCollection',
					'MongoCursor',
					'MongoCursorInterface',
					'MongoCommandCursor',

					// Mongo Types
					'MongoId',
					'MongoCode',
					'MongoDate',
					'MongoRegex',
					'MongoBinData',
					'MongoInt32',
					'MongoInt64',
					'MongoDBRef',
					'MongoMinKey',
					'MongoMaxKey',
					'MongoTimestamp',

					// Mongo GridFS Classes
					'MongoGridFS',
					'MongoGridFSFile',
					'MongoGridFSCursor',

					// Mongo Batch Classes
					'MongoWriteBatch',
					'MongoInsertBatch',
					'MongoUpdateBatch',
					'MongoDeleteBatch',

					// Mongo Miscellaneous
					'MongoLog',
					'MongoPool',
					'Mongo',

					// Mongo Exceptions
					'MongoException',
					'MongoResultException',
					'MongoCursorException',
					'MongoCursorTimeoutException',
					'MongoConnectionException',
					'MongoGridFSException',
					'MongoDuplicateKeyException',
					'MongoProtocolException',
					'MongoExecutionTimeoutException',
					'MongoWriteConcernException',

				// PHP driver for MongoDB
					// MongoDB\Driver
					'MongoDB\Driver\Manager',
					'MongoDB\Driver\Command',
					'MongoDB\Driver\Query',
					'MongoDB\Driver\BulkWrite',
					'MongoDB\Driver\WriteConcern',
					'MongoDB\Driver\ReadPreference',
					'MongoDB\Driver\Cursor',
					'MongoDB\Driver\CursorId',
					'MongoDB\Driver\Server',
					'MongoDB\Driver\WriteConcernError',
					'MongoDB\Driver\WriteError',
					'MongoDB\Driver\WriteResult',

					// BSON
					'BSON\Binary',
					'BSON\Javascript',
					'BSON\MaxKey',
					'BSON\MinKey',
					'BSON\ObjectID',
					'BSON\Regex',
					'BSON\Timestamp',
					'BSON\UTCDatetime',
					'BSON\Type',
					'BSON\Persistable',
					'BSON\Serializable',
					'BSON\Unserializable',

					// MongoDB Exceptions
					'MongoDB\Driver\Exception',
					'MongoDB\Driver\RuntimeException',
					'MongoDB\Driver\ConnectionException',
					'MongoDB\Driver\SSLConnectionException',
					'MongoDB\Driver\AuthenticationException',
					'MongoDB\Driver\WriteException',
					'MongoDB\Driver\BulkWriteException',
					'MongoDB\Driver\WriteConcernException',
					'MongoDB\Driver\DuplicateKeyException',

				// MySQL
					// Mysqli - MySQL Improved Extension
					'mysqli',
					'mysqli_stmt',
					'mysqli_result',
					'mysqli_driver',
					'mysqli_warning',
					'mysqli_sql_exception',

					// mysqlnd_uh - Mysqlnd user handler plugin
					'MysqlndUhConnection',
					'MysqlndUhPreparedStatement',

				// OCI8 - Oracle OCI8
				'OCI-Collection',
				'OCI-Lob',

				// SQLLite
				'SQLiteDatabase',  // Not easy to find in PHP docs
				'SQLiteResult',  // Not easy to find  in PHP docs
				'SQLiteUnbuffered',  // Not easy to find  in PHP docs
				'SQLiteException',	// Not easy to find  in PHP docs

				// SQLite3
				'SQLite3',
				'SQLite3Stmt',
				'SQLite3Result',

				// tokyo_tyrant
				'TokyoTyrant',
				'TokyoTyrantTable',
				'TokyoTyrantQuery',
				'TokyoTyrantIterator',
				'TokyoTyrantException',


			/* == Date and Time Related Extensions == */
			// Date/Time
			'DateTime',
			'DateTimeImmutable', // PHP 5.5.0+
			'DateTimeInterface', // PHP 5.5.0+
			'DateTimeZone',
			'DateInterval',
			'DatePeriod',
			
			// HRTime
			'HRTime\PerformanceCounter',
			'HRTime\StopWatch',
			'HRTime\Unit',


			/* == File System Related Extensions == */
			// Directories
			'Directory',

			// File Information
			'finfo', // PHP 5.3.0+


			/* == Human Language and Character Encoding Support == */
			// Gender
			'Gender\Gender',

			// intl - since PHP 5.3.0
			'Collator',
			'NumberFormatter',
			'Locale',
			'Normalizer',
			'MessageFormatter',
			'IntlCalendar', // PHP 5.5.0+
			'IntlTimeZone', // PHP 5.5.0+
			'IntlDateFormatter',
			'ResourceBundle',
			'Spoofchecker',
			'Transliterator',
			'IntlBreakIterator', // (No version information available, might only be in Git)
			'IntlRuleBasedBreakIterator', // (No version information available, might only be in Git)
			'IntlCodePointBreakIterator', // (No version information available, might only be in Git)
			'UConverter', // PHP 5.5.0+
			'IntlException', // PHP 5.5.0+
			'IntlIterator', // (No version information available, might only be in Git)


			/* == Image Processing and Generation == */
			// Cairo
			'Cairo',
			'CairoContext',
			'CairoException',
			'CairoStatus',
			'CairoSurface',
			'CairoSvgSurface',
			'CairoImageSurface',
			'CairoPdfSurface',
			'CairoPsSurface',
			'CairoSurfaceType',
			'CairoFontFace',
			'CairoFontOptions',
			'CairoFontSlant',
			'CairoFontType',
			'CairoFontWeight',
			'CairoScaledFont',
			'CairoToyFontFace',
			'CairoPatternType',
			'CairoPattern',
			'CairoGradientPattern',
			'CairoSolidPattern',
			'CairoSurfacePattern',
			'CairoLinearGradient',
			'CairoRadialGradient',
			'CairoAntialias',
			'CairoContent',
			'CairoExtend',
			'CairoFormat',
			'CairoFillRule',
			'CairoFilter',
			'CairoHintMetrics',
			'CairoHintStyle',
			'CairoLineCap',
			'CairoLineJoin',
			'CairoMatrix',
			'CairoOperator',
			'CairoPath',
			'CairoPsLevel',
			'CairoSubpixelOrder',
			'CairoSvgVersion',

			// Gmagick
			'Gmagick',
			'GmagickDraw',
			'GmagickPixel',

			// ImageMagick
			'Imagick',
			'ImagickDraw',
			'ImagickPixel',
			'ImagickPixelIterator',
			'ImagickKernel',


			/* == Mail Related Extensions == */

			/* == Mathematical Extensions == */
			// GMP
			'GMP', // PHP 5.6.0+

			// Lapack
			'Lapack',
			'LapackException',


			/* == Non-Text MIME Output == */
			// haru
			'HaruException',
			'HaruDoc',
			'HaruPage',
			'HaruFont',
			'HaruImage',
			'HaruEncoder',
			'HaruOutline',
			'HaruAnnotation',
			'HaruDestination',

			// Ming
			'SWFAction',
			'SWFBitmap',
			'SWFButton',
			'SWFDisplayItem',
			'SWFFill',
			'SWFFont',
			'SWFFontChar',
			'SWFGradient',
			'SWFMorph',
			'SWFMovie',
			'SWFPrebuiltClip',
			'SWFShape',
			'SWFSound',
			'SWFSoundInstance',
			'SWFSprite',
			'SWFText',
			'SWFTextField',
			'SWFVideoStream',


			/* == Process Control Extensions == */
			// Ev
			'Ev',
			'EvCheck',
			'EvChild',
			'EvEmbed',
			'EvFork',
			'EvIdle',
			'EvIo',
			'EvLoop',
			'EvPeriodic',
			'EvPrepare',
			'EvSignal',
			'EvStat',
			'EvTimer',
			'EvWatcher',

			// pthreads
			'Threaded',
			'Thread',
			'Worker',
			'Collectable',
			'Pool',
			'Stackable', // No longer available ?
			'Mutex',
			'Cond',

			// Sync
			'SyncMutex',
			'SyncSemaphore',
			'SyncEvent',
			'SyncReaderWriter',


			/* == Other Basic Extensions == */
			// FANN - Fast Artificial Neural Network
			'FANNConnection',

			// JSON - JavaScript Object Notation
			'JsonSerializable',

			// Judy - Judy Arrays
			'Judy',

			// Lua
			'Lua',
			'LuaClosure',

			// SPL - Standard PHP Library (SPL)

				// SPL Data structures
				'SplDoublyLinkedList',
				'SplStack',
				'SplQueue',
				'SplHeap',
				'SplMaxHeap',
				'SplMinHeap',
				'SplPriorityQueue',
				'SplFixedArray',
				'SplObjectStorage',

				// SPL Iterators
				'AppendIterator',
				'ArrayIterator',
				'CachingIterator',
				'CallbackFilterIterator',
				'DirectoryIterator',
				'EmptyIterator',
				'FilesystemIterator',
				'FilterIterator',
				'GlobIterator',
				'InfiniteIterator',
				'IteratorIterator',
				'LimitIterator',
				'MultipleIterator',
				'NoRewindIterator',
				'ParentIterator',
				'RecursiveArrayIterator',
				'RecursiveCachingIterator',
				'RecursiveCallbackFilterIterator',
				'RecursiveDirectoryIterator',
				'RecursiveFilterIterator',
				'RecursiveIteratorIterator',
				'RecursiveRegexIterator',
				'RecursiveTreeIterator',
				'RegexIterator',

				'CachingRecursiveIterator', // Not in PHP docs - deprecated


				// SPL Interfaces
				'Countable',
				'OuterIterator',
				'RecursiveIterator',
				'SeekableIterator',

				// SPL Exceptions
				'BadFunctionCallException',
				'BadMethodCallException',
				'DomainException',
				'InvalidArgumentException',
				'LengthException',
				'LogicException',
				'OutOfBoundsException',
				'OutOfRangeException',
				'OverflowException',
				'RangeException',
				'RuntimeException',
				'UnderflowException',
				'UnexpectedValueException',

				// SPL File Handling
				'SplFileInfo',
				'SplFileObject',
				'SplTempFileObject',

				// SPL Miscellaneous Classes and Interfaces
				'ArrayObject',
				'SplObserver',
				'SplSubject',

			// SPL Types - SPL Type Handling
			'SplType',
			'SplInt',
			'SplFloat',
			'SplEnum',
			'SplBool',
			'SplString',

			// Streams
			'php_user_filter',
			'streamWrapper',

			// Tidy
			'tidy',
			'tidyNode',

			// V8js - V8 Javascript Engine Integration
			'V8Js',
			'V8JsException',

			// Yaf
			'Yaf_Application',
			'Yaf_Bootstrap_Abstract',
			'Yaf_Dispatcher',
			'Yaf_Config_Abstract',
			'Yaf_Config_Ini',
			'Yaf_Config_Simple',
			'Yaf_Controller_Abstract',
			'Yaf_Action_Abstract',
			'Yaf_View_Interface',
			'Yaf_View_Simple',
			'Yaf_Loader',
			'Yaf_Plugin_Abstract',
			'Yaf_Registry',
			'Yaf_Request_Abstract',
			'Yaf_Request_Http',
			'Yaf_Request_Simple',
			'Yaf_Response_Abstract',
			'Yaf_Route_Interface',
			'Yaf_Route_Map',
			'Yaf_Route_Regex',
			'Yaf_Route_Rewrite',
			'Yaf_Router',
			'Yaf_Route_Simple',
			'Yaf_Route_Static',
			'Yaf_Route_Supervar',
			'Yaf_Session',
			'Yaf_Exception',
			'Yaf_Exception_TypeError',
			'Yaf_Exception_StartupError',
			'Yaf_Exception_DispatchFailed',
			'Yaf_Exception_RouterFailed',
			'Yaf_Exception_LoadFailed',
			'Yaf_Exception_LoadFailed_Module',
			'Yaf_Exception_LoadFailed_Controller',
			'Yaf_Exception_LoadFailed_Action',
			'Yaf_Exception_LoadFailed_View',


			/* == Other Services == */
			// AMQP - Deprecated ?
			'AMQPConnection',
			'AMQPChannel',
			'AMQPExchange',
			'AMQPQueue',
			'AMQPEnvelope',

			// chdb - Constant hash database
			'chdb',

			// Curl - Client URL Library
			'CURLFile',

			// Event
			'Event',
			'EventBase',
			'EventBuffer',
			'EventBufferEvent',
			'EventConfig',
			'EventDnsBase',
			'EventHttp',
			'EventHttpConnection',
			'EventHttpRequest',
			'EventListener',
			'EventSslContext',
			'EventUtil',

			// Gearman
			'GearmanClient',
			'GearmanJob',
			'GearmanTask',
			'GearmanWorker',
			'GearmanException',

			// HTTP
			'HttpDeflateStream',
			'HttpInflateStream',
			'HttpMessage',
			'HttpQueryString',
			'HttpRequest',
			'HttpRequestPool',
			'HttpResponse',

			// Hyperwave API
			'hw_api',
			'hw_api_attribute',
			'hw_api_content',
			'hw_api_error',
			'hw_api_object',
			'hw_api_reason',

			// Memcache
			'Memcache',

			// Memcached
			'Memcached',
			'MemcachedException',

			// RRD - RRDtool
			'RRDCreator',
			'RRDGraph',
			'RRDUpdater',

			// Simple Asynchronous Messaging
			'SAMConnection',
			'SAMMessage',

			// SNMP
			'SNMP',
			'SNMPException',

			// Stomp - Stomp Client
			'Stomp',
			'StompFrame',
			'StompException',

			// SVM - Support Vector Machine
			'SVM',
			'SVMModel',

			// Varnish
			'VarnishAdmin',
			'VarnishStat',
			'VarnishLog',

			// ZMQ - 0MQ messaging
			'ZMQ',
			'ZMQContext',
			'ZMQSocket',
			'ZMQPoll',
			'ZMQDevice',


			/* == Search Engine Extensions == */
			// Solr - Apache Solr
			'SolrUtils',
			'SolrInputDocument',
			'SolrDocument',
			'SolrDocumentField',
			'SolrObject',
			'SolrClient',
			'SolrResponse',
			'SolrQueryResponse',
			'SolrUpdateResponse',
			'SolrPingResponse',
			'SolrGenericResponse',
			'SolrParams',
			'SolrModifiableParams',
			'SolrQuery',
			'SolrDisMaxQuery',
			'SolrException',
			'SolrClientException',
			'SolrServerException',
			'SolrIllegalArgumentException',
			'SolrIllegalOperationException',

			// Sphinx - Sphinx Client
			'SphinxClient',

			// Swish Indexing
			'Swish',
			'SwishResult',
			'SwishResults',
			'SwishSearch',


			/* == Server Specific Extensions == */

			/* == Session Extensions == */
			// Sessions - Session Handling
			'SessionHandler',
			'SessionHandlerInterface',


			/* == Text Processing == */

			/* == Variable and Type Related Extensions == */
			// Quickhash
			'QuickHashIntSet',
			'QuickHashIntHash',
			'QuickHashStringIntHash',
			'QuickHashIntStringHash',

			// Reflection
			'Reflection',
			'ReflectionClass',
			'ReflectionZendExtension',
			'ReflectionExtension',
			'ReflectionFunction',
			'ReflectionFunctionAbstract',
			'ReflectionMethod',
			'ReflectionObject',
			'ReflectionParameter',
			'ReflectionProperty',
			'Reflector',
			'ReflectionException',


			/* == Web Services == */
			// OAuth
			'OAuth',
			'OAuthProvider',
			'OAuthException',

			// SCA
			'SCA',
			'SCA_LocalProxy',
			'SCA_SoapProxy',

			// SOAP
			'SoapClient',
			'SoapServer',
			'SoapFault',
			'SoapHeader',
			'SoapParam',
			'SoapVar',

			// YAR - Yet Another RPC Framework
			'Yar_Server',
			'Yar_Client',
			'Yar_Concurrent_Client',
			'Yar_Server_Exception',
			'Yar_Client_Exception',


			/* == Windows Only Extensions == */

			// COM - COM and .Net (Windows)
			'COM',
			'DOTNET',
			'VARIANT',
			'COMPersistHelper', // Not in PHP docs
			'com_exception', // Not in PHP docs
			'com_safearray_proxy', // Not in PHP docs


			/* == XML Manipulation == */
			// DOM - Document Object Model
			'DOMAttr',
			'DOMCdataSection',
			'DOMCharacterData',
			'DOMComment',
			'DOMDocument',
			'DOMDocumentFragment',
			'DOMDocumentType',
			'DOMElement',
			'DOMEntity',
			'DOMEntityReference',
			'DOMException',
			'DOMImplementation',
			'DOMNamedNodeMap',
			'DOMNode',
			'DOMNodeList',
			'DOMNotation',
			'DOMProcessingInstruction',
			'DOMText',
			'DOMXPath',

			'DOMConfiguration', // Not in PHP docs
			'DOMDocumentType', // Not in PHP docs
			'DOMDomError', // Not in PHP docs
			'DOMErrorHandler', // Not in PHP docs
			'DOMImplementationList', // Not in PHP docs
			'DOMImplementationSource', // Not in PHP docs
			'DOMLocator', // Not in PHP docs
			'DOMNameList', // Not in PHP docs
			'DOMNameSpaceNode', // Not in PHP docs
			'DOMStringExtend', // Not in PHP docs
			'DOMStringList', // Not in PHP docs
			'DOMTypeinfo', // Not in PHP docs
			'DOMUserDataHandler', // Not in PHP docs

			// libxml
			'libXMLError',

			// Service Data Objects
			'SDO_DAS_ChangeSummary',
			'SDO_DAS_DataFactory',
			'SDO_DAS_DataObject',
			'SDO_DAS_Setting',
			'SDO_DataFactory',
			'SDO_DataObject',
			'SDO_Exception',
			'SDO_List',
			'SDO_Model_Property',
			'SDO_Model_ReflectionDataObject',
			'SDO_Model_Type',
			'SDO_Sequence',

			// SDO Relational Data Access Service
			'SDO_DAS_Relational',

			// SDO XML Data Access Service
			'SDO_DAS_XML',
			'SDO_DAS_XML_Document',

			// SimpleXML
			'SimpleXMLElement',
			'SimpleXMLIterator',

			// XMLDiff — XML diff and merge
			'XMLDiff\Base',
			'XMLDiff\DOM',
			'XMLDiff\Memory',
			'XMLDiff\File',

			// XMLReader
			'XMLReader',

			// XMLWriter
			'XMLWriter',

			// XSL
			'XSLTProcessor',

		);
	} // End of class Debug_Bar_List_PHP_Classes
} // End of if class_exists wrapper
