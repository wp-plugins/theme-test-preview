<?php
/*
Plugin Name: Theme Test preview
Description: Temporarily Preview/Switch your site to different theme (While visitors still see the default theme). After activation, click SETTINGS>Test themes!!! (OTHER MUST-HAVE PLUGINS : http://codesphpjs.blogspot.com/2014/10/must-have-wordpress-plugins.html ) IF PROBLEMS, just REMOVE the plugin.
contributors: selnomeria
Version: 1.2
*/ //FREE LICENCE. Many thanks to "Theme Test Drive" plugin.


define('TTPRW_ACTIVE_THEMFOLD',esc_attr(wp_get_theme()->template));	//get_stylesheet()
define('TTPRW_ACTIVE_THEMNAME',esc_attr(wp_get_theme()->name));		//wp_get_theme()->template



register_deactivation_hook(__FILE__,'TTPRW_activatt');	function TTPRW_activatt()	{}
register_activation_hook(__FILE__,  'TTPRW_deactivatt');function TTPRW_deactivatt()	{setcookie('tPREW_t','delet',time()-9999999,'/');}
	
	
	function TTPRW_detectionn(){
		if (substr($_SERVER['REQUEST_URI'],-9)=='/testmode')	{header("location: ".home_url().'/?turnTestOffOn='.TTPRW_ACTIVE_THEMFOLD) or die(__FILE__);} 
	} TTPRW_detectionn(); //add_action('plugins_loaded','TTPRW_detectionn');     <-- this doesnt work.. i dont know why..

	function TTPRW_permisionn(){
		if (get_option('TTPRW_environment_access')=='adminsss' && !current_user_can( 'edit_posts' )){return false;}
		return true;
	}
	

add_action('plugins_loaded','TTPRW_change_func');function TTPRW_change_func(){
	if (TTPRW_permisionn()){
		//DETECT situation
		if 	(isset($_GET['turnTestOffOn'])) {				//change was detected
			$GLOBALS['previewMODEE']=$_GET['turnTestOffOn'];
			setcookie('tPREW_t',$_GET['turnTestOffOn'],	time()+9999999,'/');
			header("location: ".home_url()) or die(__FILE__);
		}
		elseif (isset($_COOKIE['tPREW_t'])){ 		//change was NOT detected
			$GLOBALS['previewMODEE']=$_COOKIE['tPREW_t'];
		}
		else {
			setcookie('tPREW_t','delet',	time()-9999999,'/');
		}
	}

	//if enabled, then...
	if (!empty($GLOBALS['previewMODEE'])){  
 		add_filter('template', 'themedrive_get_template3');
 		add_filter('stylesheet', 'themedrive_get_stylesheet3');
		//my addition:
		add_filter( 'option_template', 'themedrive_get_template3' );
		add_filter( 'option_stylesheet', 'themedrive_get_stylesheet3' );
	}
}
 
function themedrive_get_template3($template)	{$theme=TTPRW_determine(); if($theme === false) {return $template;}		return $theme['Template'];}
function themedrive_get_stylesheet3($stylesheet){$theme=TTPRW_determine(); if($theme === false) {return $stylesheet;}	return $theme['Stylesheet'];}

function TTPRW_determine(){
  $theme = $GLOBALS['previewMODEE'];	if (empty($theme) || (!file_exists(get_theme_root().'/'.$theme))  ) {return false;}
  //if chosen theme name
  $theme_data = wp_get_theme($theme);	if (!empty($theme_data)) { return ('publish' == $theme_data['Status']) ? $theme_data : false; }
  //if chosen theme title
  $themes = wp_get_themes();
			foreach ($themes as $theme_data) {
				if ($theme == $theme_data['Stylesheet'] ) {	  //Stylesheet is unique to the theme (Template may point to other theme's template)
					if ('publish' == $theme_data['Status']) {return $theme_data;} else { return false;}	  
				}
			}
  return false;
}











// ==========================================        Call the ADMIN MENU        ================================
add_action('admin_menu', 'prev_menuuu_link');function prev_menuuu_link() {add_submenu_page( 'options-general.php', 'Theme Test Preview', 'Theme Test Preview', 'manage_options', 'theme-test-preview', 'previewr_func' ); }function previewr_func(){
	if (!empty($_POST['accessts']))	{
		update_option('TTPRW_environment_access', $_POST['accessts']);
		echo '<br/><h3 style="color:red;"> Testing Theme is Set </h3><br/>';
	}
	?> 

	<div class="choose_theme"><br/><br/>
	<b>Keep in mind, if you will have problems, just deactivate/delete this plugin. (There exist other alike plugins "Theme Test drive" , "User Theme", "Theme Switch and Preview" , "page theme" and etc..)</b><br/><br/><br/>
	<form action="" method="POST">
	<p> Only Logged in Administrators can see Testing Environment ? <input type="hidden" name="accessts" value="everyonee" /> <input type="checkbox" name="accessts" value="adminsss" <?php if (get_option('TTPRW_environment_access')=='adminsss') {echo 'checked="checked"';}?> />	</p>  <input type="submit" value="Save">  <p>after saving, just visit <a href="<?php echo home_url();?>/testmode" target="_blank" style="color:red;font-size:1.2em;">yoursite.com/<b style="font-size:1.2em;">testmode</b></a> (and on the left upper corner you will see a menu)</p>
	</form>
	</div>
<?php
} 

add_action('wp_footer','TTPRW_show_testONOFF');function TTPRW_show_testONOFF(){
	if (!empty($GLOBALS['previewMODEE'])) {
		echo '<select class="testingCHOOSER" onchange="OnOffTTPRW(this)"
		style="display:block; z-index:9999; position:fixed;top:30px;width:200px; height:40px;left:1px; padding:4px;color:white; background-color: #C00;border: 5px solid green;" >';
		
				$themes = wp_get_themes();
				if (count($themes) > 1) {
					$theme_names = array_keys($themes);
					natcasesort($theme_names);
					foreach ($theme_names as $theme_name){			
						$tName1=$themes[$theme_name]['Template'];	
						$tName2=$themes[$theme_name]['Name'];
						$tName3=$theme_name;
						if ('publish' == $themes[$theme_name]['Status'] ) {
							$selectdd =  ($GLOBALS['previewMODEE'] == $tName1)	? ' selected="selected"'	:	'';
							echo '<option value="' . esc_attr( $tName3 ).'" '. $selectdd .'>'. $tName2.'</option>'."\n";
						}
					}
				}else {echo '<option value="x">NO ADDITIONAL THEMES INSTALLED</option>';}
				echo '<option value="">--EXIT PREVIEW MODE--</option>';
				
		echo '</select> 	<script type="text/javascript">function OnOffTTPRW(elm){window.location="'.home_url().'/?turnTestOffOn=" + elm.value;}</script>';
	}
}


add_action( 'activated_plugin', 'TTPRW_activation_redirect' ); function TTPRW_activation_redirect( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) { exit( wp_redirect( admin_url( 'options-general.php?page=theme-test-preview' ) ) ); }
	update_option('TTPRW_environment_access','adminsss');
}
?>
