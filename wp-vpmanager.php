<?php
/*
    Plugin Name: WP-Volunteer Project Manager
    Plugin URI: http://EXAMPLE.com/
    Description: Create and manage volunteer projects for your organization.
    Author: <a href="http://Cyberbusking.org/">Meitar "maymay" Moscovitz</a> and Lionel Di Giacomo
    Version: 0.01
    Text Domain: wp-vpmanager
    Domain Path: /languages
*/

class WP_VPManager {
    private $post_type = 'wp-vpm-project';
  
    public function __construct () {
		add_shortcode('wp_vpm_datepicker', array($this, 'sc_datePicker'));  
	  add_shortcode('wp_vpm_status', array($this, 'sc_projectStatus'));
  	add_shortcode('wp_vpm_joinbutton', array($this, 'sc_joinButton'));
    add_action('init', array($this, 'registerCustomPostType'), 10);
    add_action('init', array($this, 'registerTaxonomy'), 20);
    add_action('init', array($this, 'registerDateScript'), 30);
		add_action('add_meta_boxes_' . $this->post_type, array($this, 'addMetaBoxes'));
		add_action('save_post', array($this, 'savePost'));
        
    }
    
    public function registerCustomPostType () {
        $labels = array(
            'name'               => __('Projects', 'wp-vpmanager'),
            'singular_name'      => __('Project', 'wp-vpmanager'),
            'add_new'            => __('Add New Project', 'wp-vpmanager'),
            'add_new_item'       => __('Add New Project', 'wp-vpmanager'),
            'edit'               => __('Edit Project', 'wp-vpmanager'),
            'edit_item'          => __('Edit Project', 'wp-vpmanager'),
            'new_item'           => __('NewProject', 'wp-vpmanager'),
            'view'               => __('View Project', 'wp-vpmanager'),
            'view_item'          => __('View Project', 'wp-vpmanager'),
            'search'             => __('Search Projects', 'wp-vpmanager'),
            'not_found'          => __('No Projects found', 'wp-vpmanager'),
            'not_found_in_trash' => __('No Projects found in trash', 'wp-vpmanager')
        );
        
        $url_rewrites = array(
            'slug' => 'projects'
        );
        
        $args = array(
            'labels' => $labels,
            'description' => __('Volunteer Projects', 'wp-vpmanager'),
            'public' => true,
//            'menu_icon' => plugins_url(basename(__DIR__) . '/images/seedexchange_icon.png'),
            'has_archive' => true,
            'supports' => array(
                'title',
                'editor',
                'author',
                'comments'
            ),
            'rewrite' => $url_rewrites
        );

        register_post_type($this->post_type, $args);
    }

