<?php
/**
 * @package   WPGlobus
 */

/**
 * Class WPGlobus_Options
 * Based on ReduxFramework Sample Config File
 * For full documentation, please visit: https://docs.reduxframework.com
 */
class WPGlobus_Options {

	public $args = array();
	public $sections = array();
	public $theme;
	public $ReduxFramework;

	private $menus = array();

	/**
	 * Constructor
	 */
	public function __construct() {

		if ( ! class_exists( 'ReduxFramework' ) ) {
			return;
		}

		$nav_menus = WPGlobus::_get_nav_menus();

		foreach ( $nav_menus as $menu ) {
			$this->menus[ $menu->slug ] = $menu->name;
		}
		if ( ! empty( $nav_menus ) && count( $nav_menus ) > 1 ) {
			$this->menus['all'] = 'All';
		}

		// This is needed. Bah WordPress bugs.  ;)
		//            if (  true == Redux_Helpers::isTheme(__FILE__) ) {
		//                $this->initSettings();
		//            } else {
		$this->initSettings();
		//                add_action('plugins_loaded', array($this, 'initSettings'), 10);
		//            }

		/** remove redux menu under the tools **/
		add_action( 'admin_menu', array( $this, 'remove_redux_menu' ), 12 );

	}

	public function remove_redux_menu() {
		remove_submenu_page( 'tools.php', 'redux-about' );
	}

	public function initSettings() {

		// Set the default arguments
		$this->setArguments();

		// Set a few help tabs so you can see how it's done
		$this->setHelpTabs();

		// Create the sections and fields
		$this->setSections();

		if ( ! isset( $this->args['opt_name'] ) ) { // No errors please
			return;
		}

		$this->ReduxFramework = new ReduxFramework( $this->sections, $this->args );
	}

