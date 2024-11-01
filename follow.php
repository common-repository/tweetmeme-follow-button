<?php
/*
Plugin Name: TweetMeme Follow Button
Plugin URI: http://tweetmeme.com/about/plugins
Description: Adds a button which lets you follow Twitter users.
Version: 0.2
Author: TweetMeme
Author URI: http://tweetmeme.com
*/

function tm_follow_options() {
	add_menu_page('Follow', 'Follow', 8, basename(__FILE__), 'tm_follow_page');
}

/* Follow Widget
--------------------------------------------------------------------------------------------------------------------- */

class Follow_Widget extends WP_Widget {
	/**
	* Intilaize
	*/
	function Follow_Widget() {
		$widget_ops = array(
			'classname' => 'widget_rss_links',
			'description' => 'Display the TweetMeme follow button.'
		);
		$this->WP_Widget(false, 'Follow Button');
	}

	/**
	* Widget Rendered
	*/
	function widget($args, $instance) {
		extract($args);
		// get the variables
		$screen_name = $instance['screen_name'];
		$style = $instance['style'];
		// echo out the before data
		echo $before_widget;
		// check the style
    	if ($screen_name) {
        	if ($style == 'normal') {
        		?>
        		<iframe src="http://api.tweetmeme.com/v2/follow.js?screen_name=<?php echo $instance['screen_name']; ?>" width="85" height="30" frameborder="0">
        		</iframe>
        		<?php
			} else {
				?>
        		<iframe src="http://api.tweetmeme.com/v2/follow.js?screen_name=<?php echo $instance['screen_name']; ?>&style=compact" width="75" height="16" frameborder="0">
        		</iframe>
        		<?php
			}
		}
		// echo out the after widget data
    	echo $after_widget;
	}

	/**
	* Update
	*/
	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	/**
	* Form HTML
	*/
	function form($instance) {
		$screen_name = esc_attr($instance['screen_name']);
		$style = esc_attr($instance['style']);
        ?>
            <p>
            	<label for="<?php echo $this->get_field_id('screen_name'); ?>">
            		<?php _e('Twitter Screen Name'); ?>
            		<input class="widefat" id="<?php echo $this->get_field_id('screen_name'); ?>" name="<?php echo $this->get_field_name('screen_name'); ?>" type="text" value="<?php echo $screen_name; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('style'); ?>">
            		<?php _e('Style'); ?>
            		<select name="<?php echo $this->get_field_name('style'); ?>" id="<?php echo $this->get_field_id('style'); ?>">
            			<option value="normal" <?php if ($style == 'normal') echo 'selected="selected"'; ?>>Normal</option>
            			<option value="compact" <?php if ($style == 'compact') echo 'selected="selected"'; ?>>Compact</option>
            		</select>
            	</label>
            </p>
        <?php
	}
}

/* Follow Button Output
--------------------------------------------------------------------------------------------------------------------- */

/**
* Render the follow button
*
* @param string $screen_name
* @param string $style
* @return string
*/
function tm_follow($screen_name, $style) {
	ob_start();
	if ($screen_name) {
    	if ($style == 'normal') {
        	?>
        	<iframe src="http://api.tweetmeme.com/v2/follow.js?screen_name=<?php echo $instance['screen_name']; ?>" width="85px" height="30px" frameborder="0">
        	</iframe>
        	<?php
		} else {
			?>
        	<iframe src="http://api.tweetmeme.com/v2/follow.js?screen_name=<?php echo $instance['screen_name']; ?>&style=compact" width="75px" height="16px" frameborder="0">
        	</iframe>
        	<?php
		}
		return ob_get_clean();
	}
	return false;
}

/**
* Adds a twitter username field to the comment area
*/
function tm_add_twitter_field() {
	global $current_user;
	// get the current user
    get_currentuserinfo();
    // if they are not logged in
	if (!$current_user->ID) {
		ob_start();
		?>
		<label for="screen_name">Twitter Username</label>
		<input type="text" tabindex="4" size="27" value="" id="screen_name" name="screen_name">
		<?php
		echo ob_get_clean();
	}
}

/**
* Add the comment information when the comment is submitted REQUIRES >= 2.9
*
* @param int $id ID of the post
*/
function tm_add_meta($id) {
	$screen_name = isset($_POST['screen_name']) ? trim($_POST['screen_name']) : false;
	// make sure we have a screen_name
	if ($screen_name) {
		// insert into the DB
		add_comment_meta($id, 'screen_name', $screen_name);
	}
}

/**
* Replace the avatar with the follow button
*
* @param string $avatar
* @param mixed $id_or_email
* @param int $size
* @return string
*/
function tm_get_avatar_tweet($avatar, $id_or_email, $size) {
	// is it an object (must be comment object)
	if (is_object($id_or_email)) {
		// is the person logged in
		if (isset($id_or_email->user_id) && $id_or_email->user_id != 0) {
			// if so get their usermeta data
			$screen_name = get_usermeta($id_or_email->user_id, 'screen_name');
		} else {
			// posted a comment, so get it out the comment meta data
			$screen_name = get_comment_meta($id_or_email->comment_ID, 'screen_name');
			$screen_name = count($screen_name) > 0 ? $screen_name[0] : FALSE;
		}
	}

	// render the button
	if ($screen_name) {
		ob_start();
		?>
			<iframe class="avatar" src="http://api.tweetmeme.com/v2/follow.js?screen_name=<?php echo $screen_name; ?>&style=square&size=<?php echo $size; ?>" width="<?php echo $size; ?>" height="<?php echo $size; ?>" frameborder="0" scrolling="no">
			</iframe>
		<?php
		return ob_get_clean();
	}
	return $avatar;
}

