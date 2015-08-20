<?php
/**
 * Checks Envato WordPress plugins' updates and download its if any update available
 *
 * @author Eray Alakese <erayalakese@gmail.com>
 * @version 1.0.1
 * @license GPL v2
 */
require_once('vendor/autoload.php');
class Envato_Update_Checker
{

	private $plugin_name;
	private $plugin_slug;
	private $purchase_code;
	private $envato_update_checker_json;

	/**
	 * Workflow
	 *
	 * 1) Check if user registered any purchase code for {plugin slug}
	 * 2) If yes, check envato-update-checker.json file, else go to step 4
	 * 3) If there is a new version, show admin_notices, else do nothing
	 * 4) Ask user to save purchase code.
	 */

	function __construct($plugin_name, $plugin_slug, $envato_update_checker_json, $personal_token)
	{
		$this->plugin_name = $plugin_name;
		$this->plugin_slug = $plugin_slug;
		$this->envato_update_checker_json = $envato_update_checker_json;
		$this->personal_token = $personal_token;

		add_action('admin_init', array($this, 'http_requests'));
		add_action('admin_init', array($this, 'init'));
	}

	function init()
	{
		$this->purchase_code = get_option('euc_'+$this->plugin_slug+'_pc');
		if($this->purchase_code === FALSE)
		{
			add_action('admin_notices', array($this, 'ask_for_pc'));
		}
		else
		{
			add_action('admin_notices', array($this, 'update_check'));
		}
	}

	function ask_for_pc()
	{
		?>
		<div class="error"><p>
			<strong>You need to type your purchase code to get notifications about updates.</strong>
			<form action="" method="GET"><input type="text" name="euc_input_pc"><input type="submit"></form>
		</p></div>
		<?php
	}

	function http_requests()
	{
		if(isset($_GET["euc_input_pc"]))
		{
			update_option('euc_'+$this->plugin_slug+'_pc', $_GET["euc_input_pc"]);
			wp_safe_redirect(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'wp-admin/index.php');
			exit;
		}
		elseif(isset($_GET["euc_action"]) && $_GET["euc_action"] == 'download')
		{
			$api->download_item($this->purchase_code);
		}
	}

	function update_check()
	{
	    $plugin_data = get_plugin_data( __FILE__ );
		$version = $plugin_data['Version'];
	    $url = $this->envato_update_checker_json;
	    if(function_exists('curl_version')) :
	        $CURL = curl_init();
	        curl_setopt($CURL, CURLOPT_URL, $url);
	        curl_setopt($CURL, CURLOPT_HEADER, 0);
	        curl_setopt($CURL, CURLOPT_RETURNTRANSFER, 1);
	        $data = curl_exec($CURL);
	        curl_close($CURL);
	        $c = $data;
	    else :
	        $c = file_get_contents($url);
	    endif;
	    $json = json_decode($c);
	    $new_version = str_replace('.', '' , $json->{$this->plugin_slug});
	    $recent_version = str_replace('.', '' , $version);
	    if($new_version > $recent_version) :
	        ?>
	        <div class="update-nag">
	            New <strong><?=$this->plugin_name?> plugin</strong> update available. <a href="?euc_action=download">Click here</a> to download newest version of the plugin.
	        </div>
	    <?php
	    endif;
	}
}
//new Envato_Update_Checker("Plugin Name", "vcb", "http://erayalakese.com/envato-update-checker.json", "GTTTePxFvxlTacMrB5I3qqPtCd4D0Po4");