	public function setSections() {

		$config = WPGlobus::Config();

		$wpglobus_option = get_option( $config->option );


		$fields_home = array();

		/**
		 * Display warning if an old Redux is loaded
		 * @todo Add link to FAQ explaining what to do.
		 * @todo Tell the admin what did we load (plugin or someone else's Redux)
		 */
		if ( version_compare( ReduxFramework::$_version, WPGlobus::$minimalReduxFramework_version ) < 0 ) {
			$fields_home[] =
				array(
					'id'     => 'version_warning',
					'type'   => 'info',
					'title'  => __( 'WARNING: Redux Framework upgrade is highly recommended!', 'wpglobus' ),
					'desc'   => sprintf(
						__( 'WPGlobus administration panel requires Redux Framework %2$s or later. The version you have installed is %1$s.' ),
						ReduxFramework::$_version,
						WPGlobus::$minimalReduxFramework_version
					),
					'style'  => 'critical',
					'notice' => true,
				);
		}

		/**
		 * The Welcome message
		 * @todo Link to Contact Us (site, Github)
		 */
		$fields_home[] =
			array(
				'id'     => 'welcome_intro',
				'type'   => 'info',
				'title'  => __( 'Thank you for installing WPGlobus!', 'wpglobus' ),
				'desc'   => '' .
				            '<br/>' .
				            '&bull; ' .
				            '<a href="' . admin_url() . 'admin.php?page=' . WPGlobus::PAGE_WPGLOBUS_ABOUT . '">' .
				            __( 'Read About WPGlobus', 'wpglobus' ) .
				            '</a>' .
				            '<br/>' .
				            '&bull; ' . __( 'Click the <strong>[Languages]</strong> tab at the left to setup the various options.', 'wpglobus' ) .
				            '<br/>' .
				            '&bull; ' . __( 'Use the <strong>[Languages Table]</strong> section to add a new language or to edit the language attributes: name, code, flag icon, etc.', 'wpglobus' ) .
				            '<br/>' .
				            '<br/>' .
				            __( 'Should you have any questions or comments, please do not hesitate to contact us.', 'wpglobus' ) .
				            '<br/>' .
				            '<br/>' .
				            '<em>' .
				            __( 'Sincerely Yours,', 'wpglobus' ) .
				            '<br/>' .
				            __( 'The WPGlobus Team', 'wpglobus' ) .
				            '</em>' .
				            '',
				'style'  => 'info',
				'notice' => false,
			);

		$this->sections[] = array(
			'title'  => __( 'Welcome!', 'wpglobus' ),
			'icon'   => 'el-icon-globe',
			'fields' => $fields_home
		);

		/*
		 * SECTION: languages
		 */

		/** @var array $enabled_languages contains all enabled languages */
		$enabled_languages = array();

		/** @var array $defaults_for_enabled_languages Need for the sortable field setup */
		$defaults_for_enabled_languages = array();

		/** @var array $more_languages */
		$more_languages = array();

		foreach ( $config->enabled_languages as $code ) {
			$lang_in_en = '';
			if ( isset( $config->en_language_name[ $code ] ) && ! empty( $config->en_language_name[ $code ] ) ) {
				$lang_in_en = ' (' . $config->en_language_name[ $code ] . ')';
			}

			$enabled_languages[ $code ] = $config->language_name[ $code ] . $lang_in_en;
			$defaults_for_enabled_languages[ $code ] = true;
		}

		/** Add language from 'more_language' option to array $enabled_languages */
		if ( isset( $wpglobus_option['more_languages'] ) && ! empty( $wpglobus_option['more_languages'] ) ) {

			$lang       = $wpglobus_option['more_languages'];
			$lang_in_en = '';
			if ( isset( $config->en_language_name[ $lang ] ) && ! empty( $config->en_language_name[ $lang ] ) ) {
				$lang_in_en = ' (' . $config->en_language_name[ $lang ] . ')';
			}

			$enabled_languages[ $lang ] = $config->language_name[ $lang ] . $lang_in_en;

			$wpglobus_option['enabled_languages'][ $wpglobus_option['more_languages'] ] =
				$config->language_name[ $wpglobus_option['more_languages'] ];
			update_option( $config->option, $wpglobus_option );

		}

		/** Generate array $more_languages */
		foreach ( $config->flag as $code => $file ) {
			if ( ! array_key_exists( $code, $enabled_languages ) ) {
				$lang_in_en = '';
				if ( isset( $config->en_language_name[ $code ] ) && ! empty( $config->en_language_name[ $code ] ) ) {
					$lang_in_en = ' (' . $config->en_language_name[ $code ] . ')';
				}
				$more_languages[ $code ] = $config->language_name[ $code ] . $lang_in_en;
			}
		}


		/*
		 * for miniGLOBUS
		 */
		if ( empty( $this->menus ) ) {
			$navigation_menu_placeholder = __( 'No navigation menu', 'wpglobus' );
		} else {
			$navigation_menu_placeholder = __( 'Select navigation menu', 'wpglobus' );
		}

		$desc_enabled_languages = join( '', array(
			'<strong>' . __( 'Instructions:', 'wpglobus' ) . '</strong>',
			'<ul style="list-style: disc; list-style-position: inside;">',
			'<li>' . sprintf( __( 'Place the <strong>main language</strong> of your site at the top of the list by dragging the %s icons.', 'wpglobus' ), '<i class="el el-move icon-large"></i>' ) . '</li>',
			'<li>' . __( '<strong>Uncheck</strong> the languages you do not plan to use.', 'wpglobus' ) . '</li>',
			'<li>' . __( '<strong>Add</strong> more languages using the section below.', 'wpglobus' ) . '</li>',
			'<li>' . __( 'When done, click the [Save Changes] button.', 'wpglobus' ) . '</li>',
			'<li>' . '<a href="#" onclick="location.reload(true); return false;">' .
			__( 'Reload', 'wpglobus' ) . '</a> ' .
			__( 'the screen to see the updated settings.', 'wpglobus' ) . '</li>',
			'</ul>'
		) );

		$desc_more_languages =
			__( 'Choose a language you would like to enable. <br>Press the [Save Changes] button to confirm.',
				'wpglobus' ) . '<br /><br />';
		$desc_more_languages .= sprintf( __( 'or Add new Language %1s here %2s', 'wpglobus' ),
			'<a href="?page=wpglobus_language_edit&action=add">', '</a>' );

		$this->sections[] = array(
			'title'  => __( 'Languages', 'wpglobus' ),
			'icon'   => 'el-icon-wrench-alt',
			'fields' => array(
				array(
					'id'          => 'enabled_languages',
					'type'        => 'sortable',
					'title'       => __( 'Enabled Languages', 'wpglobus' ),
					'compiler'    => 'false',
					'desc'        => $desc_enabled_languages,
					'subtitle'    => __( 'These languages are currently enabled on your site.', 'wpglobus' ),
					'options'     => $enabled_languages,
					'default'     => $defaults_for_enabled_languages,
					'mode'        => 'checkbox',
				),
				array(
					'id'          => 'more_languages',
					'type'        => 'select',
					'title'       => __( 'Add Languages', 'wpglobus' ),
					'compiler'    => 'false',
					'mode'        => false,
					'desc'        => $desc_more_languages,
					'placeholder' => __( 'Select a language', 'wpglobus' ),
					'options'     => $more_languages,
				),
				array(
					'id'       => 'show_flag_name',
					'type'     => 'select',
					'title'    => __( 'Language Selector Mode', 'wpglobus' ),
					'compiler' => 'false',
					'mode'     => false,
					'desc'     => __( 'Choose the way language name and country flag are shown in the drop-down menu', 'wpglobus' ),
					'select2'  => array(
						'allowClear'              => false,
						'minimumResultsForSearch' => - 1
					),
					'options'  => array(
						'code'  => __( 'Two-letter Code (en, ru, it, etc.)', 'wpglobus' ),
						'name'  => __( 'Full Name (English, Russian, Italian, etc.)', 'wpglobus' ),
						'empty' => __( 'Flags only', 'wpglobus' )
					),
					'default'  => 'code'
				),
				array(
					'id'          => 'use_nav_menu',
					# $WPGlobus_Config->nav_menu
					'type'        => 'select',
					'title'       => __( 'Language Selector Menu', 'wpglobus' ),
					'compiler'    => 'false',
					'mode'        => false,
					'desc'        => __( 'Choose the navigation menu where the language selector will be shown', 'wpglobus' ),
					'select2'     => array(
						'allowClear'              => true,
						'minimumResultsForSearch' => - 1
					),
					'options'     => $this->menus,
					'placeholder' => $navigation_menu_placeholder,
				),
				array(
					'id'       => 'selector_wp_list_pages',
					'type'     => 'checkbox',
					'title'    => __( '"All Pages" menus Language selector', 'wpglobus' ),
					'subtitle' => __( '(Found in some themes)', 'wpglobus' ),
					'desc'     => __( 'Adds language selector to the menus that automatically list all existing pages (using `wp_list_pages`)', 'wpglobus' ),
					'compiler' => 'false',
					'default'  => 1,
					'options'  => array(
						'show_selector' => __( 'Enable', 'wpglobus' )
					),
				),
				array(
					'id'       => 'css_editor',
					'type'     => 'ace_editor',
					'title'    => __( 'Custom CSS', 'wpglobus' ),
					'mode'     => 'css',
					'theme'    => 'chrome',
					'compiler' => 'false',
					'desc'     => __( 'Here you can enter the CSS rules to adjust the language selector menu for your theme. Look at the examples in the `style-samples.css` file.', 'wpglobus' ),
					'subtitle' => __( '(Optional)', 'wpglobus' ),
					'default'  => '',
					'rows'     => 15
				)
			)
		);

		/*
		*	SECTION: Language table
		*/
		$this->sections[] = array(
			'title'  => __( 'Languages table', 'wpglobus' ),
			'icon'   => 'el-icon-th-list',
			'fields' => array(
				array(
					'id'       => 'description',
					'type'     => 'info',
					'title'    => __( 'Use this table to add, edit or delete languages.', 'wpglobus' ),
					'subtitle' => __( 'NOTE: you cannot remove the main language.', 'wpglobus' ),
					'style'    => 'info',
				),
				array(
					'id'   => 'lang_new',
					'type' => 'table'
				)
			)
		);

		/**
		 *    SECTION: Post types
		 */
		$post_types = get_post_types( array( '_builtin' => true ) );

		$fields = array(
			array(
				'id'    => 'description',
				'type'  => 'info',
				'title' => __( 'Uncheck to disable WPGlobus', 'wpglobus' ),
				'style' => 'info',
			),
		);

		$default         = array();
		$open_post_types = array();
		foreach ( $post_types as $post_type ) {
			if ( ! in_array( $post_type, array( 'attachment', 'revision', 'nav_menu_item' ) ) ) {
				$open_post_types[ $post_type ] = $post_type;
				$default[ $post_type ]         = true;
			}
		}

		$fields[] = array(
			'id'       => 'post_type',
			'type'     => 'checkbox',
			'compiler' => false,
			'default'  => $default,
			'options'  => $open_post_types
		);

		$fields[] = array(
			'id'   => 'custom_post_types',
			'type' => 'post_types'
		);

		$this->sections[] = array(
			'title'  => __( 'Post types', 'wpglobus' ),
			'icon'   => 'el-icon-th-list',
			'fields' => $fields
		);

		/**
		 * SECTION: Add-ons
		 * We need add it for menu item only
		 */		
		$this->sections[] = array(
			'title'  => __( 'Add-ons', 'wpglobus' ),
			'icon'   => 'el-icon-th-list',
			'class'	 => 'wpglobus-addons-group hidden'
		);
	
		/**
		 * Filter the array of sections.
		 * @since 1.0.11
		 *
		 * @param array $sections Array of Redux sections.
		 */	
		$this->sections = apply_filters( 'wpglobus_option_sections', $this->sections );
		
	}

