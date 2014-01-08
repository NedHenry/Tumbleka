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
	    $tumbleka= new TumblekaPlugin();
	    $tumbleka->registerNewTumblrAccount();		
    }
}
