<?php
/*
Plugin Name: Theme Test preview
Description: Temporarily Preview/Switch your site to different theme (While visitors still see the default theme). After activation, click SETTINGS>Test themes!!! (OTHER MUST-HAVE PLUGINS : http://codesphpjs.blogspot.com/2014/10/must-have-wordpress-plugins.html ) IF PROBLEMS, just REMOVE the plugin.
contributors: selnomeria
Version: 1.2
*/ // FREE LICENCE. Many thanks to "Theme Test Drive" plugin.

define('TTPRW_ACTIVE_THEMFOLD',esc_attr(wp_get_theme()->template));     //get_stylesheet()
define('TTPRW_ACTIVE_THEMNAME',esc_attr(wp_get_theme()->name));  //wp_get_theme()->template


	function TTPRW_detection(){
		if (substr($_SERVER['REQUEST_URI'],-9)=='/testmode')	{header("location: ".home_url().'/?turnTestOffOn='.TTPRW_ACTIVE_THEMFOLD) or die(__FILE__);} 
	} TTPRW_detection(); //add_action('plugins_loaded','TTPRW_detection');     <-- this doesnt work.. i dont know why..

	function TTPRW_permisionn(){
		if (get_option('TTPRW_environment_access')!='everyonee')	{	if (!current_user_can( 'edit_posts' ))	{return false;}  return true;}
	}
	
add_action('plugins_loaded','TTPRW_change_func');function TTPRW_change_func(){
	if (TTPRW_permisionn()){
		//if change detected
		if 	($t_request = $_GET['turnTestOffOn']){
			if($t_request == 'complete_off')	{setcookie('tPREW','canceled',	time()+9999999,'/');  $GLOBALS['previewMODEE']=false;		}
			else								{setcookie('tPREW',$t_request,	time()+9999999,'/');  $GLOBALS['previewMODEE']=$t_request;	}
			header("location: ".home_url()) or die(__FILE__);
		}
		//if change NOT detected
		elseif ($cookieee_set = $_COOKIE['tPREW']){
			if ($cookieee_set == 'canceled') {$GLOBALS['previewMODEE']=false;}
			else							 {$GLOBALS['previewMODEE']=$_COOKIE['tPREW'];}
		}
	}
}
add_action('plugins_loaded','TTD_filters3');  function TTD_filters3 () {
	if ($GLOBALS['previewMODEE']){  
 		add_filter('template', 'themedrive_get_template3');
 		add_filter('stylesheet', 'themedrive_get_stylesheet3');
		add_filter( 'option_template', 'themedrive_determine_theme3' );
		add_filter( 'option_stylesheet', 'themedrive_determine_theme3' );
	}
}
 
 




function themedrive_determine_theme3()
{
  $theme_name=$GLOBALS['previewMODEE'];
  if (isset($theme_name)) { $theme = $theme_name; }
  
  $theme_data = wp_get_theme($theme);
  if (!empty($theme_data)) {
	  // Don't let people peek at unpublished themes
	  if (isset($theme_data['Status']) && $theme_data['Status'] != 'publish') { return false; }
	  return $theme_data;
  }
  
  // perhaps they are using the theme directory instead of title
  $themes = wp_get_themes();
  
  foreach ($themes as $theme_data) {
	  // use Stylesheet as it's unique to the theme - Template could point to another theme's templates
	  if ($theme_data['Stylesheet'] == $theme) {
		  // Don't let people peek at unpublished themes
		  if (isset($theme_data['Status']) && $theme_data['Status'] != 'publish') {   return false;    }
		  return $theme_data;
	  }
  }
  return false;
}

function themedrive_get_template3($template)  {
  $theme = themedrive_determine_theme3();
  if ($theme === false) {return $template;}
  return $theme['Template'];
}

function themedrive_get_stylesheet3($stylesheet)  {
  $theme = themedrive_determine_theme3();
  if ($theme === false) { return $stylesheet;  }
  return $theme['Stylesheet'];
}

  








add_action('wp_footer','TTPRW_show_testONOFF');function TTPRW_show_testONOFF(){
	if ($GLOBALS['previewMODEE']!=false) {
		echo '<select class="testingCHOOSER" onchange="OnOffTest(this)"
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
				echo '<option value="complete_off">--EXIT PREVIEW MODE--</option>';
				
		echo '</select>
		<script type="text/javascript">function OnOffTest(elm){window.location="'.home_url().'/?turnTestOffOn=" + elm.value;}</script>';
	}
}





// ===================Call the ADMIN MENU=====================
add_action('admin_menu', 'prev_menuuu_link');function prev_menuuu_link() {add_submenu_page( 'options-general.php', 'Theme Test Preview', 'Theme Test Preview', 'manage_options', 'theme-test-preview', 'previewr_func' ); }function previewr_func(){
	if (!empty($_POST['accessts']))	{
		update_option('TTPRW_environment_access', $_POST['accessts']);
		echo '<br/><h3 style="color:red;">Settings Saved!</h3><br/>';
	}
	?> 

	<div class="choose_theme"><br/><br/>
	<b>Keep in mind, if you will have problems, just deactivate/delete this plugin. (There exist other relative plugins "Theme Test drive" , "User Theme", "Theme Switch and Preview" , "page theme" and etc..)</b><br/><br/><br/>
	<form action="" method="POST">
	<p> Only Logged in Administrators can see Testing Environment ? <input type="hidden" name="accessts" value="everyonee" /> <input type="checkbox" name="accessts" value="adminsss" <?php if (get_option('TTPRW_environment_access')!='everyonee') {echo 'checked="checked"';}?> />	</p>  <input type="submit" value="Save">  <p>after saving, just visit <a href="<?php echo home_url();?>/testmode" target="_blank" style="color:red;font-size:1.2em;">yoursite.com/<b style="font-size:1.2em;">testmode</b></a> (and on the left upper corner you will see a menu)</p>
	</form>
	</div>
<?php
} 


add_action( 'activated_plugin', 'TTPRW_activation_redirect' ); function TTPRW_activation_redirect( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) { exit( wp_redirect( admin_url( 'options-general.php?page=theme-test-preview' ) ) ); }
}
?>