	public function setHelpTabs() {
		$this->args['help_tabs']    = array();
		$this->args['help_sidebar'] = '';
	}

	/**
	 * All the possible arguments for Redux.
	 * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
	 **/
	public function setArguments() {

		$this->args = array(
			// TYPICAL -> Change these values as you need/desire
			'opt_name'           => WPGlobus::Config()->option,
			// This is where your data is stored in the database and also becomes your global variable name.
			'display_name'       => 'WPGlobus',
			// Name that appears at the top of your panel
			'display_version'    => WPGLOBUS_VERSION,
			// Version that appears at the top of your panel
			'menu_type'          => 'menu',
			//Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
			'allow_sub_menu'     => true,
			// Show the sections below the admin menu item or not
			'menu_title'         => __( 'WPGlobus', 'wpglobus' ),
			'page_title'         => __( 'WPGlobus', 'wpglobus' ),
			// You will need to generate a Google API key to use this feature.
			// Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
			'google_api_key'     => '',
			// Must be defined to add google fonts to the typography module

			'async_typography'   => false,
			// Use a asynchronous font on the front end or font string
			'admin_bar'          => false,
			// Show the panel pages on the admin bar
			'global_variable'    => '',
			// Set a different name for your global variable other than the opt_name
			'dev_mode'           => false,
			// Show the time the page took to load, etc
			'customizer'         => true,
			// Enable basic customizer support

			// OPTIONAL -> Give you extra features
			'page_priority'      => null,
			// Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
			'page_parent'        => 'themes.php',
			// For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
			'page_permissions'   => 'manage_options',
			// Permissions needed to access the options panel.
			'menu_icon'          => '',
			// Specify a custom URL to an icon
			'last_tab'           => '',
			// Force your panel to always open to a specific tab (by id)
			'page_icon'          => 'icon-themes',
			// Icon displayed in the admin panel next to your menu_title
			'page_slug'          => WPGlobus::OPTIONS_PAGE_SLUG,
			// Page slug used to denote the panel
			'save_defaults'      => true,
			// On load save the defaults to DB before user clicks save or not
			'default_show'       => false,
			// If true, shows the default value next to each field that is not the default value.
			'default_mark'       => '',
			// What to print by the field's title if the value shown is default. Suggested: *
			'show_import_export' => false,
			// Shows the Import/Export panel when not used as a field.

			// CAREFUL -> These options are for advanced use only
			'transient_time'     => 60 * MINUTE_IN_SECONDS,
			'output'             => true,
			// Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
			'output_tag'         => true,
			// Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
			'footer_credit'     => '&copy; Copyright 2014-' . date( 'Y' ) .
			                       ', <a href="' . WPGlobus::URL_WPGLOBUS_SITE . '">WPGlobus</a>.',

			'database'           => 'options',
			// possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
			'system_info'        => false,
			// REMOVE

			'hide_reset' => TRUE,
			'disable_tracking' => true,


			// HINTS
			'hints'              => array(
				'icon'          => 'icon-question-sign',
				'icon_position' => 'right',
				'icon_color'    => 'lightgray',
				'icon_size'     => 'normal',
				'tip_style'     => array(
					'color'   => 'light',
					'shadow'  => true,
					'rounded' => false,
					'style'   => '',
				),
				'tip_position'  => array(
					'my' => 'top left',
					'at' => 'bottom right',
				),
				'tip_effect'    => array(
					'show' => array(
						'effect'   => 'slide',
						'duration' => '500',
						'event'    => 'mouseover',
					),
					'hide' => array(
						'effect'   => 'slide',
						'duration' => '500',
						'event'    => 'click mouseleave',
					),
				),
			)
		);
				
		$donate_1 = '<div style="float:left;width:50%;">
						<p style="float:left;width:50%;position:relative;top:50%;transform:translateY(25%);">' .
						   __( 'If you like WPGlobus, please consider a small donation to support the future development.', 'wpglobus' ) . 
						'</p>' . 
						'<div style="float:left;">
							<form id="wpglobus-donate" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
								<input type="hidden" name="cmd" value="_s-xclick">
								<input type="hidden" name="hosted_button_id" value="SLF8M4YNZHNQN">
								<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif" border="0" name="submit" alt="PayPal">
								<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
							</form>
						</div>
					</div>
					<div style="clear:both;"></div>';			
					
		$donate_2 = '<div style="float:left;width:50%;">
						<p style="float:left;width:50%;position:relative;top:50%;transform:translateY(25%);">' .
						   __( 'If you like WPGlobus, please consider a small donation to support the future development.', 'wpglobus' ) . 
						'</p>' . 
						'<div style="float:left;">
							<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=SLF8M4YNZHNQN"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif" /></a>
						</div>
					</div>
					<div style="clear:both;"></div>';						
					
		$this->args['intro_text'] =
			'<h1 style="width:30%;float:left;">' . __( 'WPGlobus', 'wpglobus' ) . ' ' . WPGLOBUS_VERSION . '</h1>' .  $donate_2;

		// Add content after the form.
//		$this->args['footer_text'] =
//			'&copy; Copyright 2014-' . date( 'Y' ) . ', <a href="' . WPGlobus::URL_WPGLOBUS_SITE . '">WPGlobus</a>.';
	}

} // class

# --- EOF