<?php
	
////////////////////////////
// SETTINGS PAGE 
////////////////////////////

function br_ga_plugin_menu() {
	add_options_page( 
		__( 'Google Analytics Manager', 'br-ga' ),
		__( 'Google Analytics Manager', 'br-ga' ),
		'manage_options',
		'br_ga_settings_page',
		'br_ga_settings_page'
	);
}
add_action( 'admin_menu', 'br_ga_plugin_menu' );

//ENQUEUE ANY SCRIPTS OR CSS FOR OUR ADMIN PAGE EDITOR
function br_ga_admin_enqueue() {

	wp_enqueue_style('dashicons');
	wp_enqueue_script('jquery');
	wp_enqueue_script( 'br_ga_select2', BR_GA_PLUGINS_URL . '/includes/select2/select2.min.js', array(), BR_GA_PLUGIN_VER, true );
	wp_enqueue_style( 'br_ga_select2', BR_GA_PLUGINS_URL . '/includes/select2/select2.min.css', array(), BR_GA_PLUGIN_VER );
			
	wp_enqueue_script('br_ga_admin_js', BR_GA_PLUGINS_URL . '/includes/editor/admin.min.js', array( 'jquery', 'br_ga_select2' ), BR_GA_PLUGIN_VER, true );		
	wp_enqueue_style( 'br_ga_admin_stylesheet', BR_GA_PLUGINS_URL . '/includes/editor/admin.min.css', array(), BR_GA_PLUGIN_VER );
	
	$admin_data = array (
		'ajaxurl' => admin_url ( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'br_ga_admin_nonce' ),
	);
	
	wp_localize_script( 'br_ga_admin_js', 'adminData', $admin_data );
	
}

function br_ga_admin_notice_save() {
	echo '<div id="br-ga-notice-save" style="padding-bottom: 10px;" class="notice notice-success is-dismissible">';
		echo '<p><strong>' . __( "Settings saved.", 'br-ga' ) . '</strong></p>';
	echo '</div>';
}

function br_ga_settings_page() {
    echo '<div class="wrap" id="br-ga-group-wrap">';
    echo '<div class="br-columns-2">';
    //ADDIND SCRIPTS
	br_ga_admin_enqueue();
	
	if ( isSet( $_POST['br_ga_save'] ) ) { 
		br_ga_settings_save();	
		br_ga_admin_notice_save();		
	}	
	$options = get_option( 'br_ga', true );
	$id = empty ( $options['id'] ) ? '' : $options['id'];
	$exclude = empty ( $options['exclude'] ) ? array() : $options['exclude'];
	//DEFAULT EXCLUDE TO ADMIN & EDITOR
	$exclude = empty ( $options['has_save'] ) ? array( 'Administrator', 'Editor' ) : $exclude;
    echo '<div class="br-column-2"><div class="br-box"><div class="inner">';
    echo '<img style="float:right;margin-left:15px;" height="120" width="120" src="' . BR_GA_PLUGINS_URL . '/assets/icon-128×128.png' . '">';
    echo '<h1 class="wp-heading">' .  __('Google Analytics Manager', 'br-ga') . '</h1>';
    echo '<h4 style="margin-top:0">' .  __('by Bluerange Sweden AB', 'br-ga') . '</h4>';
    echo '<p>' . __('Google Analytics Manager is a lean, fast, simple, no-frills way to add your Google Analytics code to your WordPress site', 'br-ga') . '.</p>';
    echo '</div><div class="footer">';
    echo '<p>' . __('Thanks for using Google Analytics Manager', 'br-ga') . '.</p>';
    echo '</div></div></div>';   
    
   
    $html = '<form action="" method="post" id="br_ga_main_form">';
		
		$html .= '<h1>' .  __('Google Analytics Manager', 'br-ga') . '</h1>';
		$html .= '<p>' . sprintf(  __('Need help? %1$sRead our quick-start guide.%2$s', 'br-ga'), '<a href="https://support.bluerange.se/lagg-till-google-analytics/"" target="_blank">', '</a>' ) . '</p>';
		
		//ADD A HIDDEN INPUT TO DETERMINE IF WE HAVE AN EMPTY SAVE OR NOT
		$html .= br_ga_input ( 'has_save', '', true, 'hidden' );
		
		$html .= '<table class="br_ga_setting_table" >';
			$html .= "<tr>";
				$html .= '<th width="150">' . __('Google Analytics ID', 'br-ga') . '</th>';
				$html .= '<td id="br-ga-helptext" title="' . __('Your Google Analytics ID should only contain numbers', 'br-ga') . '" >' . br_ga_input ( 'id', 'e.g. UA-12345678-1', $id, 'text' );
				$html .= '<p class="br_ga_hint"><a href="https://support.bluerange.se/lagg-till-google-analytics/#ga-id" target="_blank">' . __( 'What is my Google Analytics ID?', 'br-ga' ) . '</a></p>';
				$html .= '</td>';
            $html .= "</tr>";
            $html .= "<tr><td height='30' colspan='2'></td></tr>";
			$html .= "<tr>";
				$html .= '<th>' . __('Exclude Users', 'br-ga') . '</th>';
				$html .= '<td>' . br_ga_input ( 'exclude', '', $exclude, 'roles' );
				$html .= '<p class="br_ga_hint">' . __( 'Logged in users selected above will not trigger analytics tracking.', 'br-ga' ) . '</p>';
				$html .= '</td>';
			$html .= "</tr>";

		$html .= '</table>';
		
		$html .= '<button type="submit" name="br_ga_save" class="button button-primary">' . __('Save', 'br-ga') . '</button>';
	
	$html .= '</form>';
	
	
    echo $html;

    echo '</div></div>';
}

function br_ga_settings_save() {
	$data = br_ga_escape_input ( $_POST['br_ga'] );
	update_option( 'br_ga', $data );
}