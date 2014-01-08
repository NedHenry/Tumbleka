<?php
/**
 * Tumbleka
 *
 * @copyright Copyright 2013 Ned Henry
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

 
	require_once('libraries/httpclient/http.php');
	require_once('libraries/oAuth/oauth_client.php');
/**
 * Tumblr plugin for Omeka
 */
class TumblekaPlugin extends Omeka_Plugin_AbstractPlugin
{
	
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array('install','uninstall','config_form','config','make_item_public');

    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        'tumbleka_autoTumbl_new_posts' => '1',
        'tumbleka_blog' =>'',
        'tumbleka_token' => '',
        'tumbleka_secret' => ''
    );
    
	
	/**
	*	@var string Tumblr oAuth keys for this application
	**/
    protected static $consumer_key = 'QQGtGIcGosIHYBU1GBex9itYPv4BpifEimjWRPRGyLh9R6wZtM';
    protected static $consumer_secret = 'pLGk3Q8w7uqIpqCoCixq2cxJUBji8cMocKXibjYxEkfMbC5xDo';
    
    
	
	public static function isAuthenticated($blog)
	{
		$url = 'http://api.tumblr.com/v2/blog/'.$blog.'/user/info';
		$options = Array();
		$params = Array();
		$client = TumblekaPlugin::getTumblrClient();
		// Process the OAuth server interactions 
		if(($success = $client->Initialize()))
		{
			$client->access_token = get_option('tumbleka_token');
			$client->access_token_secret = get_option('tumbleka_secret');
			$success = $client->CallAPI($url,'GET',$params,$options);
			 
			// Internal cleanup call
			$success = $client->Finalize($success);
		}
		if($success)
			return true;
		else
			return false;
	}
	
		
	public static function registerNewTumblrAccount()
	{
		$client = TumblekaPlugin::getTumblrClient();
		$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].url('tumbleka/oauth/authenticate');
		// Process the OAuth server interactions 
		if(($success = $client->Initialize()))
		{
			$success = $client->Process();
			// If access token was successfully obtained, update options
			 if(strlen($client->access_token))
			 {
			   set_option('tumbleka_token',$client->access_token);
			   set_option('tumbleka_secret',$client->access_token_secret);
			 }
			
			// Internal cleanup call
			$success = $client->Finalize($success);
		}
		self::cleanUpTumblrClient($client,$success);
	}
	
	protected function postToTumblr($blog,$newPost)
	{

		$url = 'http://api.tumblr.com/v2/blog/'.$blog.'/post';
		
		$options = array();
		
		$client = TumblekaPlugin::getTumblrClient();
		$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].url('admin');
		// Process the OAuth server interactions 
		
		if(($success = $client->Initialize()))
		{
			$client->access_token = get_option('tumbleka_token');
			$client->access_token_secret = get_option('tumbleka_secret');
			//$success = $client->Process();  //this would set keys using session storage
			
			 if(strlen($client->access_token))
			 {
	
			   	$success = $client->CallAPI($url,'POST',$newPost,$options);
			 }
			
			// Internal cleanup call
			$success = $client->Finalize($success);
		}
		$this->cleanUpTumblrClient($client,$success);
		
	}
	
	
	protected static function cleanUpTumblrClient($client,$success)
	{
	
		if($client->exit)
			exit;
		if($success)
		{
			$client->Output();
		}
		elseif ($client->response_status == 401) //If the access keys are wrong or privileges have been revoked
		{
			self::unsetAccessKeys();
			self::registerNewTumblrAccount();
		}
		else
		{
			
			?>
				<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
				<html>
				<head>
				<title>OAuth client error</title>
				</head>
				<body>
				<h1>OAuth client error</h1>
				<pre>Error: <?php echo HtmlSpecialChars($client->error); ?></pre>
				</body>
				</html>
			<?php
		}
	}
	
	protected static function unsetAccessKeys()
	{
		set_option('tumbleka_token','');
		set_option('tumbleka_secret','');
		if(isset($_SESSION['OAUTH_ACCESS_TOKEN']))
			unset($_SESSION['OAUTH_ACCESS_TOKEN']);
	}
	
	static function getTumblrClient()
	{
	
		// Create the OAuth authentication client class  
		$client = new oauth_client_class;
		
		$client->debug = true;
		$client->debug_http = true;
		$client->server = 'Tumblr';
	
		/*
		$redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].dirname(strtok($_SERVER['REQUEST_URI'],'?'));
		$redirect_uri = substr($redirect_uri,-7);   //removes "plugins" from the current url - super jenky, fix this later
		$redirect_uri = $redirect_uri."/tumbleka/oauth/authenticate";
		*/
		$client->client_id = self::$consumer_key ;
		$client->client_secret = self::$consumer_secret ;
		$client->scope = '';
		
		return($client);
	}
	
	
	        
    /**
     * Autopost new items to tumblr if that option is selected
     */
    public function hookMakeItemPublic($args)  
	{     
		
		if (!get_option('tumbleka_autoTumbl_new_posts')) {
            return;
        }
        $item= $args['record'];
        //metadata($item,array('Dublin Core','Title')));
        $title = metadata($item,array('Dublin Core','Title'));
        ob_start();
    	$collection = get_collection_for_item($item);	
		if($item->hasThumbnail())
		{
		    ?>
			<div style="text-align:center; border-top:1px solid black;border-bottom:1px solid black;max-width: 75%;margin-left: auto;margin-right: auto;margin-top:30px;">
					<?php
						echo item_image_gallery(Array(),'square_thumbnail', false,$item);
					?>
			</div>
<?php	}		?>
	
		<div style="text-align:center;">
			
			<p style="font-style:italic;margin:25px 75px 25pxpx 75px;font-size:1.25em">
				<?php
					echo metadata($item,array('Dublin Core','Description'));
				?>
			</p>
		</div>
		
		<!-- more -->
		<?php if(isset($collection)&&!is_null($collection)&&$collection!="") { ?>
			<div style="text-align:center;">
				<h3>
					<a href="
					<?php 
						echo record_url($collection);
					?>">
						<?php 
							echo metadata($collection,array('Dublin Core','Title'));
						?>
					</a>
				</h3>
				<p>
					<?php 
						echo metadata($collection,array('Dublin Core','Description'));
					?>
				</p>
			</div>
			<?php
		}
		
        $body = ob_get_clean();
	    
		$tags="";
		if($subject = metadata($item,array('Dublin Core','Subject')) != "")
			$tags=$tags.$subject.",";
		if($source = metadata($item,array('Dublin Core','Source')) != "")
			$tags=$tags.$source.",";
		if($publisher = metadata($item,array('Dublin Core','Publisher')) != "")
			$tags=$tags.$publisher.",";
		$creators = metadata($item,array('Dublin Core','Creator'),'all');
		foreach($creators as $creator)
		{
			$tags=$tags.$creator.",";
		}
		$contributors = metadata($item,array('Dublin Core','Contributor'),'all');
		foreach($contributors as $contributor)
		{
			$tags=$tags.$contributor.",";
		}
		
		$tags=substr($tags,0,strlen($tags)-1);
		
		$newPost = array(
			'type' => 'text',
			'tags' => $tags, //PULL FROM METADATA
			'title'    => $title, //PULL FROM METADATA
			'body' =>   $body
		);
		$blog = get_option('tumbleka_blog');
		
		$this->postToTumblr($blog,$newPost);
		    
    }

    
	/**
     * Install the plugin.
     */
    public function hookInstall()
    {    
	    $this->_installOptions();
	}

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {        
        $this->_uninstallOptions();
    }
 
    /**
     * Display the plugin config form.
     */
    public function hookConfigForm()
    {
	    //set_current_item(get_item_by_id(12));
	    
        require dirname(__FILE__) . '/config_form.php';
    }

    /**
     * Set the options from the config form input.
     */
    public function hookConfig()
    {
        if(isset($_POST['tumbleka_register_button']))
    	{
			self::unsetAccessKeys();
		    self::registerNewTumblrAccount();
	    	//testPostToTumblr('nedhenry.tumblr.com');
	    }
	    
	    set_option('tumbleka_blog', $_POST['tumbleka_blog']);
        set_option('tumbleka_autoTumbl_new_posts', (int)(boolean)$_POST['tumbleka_autoTumbl_new_posts']);
    }

}
