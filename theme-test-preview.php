<?php
  /*
   Plugin Name: Theme Test preview
   Plugin URI:  http://none
   Description: Temporarily Preview/Switch your site to different theme (While visitors still see the default theme). After activation, under your settings, click the plugin button!!!!
   Author: Selnomeria
   Version: 1.1
   Author URI: http://none
    */
// FREE LICENCE. Many thanks to "Theme Test Drive" plugin.

	
if (!defined('ABSPATH')) {exit;}


//start redirection
add_action('wp','start_test_detector');
function start_test_detector()
{
	if (isset($_GET['testmode']))	{header("location: ".home_url().'?turnTestOffOn=on');} 
}



if 	(!empty($_GET['turnTestOffOn']))
{
	if ($_GET['turnTestOffOn'] == 'on')
	{
		define('previewMODEE',true);setcookie('prw','yes',time()+1000000,'/');	header("location: ".home_url()) or die(__FILE__);
	}
	elseif($_GET['turnTestOffOn'] == 'off')
	{
		define('previewMODEE',false);setcookie('prw','no',time()+1000000,'/');	header("location: ".home_url()) or die(__FILE__);
	}
	elseif($_GET['turnTestOffOn'] == 'complete_off')
	{
		define('previewMODEE',false);setcookie('prw','no',time()-9999999,'/');	header("location: ".home_url()) or die(__FILE__);
	}
}
elseif	('yes'	== $_COOKIE['prw'] )		{define('previewMODEE',true);	}
elseif	('no'	== $_COOKIE['prw'] )		{define('previewMODEE',false);	}
else										{define('previewMODEE',false);	}

add_action('wp_footer','show_testONOFF');
function show_testONOFF()
{
	//check if prohibited for him
	if (get_option('only_admin_ts_access')!='everyonee')
	{	
		if (!current_user_can( 'edit_posts' ))
		{
		return;
		}
	}
	
		if ( !empty($_COOKIE['prw']) || previewMODEE )
		{
			$slcted	=	previewMODEE ? '' : 'selected';
			echo '<select class="testingCHOOSER" onchange="OnOffTest(this)"
			style="display:block; z-index:9999; position:fixed;top:30px;width:200px; height:40px;left:1px; padding:4px;color:white; background-color: #C00;border: 5px solid green;" >
				<option value="on">PREVIEW IS ON</option>
				<option value="off" '.$slcted.'>PREVIEW OFF</option>
				<option value="complete_off">PREVIEW OFF and remove this menu</option>
			</select>
			<script type="text/javascript">function OnOffTest(elm){window.location="'.home_url().'/?turnTestOffOn=" + elm.value;}</script>';
		}
}



function test_previewerr( $template = '' ) 
{
	$theme_nameee = get_option('th_test_name');
	
	//check if prohibited for him
	if (get_option('only_admin_ts_access')!='everyonee')
	{	
		if (!current_user_can( 'edit_posts' ))
		{
		return $template;
		}
	}

	//check the name directly
	$my_theme = wp_get_theme($theme_nameee);
	if ( $my_theme->exists() ){return $my_theme;	}
	
	//if not got correct name, then try to replace whitespace
	$my_theme = wp_get_theme(str_replace(' ','',$theme_nameee));
	if ( $my_theme->exists() ){	return $my_theme;	}	
	
	//if not got again, then maybe it was stylesheet's nickname
	$my_theme = wp_get_themes();
	foreach ($my_theme as $theme_data) 
	{
	if ($theme_data == $theme_nameee) {	return $theme_data;	}
	}

	//else
	return $template;
}