/**
* Add the screen_name field into the user edit
*
* @param mixed $profileuser
*/
function tm_admin_twitter_username($profileuser)
{
	$screen_name = get_usermeta($profileuser->ID, 'screen_name');
	ob_start();
	?>
		<h3>Twitter Details</h3>
		<table class="form-table">
			<tbody>
			<tr>
				<th><label for="screen_name">Twitter Username</label></th>
				<td><input type="text" name="screen_name" id="screen_name" value="<?php echo $screen_name; ?>" class="regular-text" /><br>
				<span class="description">We can display your twitter profile pic next to your comments and posts (if enabled).</span></td>
			</tr>
			</tbody>
		</table>
	<?php
	echo ob_get_clean();
}

/**
* Update the users information
*
* @param mixed $user_id
*/
function tm_profile_update($user_id)
{
	$screen_name = $_POST['screen_name'];
	if ($screen_name) {
		update_usermeta($user_id, 'screen_name', $screen_name);
	}
}

/**
* Replace the author information
*
* @param mixed $name
* @return string
*/
function tm_get_the_author($name)
{
	// shouldn't be in the feed it is invalid
	if (is_feed()) {
		return $name;
	}

	global $authordata;
	$screen_name = get_usermeta($authordata->ID, 'screen_name');

	$type = get_option('tm_follow_type');
	if ($type == 'normal') {
		$height = 30;
		$width = 85;
	} else if ($type == 'compact') {
		$height = 16;
		$width = 70;
	}

	if ($screen_name) {
		ob_start();
		?>
			<iframe class="avatar" src="http://api.tweetmeme.com/v2/follow.js?screen_name=<?php echo $screen_name; ?>&style=<?php echo $type; ?>" height="<?php echo $height; ?>" width="<?php echo $width; ?>" frameborder="0" scrolling="no">
			</iframe>
		<?php
		return ob_get_clean();
	}
}

function tm_follow_page()
{
	?>
	<div class="wrap">
    <div class="icon32" id="icon-options-general"><br/></div><h2>Settings for Tweetmeme Follow Button</h2>
    <form method="post" action="options.php">
    <?php
        // New way of setting the fields, for WP 2.7 and newer
        if(function_exists('settings_fields')){
            settings_fields('tm-follow-options');
        } else {
            wp_nonce_field('update-options');
            ?>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="tm_follow_display_comment,tm_follow_display_author,tm_follow_type" />
            <?php
        }
    ?>
        <table class="form-table">
	        <tr>
	            <th scope="row">
	                Options
	            </th>
	            <td>
					<p>
	                    <input type="checkbox" value="1" <?php if (get_option('tm_follow_display_comment') == '1') echo 'checked="checked"'; ?> name="tm_follow_display_comment" id="tm_follow_display_comment"/>
	                    <label for="tm_follow_display_comment">Replace Comment Avatars (Square button only)</label>
	                </p>
	                If a user uses a twitter name the profile picture of that user would be used instead of the gravatar. If they don't it will default back to the gravatar.
	                <p>
	                    <input type="checkbox" value="1" <?php if (get_option('tm_follow_display_author') == '1') echo 'checked="checked"'; ?> name="tm_follow_display_author" id="tm_follow_display_author"/>
	                    <label for="tm_follow_display_author">Replace Author</label><br/>
	                </p>
	                Replace the author (<code>the_author()</code>, <code>get_the_author()</code>) with the
	                <select name="tm_follow_type">
	                	<option value="normal">Default</option>
	                	<option value="compact"> <?php if (get_option('tm_follow_type') == 'compact') echo 'selected="selected"'; ?>Compact</option>
	                </select> button.

	            </td>
	        </tr>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
    </div>
	<?php
}


/* Activation Hooks
----------------------------------------------------------------------------------------------------------------------*/

// Only all the admin options if the user is an admin
if(is_admin()){
    add_action('admin_menu', 'tm_follow_options');
    add_action('admin_init', 'tm_follow_init');
}

function tm_follow_init(){
    if(function_exists('register_setting')){
        register_setting('tm-follow-options', 'tm_follow_display_comment');
        register_setting('tm-follow-options', 'tm_follow_display_author');
        register_setting('tm-follow-options', 'tm_follow_type');
	}
}

// register the follow widget
add_action('widgets_init', create_function('', 'return register_widget("Follow_Widget");') );
// bind into the comments
if (get_option('tm_follow_display_comment') == 1) {
	add_action('comment_form', 'tm_add_twitter_field');
	add_action('comment_post', 'tm_add_meta');
	add_filter('get_avatar', 'tm_get_avatar_tweet', 10, 3);
}

add_action('show_user_profile', 'tm_admin_twitter_username');
add_action('personal_options_update', 'tm_profile_update');

if (get_option('tm_follow_display_author') == 1) {
	add_filter('the_author', 'tm_get_the_author');
}