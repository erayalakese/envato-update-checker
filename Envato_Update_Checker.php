<?php
/**
 * Checks Envato WordPress plugins' updates and download its if any update available
 *
 * @author Eray Alakese <erayalakese@gmail.com>
 * @version 1.3.0
 * @license GPL v2
 */
namespace erayalakese;

require_once(__DIR__.'/vendor/autoload.php');
class Envato_Update_Checker
{

	private $plugin_name;
	private $plugin_slug;
	private $recent_version;
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

	function __construct($plugin_name, $plugin_slug, $recent_version, $envato_update_checker_json, $personal_token)
	{
		$this->plugin_name = $plugin_name;
		$this->plugin_slug = $plugin_slug;
		$this->recent_version = $recent_version;
		$this->envato_update_checker_json = $envato_update_checker_json;
		$this->personal_token = $personal_token;

		$this->api = new \erayalakese\Envato_Market_API($this->personal_token);

		add_action('admin_init', array($this, 'init'));
		add_action('admin_init', array($this, 'http_requests'));
	}

	function init()
	{
		$this->purchase_code = get_option('euc_'.$this->plugin_slug.'_pc');
		if($this->purchase_code === FALSE)
		{
			add_action('admin_notices', array($this, 'ask_for_pc'));
		}
		else
		{
			$pause_time = get_option('euc_'.$this->plugin_slug.'_pausetime');
			if(!$pause_time || ($pause_time && time() > $pause_time+(60*60*24) ))
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
			$r = update_option('euc_'.$this->plugin_slug.'_pc', $_GET["euc_input_pc"]);
			wp_safe_redirect(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'wp-admin/index.php');
			exit;
		}
		elseif(isset($_GET["euc_action"]) && $_GET["euc_action"] == 'download')
		{
			$this->api->download_item($this->purchase_code);
		}
		elseif(isset($_GET["euc_remind_later"]))
		{
			$this->remind_later();
			wp_safe_redirect(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'wp-admin/index.php');
			exit;
		}
		elseif(isset($_GET["euc_dont_remind"]) && is_numeric($_GET["euc_dont_remind"]))
		{
			$this->dont_remind($_GET["euc_dont_remind"]);
			wp_safe_redirect(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'wp-admin/index.php');
			exit;
		}
	}

	function update_check()
	{
		$version = $this->recent_version;
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
	    $dont_remind = get_option('euc_'.$this->plugin_slug.'_dontremind');
	    if($new_version > $recent_version && (!$dont_remind || ($dont_remind != (int)$new_version))) :
	        ?>
	        <div class="update-nag">
	            New <strong><?=$this->plugin_name?> plugin</strong> update available. <a href="?euc_action=download">Click here</a> to download newest version of the plugin.
	        	<br /><br />
	        	<div style="float:right"><a href="?euc_remind_later" onclick="if(confirm('Are you sure?')) return true; else return false;">remind me later</a>&nbsp;<a href="?euc_dont_remind=<?=$new_version?>" onclick="if(confirm('Are you sure?')) return true; else return false;">don't warn me about this version again</a></div>
	        </div>
	    <?php
	    endif;
	}

	function remind_later()
	{
		update_option('euc_'.$this->plugin_slug.'_pausetime', time());
		add_action('admin_notices', array($this, 'remind_later_notice'));
	}

	function remind_later_notice()
	{
		?>
		<div class="update-nag">
            OK, we will remind you again 24 hours later.
        </div>
        <?php
	}

	function dont_remind($version)
	{
		update_option('euc_'.$this->plugin_slug.'_dontremind', $version);
		add_action('admin_notices', array($this, 'dont_remind_notice'));
	}

	function dont_remind_notice()
	{
		?>
		<div class="update-nag">
            OK, we won't warn you again for this version.
        </div>
        <?php
	}
}
