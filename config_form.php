
    <?php
    /*
    	$secret = get_option('tumbleka_secret');
    	$token = get_option('tumbleka_token');
    	echo("<br>secret:".$secret."<br>");
    	echo("<br>token:".$token."<br>");
    	*/
    ?>
<div class="field">
    <div id="tumbleka_blog_label" class="two columns alpha">
        <label for="tumbleka_blog"><?php echo __('Tumblr blog name:'); ?></label>
    </div>
    <div class="inputs three columns beta">
        <?php
        
            $blogname = get_option('tumbleka_blog');
            
            if($blogname=="" || !isset($blogname))
            	$blogname = "Not set up!";
            	
            //$blogname = $blogname
            	
        	echo get_view()->formText('tumbleka_blog',$blogname, array()); ?>
        <p class="explanation"><?php echo __('The tumblr blog that is currently authenticated for auto-posts'); ?></p>
    </div>
    <div id="tumbleka_blog_label" class="two columns omega">
    	<?php
			echo get_view()->formButton('tumbleka_register_button',"Update Authentication", array("type"=>"submit"));
			 
			if(TumblekaPlugin::isAuthenticated($blogname))
    		{			?>
    			<p style="text-align:center;border:1px solid green;color:green;margin-top:0px;">Authenticated</p>
<?php		} else {	?>
    			<p style="text-align:center;border:1px solid red;color:red;margin-top:0px;">Not Authenticated</p>
<?php		}
    		
?> 
    	<p class="explanation"><?php echo __('Click button to log in to Tumblr & authorize account for auto-posts'); ?></p>
    </div>
    
</div>

<div class="field">
    <div id="tumbleka_autotumbl_new_label" class="two columns alpha">
        <label for="tumbleka_autoTumbl_new_posts"><?php echo __('Automatically post new items to Tumblr?'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <?php echo get_view()->formCheckbox('tumbleka_autoTumbl_new_posts', true, 
        	array('checked'=>(boolean)get_option('tumbleka_autoTumbl_new_posts'))); ?>
        <p class="explanation"><?php echo __(
            'If checked, new items will automatically post to Tumblr' 
        ); ?></p>
    </div>
</div>