    public function registerTaxonomy () {
        $taxonomylabels = array(
            'name'              => _x( 'Scope', 'taxonomy general name' ),
            'singular_name'     => _x( 'Scope', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Scopes' ),
            'all_items'         => __( 'All Scopes' ),
            'edit_item'         => __( 'Edit Scope' ),
            'update_item'       => __( 'Update Scope' ),
            'add_new_item'      => __( 'Add New Scope' ),
            'new_item_name'     => __( 'New Scope' ),
            'menu_name'         => __( 'Scope' )
        );
        
        $url_rewrites = array(
            'slug' => 'scope'
        );
        
        $args = array(
            'labels' => $taxonomylabels,
            'rewrite' => $url_rewrites
        );
        
        register_taxonomy('scope', $this->post_type, $args);       
    }

		public function registerDateScript () {
	      wp_register_script('vpm-scripts', plugins_url( '/wp-vpm-scripts.js', __FILE__ ) );
     		wp_enqueue_script('jquery-ui-datepicker'); 
	      wp_enqueue_script('vpm-scripts');
		    wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    }

    public function addMetaBoxes () {
        add_meta_box(
            $this->post_type . '-projectdetailsbox',
            __('Project Details', 'wp-vpmmanager'),
            array($this, 'renderProjectDetailsBox'),
            $this->post_type
        );
    }

    public function renderProjectDetailsBox () {
			echo $this->sc_projectStatus();
			?>
				<hr>
				<table width="100%" align="center"><tr><td>
				<h4>Date of Project:</h4>
  				<?php $this->sc_datePicker();?></td><td>
				<h4>Scope:</h4>
				<select name="wp-vpm-project_scope">
  				<option value="">[Choose an option]</option>
  				<option value="morning">Morning</option>
  				<option value="afternoon">Afternoon</option>
  				<option value="allday">All Day</option>
  				<option value="multiday">Multi Day</option>
  				<option value="multidaycamping">Multi Day - Camping</option>
				</select></td><td>
				
  				<h4>Difficulty:</h4>
				<select name="wp-vpm-project_difficulty">
  				<option value="">[Choose an option]</option>
  				<option value="easy">Easy</option>
  				<option value="intermediate">Intermediate</option>
  				<option value="difficult">Difficult</option>
				</select></td><td>
				
				<h4>Maximum Volunteer Spots:</h4>
				Put a number in <input type="text" size=4 name="wp-vpm-project_max-project-size"></td></tr></table>
			<?php
    }

    public function savePost ($post_id) {
      	if($_POST['wp-vpm-project_startdate'])
					update_post_meta($post_id, 'wp-vpm-project_startdate', $_POST['wp-vpm-project_startdate']);
        if($_POST['wp-vpm-project_difficulty'])
					update_post_meta($post_id, 'wp-vpm-project_difficulty', $_POST['wp-vpm-project_difficulty']);
        if($_POST['wp-vpm-project_scope'])
					update_post_meta($post_id, 'wp-vpm-project_scope', $_POST['wp-vpm-project_scope']);
        if($_POST['wp-vpm-project_max-project-size'])
					update_post_meta($post_id, 'wp-vpm-project_max-project-size', sanitize_text_field($_POST['wp-vpm-project_max-project-size']));
    }

    public function sc_datePicker () {
      //HTML for datepicker here.
	    return "<input type='text' class='custom_date' name='wp-vpm-project_startdate' value=''/>";
    }
 
    public function sc_projectStatus () {
        global $post;
	 			$output = "<em><strong>" . $this->displayStartDate() . "</strong>"
      	 ."&nbsp;&nbsp;&nbsp;Difficulty: " . get_post_meta($post->ID, $this->post_type . '_difficulty', true)
      	 . "&nbsp;&nbsp;&nbsp;Scope: " . get_post_meta($post->ID, $this->post_type . '_scope', true)
      	 . "&nbsp;&nbsp;&nbsp;Volunteer Spots: " . get_post_meta($post->ID, $this->post_type . '_max-project-size', true) 
         . "</em>";
      	return $output;
    }

		//Helper for sc_projectStatus
    public function displayStartDate () {
    	global $post;
      	$date_str = explode('-', get_post_meta($post->ID, $this->post_type . '_startdate', true));
			return date('l, F j Y', mktime(0,0,0,$date_str[1],$date_str[2],$date_str[0]));
    }
 
  public function sc_joinButton () {
			//HTML for a join button
			if(!is_user_logged_in()) {
				return "Login to join this project!";				
			}
			
			if(!isset($_POST['wp-vpm-signup-action']))
				return "Signup: <form action='' method='post'><input type='submit' name='wp-vpm-signup-action' value='true' /></form>";
			
			return $this->signupUser(); 
    }

  public function signupUser () {

		//Was the button pushed?
		if(!isset($_POST['wp-vpm-signup-action'])){
			return 0;
			}
		
		//Check if the user is actually logged in	
		global $post;
		if (!is_user_logged_in()) {
			return "No user logged in to sign up! How did you do that?";
			}
	
		//Get the list of current volunteers for the project and max volunteer spaces.
		$roster = get_post_meta($post->ID, $this->post_type . '_signedup', false);

		//Is the current user already on the list?
		$user = get_current_user_id();  	
		foreach ($roster as $volunteer_on_list) {
			if($volunteer_on_list == $user) {
				return "You are already signed up for this project!";				
				}
		}

		//Well, is the user on the waitlist?
		$waitlist = get_post_meta($post->ID, $this->post_type . '_waitlist', false);
		
		$user = get_current_user_id();  
		foreach ($waitlist as $volunteer_on_list) {
			if($volunteer_on_list == $user) {
				$user_is_on_waitlist = true;
				break;
				}
		}

		//Are there spots left?
		$spots = get_post_meta($post->ID, $this->post_type . '_max-project-size', true);
		if (sizeof($signed_up) >= $spots) {
			if ($user_is_on_waitlist == true) {
					return "The project is still full. You are still on the waitlist, we'll contact you if someone drops out!";
				}
				update_post_meta($post->ID, 'wp-vpm-project_waitlist', $user);
				return "The project is full, but you've been added to the waitlist and we'll be in touch if someone cancels!";
		}

		//Is the current user on the waitlist, but there is space available?
		if($user_is_on_waitlist == true && sizeof($signed_up) < $spots) {			
			delete_post_meta($post->ID, 'wp-vpm-project_waitlist', $user);
			update_post_meta($post->ID, 'wp-vpm-project_signedup', $user);
			return "We've moved you from the waitlist to the roster - Congratulations! Look for a project email within a week or so of the project date, and contact us with any questions you may have.";
		}
		
		//Finally, add the user to the list. They made it!
		if(sizeof($signed_up) < $spots) {
			update_post_meta($post->ID, 'wp-vpm-project_signedup', $user);
			return "Thank you! Space is limited so please let us no if you can no longer make it. Expect a logistical email about a week prior the project. We look forward to seeing you!";
		}
		
		//Nobody should get here.
		return "What did you do?";
	}
  
}

$WP_VPManager = new WP_VPManager();