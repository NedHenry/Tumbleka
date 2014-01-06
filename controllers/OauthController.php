<?php
/**
 * The oath authentication controller class.
 *
 * @package Tumbleka
 */
 
class Tumbleka_OauthController extends Omeka_Controller_AbstractActionController
{
    public function authenticateAction()
    {
	    require_once(dirname(dirname(__FILE__)).'/oauth.php');
		registerNewTumblrAccount();
    }
}
