<?php
/*
	Plugin Name: Bluerange Google Analytics Manager
	Plugin URI: 
	Description: Add Your Google Analytics.
	Text Domain: bluerange-google-analytics-manager 
	Domain Path: /languages
	Author: Hanna Hansson @ Bluerange
	Author URI: 
	License: GPLv2
	Version: 1.0.0
*/

// BASIC SECURITY
defined( 'ABSPATH' ) or die( 'Unauthorized Access!' );

if ( !defined('BR_GA_PLUGIN_DIR') ) {
	
	//DEFINE SOME USEFUL CONSTANTS
	define( 'BR_GA_PLUGIN_VER', '1.0.0' );
	define( 'BR_GA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'BR_GA_PLUGINS_URL', plugins_url( '', __FILE__ ) );
	define( 'BR_GA_PLUGINS_BASENAME', plugin_basename(__FILE__) );
	define( 'BR_GA_PLUGIN_FILE', __FILE__ );
	define( 'BR_GA_PLUGIN_PACKAGE', 'Free' ); //DONT CHANGE THIS - BREAKS AUTO UPDATER
	
	//LOAD MODULES
	include_once( BR_GA_PLUGIN_DIR . '/includes/editor/editor.php' );

	//INSERT SCRIPT
	function br_ga_maybe_add_script() {

		$roles = wp_get_current_user()->roles;
		
		$options = get_option( 'br_ga', true );
		$id = empty ( $options['id'] ) ? '' : $options['id'];
		$exclude = empty ( $options['exclude'] ) ? array() : $options['exclude'];
		$do_script = count( array_intersect( array_map( 'strtolower', $roles), array_map( 'strtolower', $exclude ) ) ) == 0;
				
		if ( !empty( $options['id'] ) && $do_script ) {
			
			ob_start(); ?>
			
			<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

			ga('create', '<?php echo $id ?>', 'auto');
			ga('send', 'pageview');

			</script>
			
			<?php
			echo ob_get_clean();
		}
	}
	add_action('wp_head', 'br_ga_maybe_add_script');
	
	////////////////////////////
	// LOCALIZATION
	////////////////////////////
	
	function br_ga_load_localization() {
		load_plugin_textdomain( 'br-ga', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	add_action( 'init', 'br_ga_load_localization' );
	
	////////////////////////////
	// FUNCTIONS
	////////////////////////////
		
	//RETURN GENERIC INPUT HTML
	function br_ga_input ( $name, $placeholder = '', $value = '', $type = 'text' ) {
	
		$html = "<div class='br-ga-field br-ga-field-$type'>";
		
			switch ( $type ) {
				
				case 'checkbox':
					$checked = !empty( $value ) ? "checked='checked'" : '';
					
					$html .= "<div class='onoffswitch'>";
						$html .= "<input style='display:none;' type='checkbox' id='br_ga[$name]' class='onoffswitch-checkbox br-ga-input-$type br-ga-$name' name='br_ga[$name]' $checked>"; 
						$html .= "<label class='onoffswitch-label' for='br_ga[$name]'><span class='onoffswitch-inner' data-content-on='ON' data-content-off='OFF'><span class='onoffswitch-switch'></span></span></label>";
					$html .= "</div>";
					break;
					
				case 'textarea':
					$html .= "<textarea placeholder='$placeholder' class='br-ga-input-$type br-ga-$name' name='br_ga[$name]'>$value</textarea>";
					break;
					
				case 'image':
					$html .= "<input type='hidden' class='br-ga-input-$type br-ga-$name' name='br_ga[$name]' value='$value'>";
					$html .= "<button type='button' class='button-secondary br_ga_image_upload_btn'>" . __('Add Image', 'br-ga') . "</button>";
					$html .= "<img class='br_ga_image' style='max-width: 252px' src='$value'>";
			
					$html .= "<div class='br_ga_image_hover_controls'>";
						$html .= "<button type='button' class='button-secondary br_ga_image_change_btn'>" . __('Change', 'br-ga') . "</button>";
						$html .= "<button type='button' class='button-secondary br_ga_image_revert_btn'>" . __('Remove', 'br-ga') . "</button>";
					$html .=  '</div>';
					break;
				case 'color':
					$html .= "<input type='hidden' placeholder='$placeholder' class='br-ga-input-$type br-ga-$name' name='br_ga[$name]' value='$value'>";
					break;
				case 'editor':
					ob_start();
					wp_editor( $value, $name, array() );
					$html .= ob_get_clean();
					break;
				case 'datepicker':
					$html .= "<input type='text' placeholder='$placeholder' class='br-ga-input-$type br-ga-$name' name='br_ga[$name]' value='$value'>";
					break;
				case 'roles':
					$roles = get_editable_roles();
					forEach ( $roles as $role ) {
						$options[] = $role['name'];
					}

                    $html = "<select name='br_ga[$name][]' data-placeholder='$placeholder' multiple='multiple' style='width: 100%; border: 1px solid #ddd; border-radius: 0;' class='br-ga-multiselect'>";
						forEach ( $options as $role ) {
							if ( in_array($role, $value) ) {
								$html .= "<option value='$role' selected='selected'>$role</option>";
							} else {
								$html .= "<option value='$role'>$role</option>";
							}
						}
					
					$html .= "</select>";
					break;
					
				default: 
					$html .= "<input type='$type' placeholder='$placeholder' class='br-ga-input-$type br-ga-$name' name='br_ga[$name]' value='$value'>";
			}
		
		$html .= '</div>';
		
		return $html;
	}
	
	function br_ga_tooltip( $text = 'Tooltip', $icon = 'dashicons dashicons-editor-help' ) {
		return "<span class='$icon br_ga_tooltip' title='" . htmlentities( $text ) . "'></span>";
	}
	
	function br_ga_convert_entities ( $array ) {
		$array = is_array($array) ? array_map('br_ga_convert_entities', $array) : html_entity_decode( $array, ENT_QUOTES );
		return $array;
	}

	function br_ga_escape_input ($data) {
		
		if ( is_array ( $data ) ) {
			forEach ( $data as $k => $v ) {
				$data[$k] = br_ga_escape_input($v);
			}
			return $data;
		}
		
		$data = wp_kses_post( $data );
			
		return $data;

	}
	
	function br_ga_add_plugin_action_links( $links ) {
		
		$url = admin_url('options-general.php?page=br_ga_settings_page');
		
		$new_links = array(
			'configure' => "<a href='$url' >" . __('Configure Google Analytics', 'br-ga' ) . '</a>'
		);
		
		$links = array_merge( $new_links, $links );
	
		return $links;
		
	}
	add_filter( 'plugin_action_links_' . BR_GA_PLUGINS_BASENAME, 'br_ga_add_plugin_action_links' );
	
	//ADD NAG IF NO GA TRACKING CODE IS SET
	function br_ga_admin_notice() {
		$options = get_option( 'br_ga', true );
 
		if ( empty( $options['id'] ) ) {
			$url = admin_url( 'options-general.php?page=br_ga_settings_page' );
		
			echo '<div id="br-ga-setup-notice" class="notice notice-success is-dismissible" style="padding-bottom: 8px; padding-top: 8px;">';
				echo '<img style="float:left; margin-right: 16px;" height="120" width="120" src="' . BR_GA_PLUGINS_URL . '/assets/br-ga-logo.jpg' . '">';
				echo '<p><strong>' . __( "Thank you for installing Bluerange Google Analytics.", 'br-ga' ) . '</strong></p>';
				echo '<p>' . __( "Ready to get started?", 'br-ga' ) . '</p>';
				echo "<a href='$url' type='button' class='button button-primary' style='margin-top: 25px;'>" . __( 'Set up Google Analytics', 'br-ga' ) . "</a> ";
				echo '<br style="clear:both">';
			echo '</div>';
        }	
	}
	add_action( 'admin_notices', 'br_ga_admin_notice' );
	
}
?>