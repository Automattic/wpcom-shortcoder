<?php
/*
 * Plugin Name: WP.com Shortcoder
 * Description: Automates the insertion of shortcodes from the post editor using the media uploader.
 * Version: 0.1
 * Author: Babak Azarpour, Automattic
 * License: GPLv2
 */
 
/*

// Example of how to use the new shortcode function

if ( function_exists( 'wpcom_add_shortcode' ) ) :
	wpcom_add_shortcode( 'sample_shortcoder', 'sample_callback', array(
		'name' => 'Sample Shortcoder',
		'description' => 'This is a description of the shortcode',
		'args' => array(
			'parameter1' => array(
				'description' => 'Description of parameter1',
				'required' => true,
				'example' => 'parameter1="true"'
			),
			'parameter2' => array(
				'description' => 'Description of parameter2',
				'required' => true,
				'example' => 'parameter2="true"'
			)
		)
	) );
endif;

*/

if ( ! class_exists( 'WPCOM_Shortcoder' ) ) :
	class WPCOM_Shortcoder {

		/**
		 * @var array $shortcodes
		 */
		protected static $shortcodes = array();
		
		/**
		 * Adds the required hooks / filters
		 */
		public function __construct() {
			if ( is_admin() ) {
				add_filter( 'media_upload_tabs', array( $this, 'add_media_upload_tab' ) );
				add_filter( 'media_upload_wpcom-shortcoder', array( $this, 'initiate_shortcoder_content' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'load_assets') );
			}
		}
		
		/**
		 * Gets the added shortcodes
		 * 
		 * @return array $shortcodes
		 */
		public static function get_shortcodes() {
			return self::$shortcodes;
		}
		
		/**
		 * Loads assets if we are on the post screen or the tab iframe
		 */
		public function load_assets() {
			global $pagenow, $post;
			
			if ( in_array( $pagenow, array( 'edit.php', 'post-new.php' ) ) ) {
				wp_enqueue_script( 'wpcom-shortcoder_script', plugins_url( 'wpcom-shortcoder.js', __FILE__ ) );
			} elseif ( $pagenow == 'media-upload.php' && isset( $_GET['tab'] ) && $_GET['tab'] == 'wpcom-shortcoder' ) {
				wp_enqueue_script( 'wpcom-shortcoder_script_iframe', plugins_url( 'wpcom-shortcoder-iframe.js', __FILE__ ) );
				wp_enqueue_style( 'wpcom-shortcoder_style', plugins_url( 'wpcom-shortcoder-iframe.css', __FILE__ ) );
				wp_enqueue_style( 'media-views' );
			}
		}
		
		/**
		 * Adds the Insert Shortcode tab to the media tabs
		 */
		public function add_media_upload_tab( $tabs ) {
			if ( ! empty( self::$shortcodes ) ) {
				return array_merge( $tabs, array(
					'wpcom-shortcoder' => 'Insert Shortcode'
				) );
			}
			
			return $tabs;
		}
		
		/**
		 * Registers the shortcode with the plugin and with Wordpress
		 * 
		 * @param string $tag
		 * @param mixed $callback
		 * @param array $options
		 */
		public static function add_shortcode( $tag, $callback, array $options = array() ) {
			add_shortcode( $tag, $callback );
			
			self::$shortcodes[$tag] = $options;
		}

		/**
		 * Initiates the request to display the shortcoder tab content
		 */
		public function initiate_shortcoder_content()
		{
			return wp_iframe( array( $this, 'media_upload_shortcoder' ) );
		}

		/**
		 * Display the shortcoder tab content
		 */
		public function media_upload_shortcoder() {
		?>
		<div id="wpcom-shortcoder" class="attachments-browser">
			<div id="wpcom-shortcoder-inputs" class="media-toolbar">
				<select>
					<option value="">All Shortcodes</option>
					<?php foreach ( WPCOM_Shortcoder::get_shortcodes() as $tag => $shortcode ):?>
						<option value="<?php echo esc_attr( $tag );?>"><?php echo esc_attr( $shortcode['name'] );?></option>
					<?php endforeach;?>
				</select>
			</div>
			<ul id="wpcom-shortcoder-shortcodes" class="attachments">
				<?php foreach ( WPCOM_Shortcoder::get_shortcodes() as $tag => $shortcode ):?>
					<li>
						<input type="radio" name="WPS[shortcode]" value="<?php echo esc_attr( $tag );?>" /> 
						<label><?php echo esc_attr( $shortcode['name'] );?></label>
						<?php if ( ! empty( $shortcode['description'] ) ) :?>
							<p>
								<?php echo esc_attr( $shortcode['description'] );?>
							</p>
						<?php endif;?>
						<?php if ( ! empty( $shortcode['args'] ) && is_array( $shortcode['args'] ) ) :?>
							<div class="wpcom-shortcoder-shortcode-options">
								<h2><?php echo esc_attr( $shortcode['name'] );?></h2>
								<?php if ( ! empty( $shortcode['description'] ) ) :?>
									<p>
										<?php echo esc_attr( $shortcode['description'] );?>
									</p>
								<?php endif;?>
								<span class="shortcode-option-required">*</span> required
								<ul class="wpcom-shortcoder-shortcode-options-list">
									<?php foreach( $shortcode['args'] as $id => $option ):?>
										<li>
											<div>
												<strong>
													<?php echo esc_attr( $id );?> 
													<?php if ( ! empty( $option['required'] ) ) echo '<span class="shortcode-option-required">*</span>';?>
												</strong>
											</div>
											<?php if ( ! empty( $option['description'] ) ):?>
												<div>
													<?php echo esc_attr( $option['description'] );?>
												</div>
											<?php endif;?>
											<?php if ( ! empty( $option['example'] ) ):?>
												<div>
													<span>Example:</span>
													<?php echo esc_attr( $option['example'] );?>
												</div>
											<?php endif;?>
										</li>
									<?php endforeach;?>
								</ul>
							</div>
						<?php endif;?>
					</li>
				<?php endforeach;?>
			</ul>
			<div class="media-sidebar" id="wpcom-shortcoder-sidebar"></div>
		</div>
		<?php
		}
	}

	new WPCOM_Shortcoder;

endif;

if ( ! function_exists( 'wpcom_add_shortcode' ) ) :

	/**
	 * Adds a shortcode to both the Shortcoder and the native add shortcode function
	 * 
	 * @param string $tag
	 * @param mixed $callback
	 * @param array $options
	 */
	function wpcom_add_shortcode( $tag, $callback, array $options = array() ) {
		WPCOM_Shortcoder::add_shortcode( $tag, $callback, $options );
	}

endif;