function CLONE_themedrive_determine_theme2()
{
	//check if prohibited for him
	if (get_option('only_admin_ts_access')!='everyonee')
	{	
		if (!current_user_can( 'edit_posts' ))	{	return false;	}
	}

	$theme = get_option('th_test_name'); if ($theme == '') {  return false;	}
	$theme_data = wp_get_theme($theme);
	if (!empty($theme_data)) {
	  // Don't let people peek at unpublished themes
	  if (isset($theme_data['Status']) && $theme_data['Status'] != 'publish') {
		  return false;
	  }
	  return $theme_data;
	}

	// perhaps they are using the theme directory instead of title
	$themes = wp_get_themes();
	foreach ($themes as $theme_data) {
	  // use Stylesheet as it's unique to the theme - Template could point to another theme's templates
	  if ($theme_data['Stylesheet'] == $theme) {
		  // Don't let people peek at unpublished themes
		  if (isset($theme_data['Status']) && $theme_data['Status'] != 'publish') {
			  return false;
		  }
		  return $theme_data;
	  }
	}

	return false;
}

function themedrive_get_template2($template)
{
	$theme = CLONE_themedrive_determine_theme2();
	if ($theme === false) { return $template; }
	else {return $theme['Template'];}
}

function themedrive_get_stylesheet2($stylesheet)
{
	$theme = CLONE_themedrive_determine_theme2();
	if ($theme === false) { return $stylesheet; }
	else { return $theme['Stylesheet']; }
}

  
  
if (previewMODEE)
{  
	add_filter( 'template', 'themedrive_get_template2' );
	add_filter( 'stylesheet', 'themedrive_get_stylesheet2' ); // only WP smaller 3*
	add_filter( 'option_template', 'test_previewerr' );
	add_filter( 'option_stylesheet', 'test_previewerr' );
}






// ===================Call the ADMIN MENU=====================
add_action('admin_menu', 'prev_menuuu_link');
function prev_menuuu_link() 
{
	add_submenu_page( 'options-general.php', 'Theme TESTER', 'Theme TESTER', 'manage_options', 'theme-test-viewer', 'previewr_func' ); 
}


function previewr_func() 
{
	if (!empty($_POST['td_themes']))
	{
		update_option('th_test_name', $_POST['td_themes']);
		update_option('only_admin_ts_access', $_POST['accessts']);
		echo '<br/><h3 style="color:red;"> Testing Theme is Set </h3><br/>';
	}
	?> 

	<div class="choose_theme"><br/><br/>
	<h3> Keep in mind, if you will have problems, just deactivate/delete this plugin. This plugin is like "Theme Test drive" , "User Theme" and "Theme Switch and Preview"</h3><br/><br/>
	<h2> Choose the desired theme for Test mode</h2><p>(you should have at least 2 themes installed already)</p>
	<form action="" method="POST">
	<?php
	$default_theme = wp_get_theme();
	$themes = wp_get_themes();
	if (count($themes) > 1) 
	{
		$theme_names = array_keys($themes);
		natcasesort($theme_names);
		echo '<select name="td_themes">';
		foreach ($theme_names as $theme_name) 
		{
			// Skip unpublished themes.
			if (isset($themes[$theme_name]['Status']) && $themes[$theme_name]['Status'] != 'publish') {  continue;	}

			if ((get_option('th_test_name') == $theme_name) || ((get_option('th_test_name') == '') && ($theme_name == $default_theme)))			 { $selectdd=' selected="selected"'; } 
			else						{ $selectdd='';}
			
			echo '<option value="' . esc_attr( $theme_name ).'" '. $selectdd .'>'. $themes[$theme_name]['Name'].'</option>'."\n";
		}
		echo '</select>';
	}
	?>
	<p> Only Logged in Administrators can visit Testing Environment ? <input type="hidden" name="accessts" value="everyonee" /> <input type="checkbox" name="accessts" value="adminsss" <?php if (get_option('only_admin_ts_access')!='everyonee') {echo 'checked="checked"';}?> />	</p>  <input type="submit" value="Save">  <p>after saving, just visit <a href="<?php echo home_url();?>/?testmode" target="_blank" style="color:red;font-size:1.2em;">yoursite.com/<b style="font-size:1.2em;">?testmode</b></a> (on the left upper corner you will have previewer ON/OFF)</p>
	</form>
	</div>
<?php
} 
?>
