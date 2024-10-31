<?php
/*
Plugin Name: Referall 123
Plugin URI: https://wordpress.org/plugins/referall-123/
Description: Plugin for create Referall programm on your wordpress site
Version: 1.4
Author: Chugaev Aleksandr Aleksandrovich
Author URI: https://profiles.wordpress.org/aleksandrposs/
*/

function referal123_request_123(){
if(session_id() == ''){
    session_start();
}
     if (isset($_REQUEST['ref_id'])) {
        global $_SESSION;
      
        $_SESSION['reg_ref_id'] = sanitize_text_field($_REQUEST['ref_id']);
     }
      if (isset($_REQUEST['ref'])) {
        $str = "Location:/wp-login.php?action=register&ref_id=" . sanitize_text_field($_REQUEST['ref']) . "";
        header($str);
        die();
     }
}
add_action('init', 'referal123_request_123');


function referal123_insert() {
 if(session_id() == ''){
    session_start();
}
 if ($_REQUEST["checkemail"] == "registered") {
     global $_SESSION;
     global $wpdb;
     $res = $wpdb->get_results("SELECT MAX(ID) FROM $wpdb->users");
     $res = (array) $res[0];
     $userIdForRef = (int) $res["MAX(ID)"];
     add_user_meta( $userIdForRef, 'referall_login', sanitize_text_field($_SESSION['reg_ref_id']) );
 }
}
add_filter('login_message', 'referal123_insert');
     
function referal123_registration_form() {
   if(session_id() == ''){
    session_start();
}
	?>
	<p>
		<label for="first_name">
			<?php esc_html_e( 'Referall Login', 'referall_login' ) ?> <br/>
			<input type="text" class="regular_text" name="referall_login" value="<?php
   global $_SESSION;
   echo sanitize_text_field($_SESSION['reg_ref_id']);
   ?>"/>
		</label>
	</p>
	<?php
}
add_action( 'register_form', 'referal123_registration_form' );

function referall123_my_referall_link() {
    add_dashboard_page( __( 'My referall link', 'referall123' ), __( 'My referall link', 'referall123' ), 'read', 'wpdocs-unique-identifier', 'referall123_wpdocs_plugin_function' );
}
add_action('admin_menu', 'referall123_my_referall_link');

function referall123_wpdocs_plugin_function(){
      echo "<center>";
      echo "Your referall link is:<br>";
      echo home_url(). "/?ref=" . get_current_user_id() . "";
      echo "</center>";
}

function referal123_my_referall_link_2() {
    add_dashboard_page( __( 'List referalls', 'referall123' ), __( 'List referalls', 'referall123' ), 'read', 'List-referalls', 'referal123_wpdocs_plugin_function_2' );
}
add_action('admin_menu', 'referal123_my_referall_link_2');

function referal123_wpdocs_plugin_function_2(){
 ?>
 <style>
  #ref_left{width:200px;height:20px;float:left;}
  #ref_center{width:200px;height:20px;float:left;}
  #ref_right{clear:left;}
 </style>
 <center>
   <h2>List referalls</h2>
   </center>
   <?php
   global $wpdb;
   $db_ref_min = (int) $_REQUEST['db_ref_min'];
   $db_ref_max = (int) $_REQUEST['db_ref_max'];
  
   if ( $db_ref_max == 0) {
         $db_ref_max = 5;
   }
   $res_count = $wpdb->get_results(
              "
              SELECT COUNT(*) FROM $wpdb->users,$wpdb->usermeta
              WHERE $wpdb->users.ID=$wpdb->usermeta.user_id
              AND $wpdb->usermeta.meta_key='referall_login'
              ;
              "
         );
    $res_count = (array) $res_count[0];
    $res_count = (int) $res_count["COUNT(*)"];
  
    if (isset( $db_ref_min) && isset( $db_ref_max)) {
          $res = $wpdb->get_results(
              "
              SELECT * FROM $wpdb->users,$wpdb->usermeta
              WHERE $wpdb->users.ID=$wpdb->usermeta.user_id
              AND $wpdb->usermeta.meta_key='referall_login'
               AND $wpdb->usermeta.meta_value!=''
              LIMIT " . $db_ref_min . "," . $db_ref_max . "
              ;
              "
         );  
    } else {
      $res = $wpdb->get_results(
              "
              SELECT * FROM $wpdb->users,$wpdb->usermeta
              WHERE $wpdb->users.ID=$wpdb->usermeta.user_id
              AND $wpdb->usermeta.meta_key='referall_login'
              LIMIT 0,10
              ;
              "
         );  
    }

   ?>
      <div id="ref_left">User</div><div id="ref_center">Referall</div><div style="clear:left;"></div>
      <?php
      $i = 0;
      $i_step = 0;
     foreach($res as $r){
      $r = (array) $r;     
       ?>
       <div id="ref_left"><?php echo $r["user_login"]; ?></div>
       <div id="ref_center"><?php echo $r["meta_value"]; ?></div>
       <div id="ref_right"></div>
       <?php
       $i++;
     }
     // back
     if ($i < $res_count) { $db_ref_min1 = $db_ref_min - 5; $db_ref_max1 = $db_ref_max - 5;}
   
     if ($db_ref_min1 < 0) {
     } else {
        echo '<a href="/wp-admin/index.php?page=List-referalls&db_ref_min=' .  $db_ref_min1 . '&db_ref_max=' .  $db_ref_max1 . '">Back</a>';
     }  
     // next
     if ($i < $res_count) { $db_ref_min2 = $db_ref_min + 5; $db_ref_max2 = $db_ref_max + 5; }
   
     if ($db_ref_min2 >= $res_count) {
     } else {
          echo '<a href="/wp-admin/index.php?page=List-referalls&db_ref_min=' .  $db_ref_min2 . '&db_ref_max=' .  $db_ref_max2 . '">Next</a>';
     }            
}


?>
