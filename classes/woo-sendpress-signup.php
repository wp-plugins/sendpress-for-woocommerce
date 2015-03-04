<?php
class SendPress_Woo_Signup{

	private static $instance;

	function __construct() {
		add_action('plugins_loaded',array( $this, 'load' ));
	}

	function load(){
		if( $this->check_required_classes() ){
			add_action('init',array( $this, 'init' ));
		}
	}

	function init(){
		//both plugins are here, lets set up everything
		add_filter('woocommerce_get_settings_checkout', array( $this, 'add_settings' ));

		add_action('woocommerce_after_order_notes', array( $this, 'add_form_fields' ), 100);
		add_action('woocommerce_checkout_process', array( $this, 'sendpress_woo_check_for_email_signup'), 10);

	}

	static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			$class_name = __CLASS__;
			self::$instance = new $class_name;
		}
		return self::$instance;
	}

	// adds the settings to the Misc section
	function add_settings($settings, $current_section) {
	  
	 //  	echo '<pre>';
		// print_r($settings);
		// echo '</pre>';

		array_unshift($settings,
			array(	'title' => __( 'SendPress', 'woocommerce' ), 'type' => 'title', 'id' => 'checkout_process_sendpress_options' ),

			array(
				'title'    => __( 'Enable', 'woocommerce' ),
				'desc'     => __( 'Allow users to signup for SendPress newsletters', 'woocommerce' ),
				'id'       => 'woocommerce_sendpress_enable',
				'default'  => 'no',
				'type'     => 'checkbox',
				//'desc_tip' =>  __( 'test description', 'woocommerce' ),
				'autoload' => false
			),

			array(
				'title'    => __( 'List', 'woocommerce' ),
				'desc'     => __( 'Select the list you would like customers to subscribe to.', 'woocommerce' ),
				'id'       => 'woocommerce_sendpress_list_id',
				'default'  => '',
				'class'    => 'chosen_select_nostd',
				'css'      => 'min-width:300px;',
				'type'     => 'select',
				'options'  => $this->get_sendpress_lists(),
				'desc_tip' => true,
				'autoload' => false
			),

			//select
			

			array( 'type' => 'sectionend', 'id' => 'checkout_process_sendpress_options')
		);

		return $settings;
	}

	function get_sendpress_lists(){

		$lists = array();
		$lists[0] = "-- Select a List --";
		$args = array(
        	'post_type' => 'sendpress_list',
        	'post_status' => array('publish','draft')
        );
        $query = new WP_Query( $args );

		if($query->have_posts()){
			while($query->have_posts()){
				$query->the_post();
				$lists[get_the_id()] = get_the_title();
			}
		}
		wp_reset_postdata();

		return $lists;

	}

	// displays the sendpress checkbox
	function add_form_fields($checkout) {
		//global $edd_options;
		$listid = get_option('woocommerce_sendpress_list_id');
		$enabled = (get_option('woocommerce_sendpress_enable') === 'yes') ? true : false;

		if( $enabled && $listid > 0 ){
			echo '<div id="woocommerce_sendpress_checkout_field">';
 
		    woocommerce_form_field( 'woocommerce_sendpress_signup', array(
		        'type'          => 'checkbox',
		        'class'         => array('sendpress-checkout-class form-row-wide'),
		        'label'         => __('Sign up for our mailing list'),
		        ), $checkout->get_value( 'woocommerce_sendpress_signup' ));
		 
		    echo '</div>';
		}

	}

	// checks whether a user should be signed up for the sendpress list
	function sendpress_woo_check_for_email_signup() {

		global $woocommerce;

		$subscribe = isset($_POST['woocommerce_sendpress_signup']) ? true : false;
		$listid = get_option('woocommerce_sendpress_list_id');
		// If the check box has been ticked then the customer is added to the MailPoet lists enabled.
		if($subscribe && $listid > 0){
			

			$email = $_POST['billing_email'];
			$first = $_POST['billing_first_name'];
			$last = $_POST['billing_last_name'];

			SendPress_Data::subscribe_user($listid, $email, $first, $last);
		}
	}

	function check_required_classes(){

		$ret = true;
		if( !class_exists('SendPress') ){
			add_action('admin_notices',array( $this, 'notice_sedpress_missing' ));
			$ret = false;
		}
		if( !class_exists('WooCommerce') ){
			add_action('admin_notices',array( $this, 'notice_woo_missing' ));
			$ret = false;
		}

		return $ret;
	}

	function notice_sedpress_missing(){
	    echo '<div class="error">
	       <p><a href="http://sendpress.com" target="_blank">SendPress</a> needs to be installed for WooCommerce SendPress to work.</p>
	    </div>';
	}

	function notice_woo_missing(){
	    echo '<div class="error">
	       <p><a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a> needs to be installed for WooCommerce SendPress to work.</p>
	    </div>';
	}

	/**
     * plugin_activation
     * 
     * @access public
     *
     * @return mixed Value.
     */
	function plugin_activation(){
		
	}

	/**
	*
	*	Nothing going on here yet
	*	@static
	*/
	function plugin_deactivation(){
		
	} 

}
//moving this outside the class, hopefully to fix this issue
//add_action('edd_checkout_before_gateway', array( 'SendPress_EDD_Signup', 'sendpress_edd_check_for_email_signup'), 5, 2);

