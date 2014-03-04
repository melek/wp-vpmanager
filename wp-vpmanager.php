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

	//Helper for sc_projectStatus//
    public function displayStartDate () {
    	global $post;
      	$date_str = explode('-', get_post_meta($post->ID, $this->post_type . '_startdate', true));
			return date('l, F j Y', mktime(0,0,0,$date_str[1],$date_str[2],$date_str[0]));
    }
 
    public function sc_joinButton () {
        global $post;		
        $debug = "";
        $message = "";
        $opentags = "<form action='' method='get'><input type='hidden' name='wp-vpm-postref' value='".$post->ID ."'/><table><tr>";
        $cancel = "<td><button type='submit' name='wp-vpm-action' value='cancel'>Leave Project</button></td>";				        
        $waitlist = "<td><button type='submit' name='wp-vpm-action' value='waitlist'>Join the Waitlist</button></td>";       
        $signup = "<td><button type='submit' name='wp-vpm-action' value='signup'>Register for this Project</button></td>";
        $closetags = "</table></form></tr>";
        
        $debug .= "\$waitlist after initialization: " . htmlspecialchars($waitlist) . "<br>";
//->Is user logged in?		
//	->NO: Login to sign up for this project! BREAK
//  ->

		if(!is_user_logged_in()) {
			$message = "Login or register to signup for this project!";
			//Return immediately if not logged in.
			return $message;
		}		
        else $user = get_current_user_id();
        

//Action logic.
//->Did the user submit a form for THIS post?
        if($_GET['wp-vpm-postref'] == $post->ID) {
            
//  ->Did the user hit the signup button?
//      ->YES: Add the user to the project.
            if($_GET['wp-vpm-action'] == 'signup') {
                $debug = "Signup action detected. <br/>";
                add_post_meta($post->ID, $this->post_type . '_signedup', $user);
            }
        
//  ->Did the user click the waitlist button?
//      ->YES: Add the user to the waitlist.
            if($_GET['wp-vpm-action'] == 'waitlist') {
                $debug = "Waitlist action detected. <br/>";
                add_post_meta($post->ID, $this->post_type . '_waitlist', $user);
            }
//  ->Did the user click the cancel button?
//      ->YES: Remove the user from the roster and waitlist.
            if($_GET['wp-vpm-action'] == 'cancel') {
                $debug = "Cancel action detected. <br/>";
                $message .= "<strong>You've unregistered for this project.</strong><br />";
                delete_post_meta($post->ID, $this->post_type . '_waitlist', $user);
                delete_post_meta($post->ID, $this->post_type . '_signedup', $user);
            }
        } else $debug .= "No action detected.<br/>";        
        
        $debug .= "\$waitlist after action logic: " . htmlspecialchars($waitlist) . "<br>";
//Preprocessing for form logic.        
		//Get the signup list. Is the user signed up?
		$roster = get_post_meta($post->ID, $this->post_type . '_signedup', false);
    $admin = "<table><tr><td colspan='2'>Registered Volunteers</td></tr>";
		foreach ($roster as $volunteer_on_list) {
      $registeredUser = get_userdata($volunteer_on_list);
		 	$admin .= "<tr><td>" . $registeredUser->display_name . "</td><td>" . $registeredUser->user_email . "</td></tr>";
			if($volunteer_on_list == $user) {			    
				$user_signedup = true;	        
			}
		}
    $admin .= "<tr><td colspan='2'>Waitlisted Volunteers</td></tr>";
        //Get the waitlist. Is the user on that?
        $waitlist_roster = get_post_meta($post->ID, $this->post_type . '_waitlist', false);
				
		foreach ($waitlist_roster as $volunteer_on_list) {
            $waitlistedUser = get_userdata($volunteer_on_list);
		 	$admin .= "<tr><td>" . $waitlistedUser->display_name . "</td><td>" . $waitlistedUser->user_email . "</td></tr>";
			if($volunteer_on_list == $user) {
				$user_waitlisted = true;
				
			}
		}	
      $admin .= "</table>";
		//Are there spots left?
		$spots = get_post_meta($post->ID, $this->post_type . '_max-project-size', true);
    $debug .= 'sizeof($roster) = '.sizeof($roster).'; $spots = '.$spots.'<br/>';
		if (sizeof($roster) < $spots) {      	
		    $spots_available = true;
		}
		
		//Some bookkeeping; If the user is on BOTH the signup and the waitlist, remove them from the waitlist.
		if($user_signedup && $user_waitlisted){
		    delete_post_meta($post->ID, $this->post_type . '_waitlist', $user);
		    unset($user_waitlisted);
		    $message .= "<em>Oops! We found you are both registered and on the waitlist! You've been removed from the waitlist.</em><br />";
		}
	
	$debug .= "\$waitlist after pre-form logic: " . htmlspecialchars($waitlist) . "<br>";
//Form logic
//->Is user signed up?
//	->YES: Offer Cancellation (Remove Signup & Waitlist Button)
        if ($user_signedup) {
            $message .= "<strong>You are signed up for this project!</strong><br />";
            $signup = '';
            $waitlist = '';
        }
        
//->Is user waitlisted?
//	->YES: Offer Cancellation (Remove Waitlist Button)
        else if ($user_waitlisted) {
            $message .= 'You are on the waitlist for this project, we will contact you if spots become available.<br />';
            $waitlist = '';
        }
        
//->Is there space for the user?
//	->YES: Offer signup (Remove waitlist)
        if($spots_available) {
            $waitlist = '';
            $message .= "This project has space available, come join us!<br />";
        } 
//	->NO: Offer waitlist (Remove signup)
        else {
            $signup = '';
            $message .= "This project is currently full.<br />";
        }
        
        //Remove the cancel button if the user is neither signed up or waitlisted.
        if(!$user_signedup && !$user_waitlisted) $cancel = '';
     

       $debug .= '$message: ' . htmlspecialchars($message) 
         . '<br> $opentags: ' . htmlspecialchars($opentags)
         . '<br> $signup: ' . htmlspecialchars($signup)
         . '<br> $waitlist: ' . htmlspecialchars($waitlist)
         . '<br> $cancel: ' . htmlspecialchars($cancel)
      	 . '<br> $closetags: ' . htmlspecialchars($closetags);

      $debug .= "<br>Concatenation test: " . htmlspecialchars($signup.$waitlist.$cancel) . " [Should be blank]";
      
      //Option to add debug information.     
      //$message .= "<span style='background-color:silver;color:red;'>".$debug."</span>";   

      //Option to show administrative information after the project table.
      //If administrator, show a list of registered users.
      $closetags .= $admin;
      
      //Finally, return the output. 
      return  $message.$opentags.$signup.$waitlist.$cancel.$closetags;


     	
	}
  
}

$WP_VPManager = new WP_VPManager();