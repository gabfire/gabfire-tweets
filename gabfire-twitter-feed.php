<?php
/*
	Plugin Name: Gabfire Twitter Feed
	Plugin URI: http://www.gabfirethemes.com
	Description: Display tweets in a sidebar based off of many criteria.
	Author: Gabfire Themes
	Version: 1.0
	Author URI: http://www.gabfirethemes.com

	Copyright 2013 Gabfire Themes (email : info@gabfire.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

add_action('widgets_init', array('GabfireTweetsWidget', 'gabfire_tweets_register_widget'));
add_action('init', array('GabfireTweetsWidget', 'gabfire_load_textdoamin'));

class GabfireTweetsWidget extends WP_Widget {

	private static $text_domain = 'gabfire-twitter-feed';

	/**
	 * Load the text domain
	 *
	 * @since 1.0.0
	 */
	static function gabfire_load_textdoamin() {
		load_plugin_textdomain(self::$text_domain, false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Hooks to 'widgets_init'
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static function gabfire_tweets_register_widget() {
		register_widget('GabfireTweetsWidget');
	}

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'gabfire-twitter-feed', // Base ID
			__('Gabfire Twitter Feed', self::$text_domain), // Name
			array( 'description' => __( 'Display a twitter feed based off username or search parameters', self::$text_domain), 'width' => 500) // Args
		);
	}

    /**
     * widget function.
     *
     * @access public
     * @param mixed $args
     *
     * @param mixed $instance
     * @return void
     */
    function widget($args, $instance) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$tweets_base = $instance['tweets_base'];

		echo $before_widget;

			if ( $title ) {
				echo $before_title . $title . $after_title;
			}

			if ($instance['tweets_base'] == 'username'){
				echo $this->gt_get_twitter_data($instance,'username');
			} else {
				echo $this->gt_get_twitter_data($instance,'hashtag');
			}

		echo $after_widget;
    }

	/**
	 * update function.
	 *
	 * @access public
	 * @param mixed $new_instance
	 *
	 * @param mixed $old_instance
	 * @return void
	 */
	function update($new_instance, $old_instance) {
		$instance['title']	= ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['tweets_base'] = ( ! empty( $new_instance['tweets_base'] ) ) ? sanitize_text_field($new_instance['tweets_base']) : '';
		$instance['tweets_of'] = ( ! empty( $new_instance['tweets_of'] ) ) ? sanitize_text_field($new_instance['tweets_of']) : '';
		$instance['profile_photo'] = ( ! empty( $new_instance['profile_photo'] ) ) ? sanitize_text_field($new_instance['profile_photo']) : '';
		$instance['tweets_nr'] = ( ! empty( $new_instance['tweets_nr'] ) ) ? (int) sanitize_text_field($new_instance['tweets_nr']) : '';

		$instance['consumer_key'] = ( ! empty( $new_instance['consumer_key'] ) ) ? sanitize_text_field($new_instance['consumer_key']) : '';
		$instance['consumer_secret'] = ( ! empty( $new_instance['consumer_secret'] ) ) ? sanitize_text_field($new_instance['consumer_secret']) : '';
		$instance['access_token_key'] = ( ! empty( $new_instance['access_token_key'] ) ) ? sanitize_text_field($new_instance['access_token_key']) : '';
		$instance['access_token_secret'] = ( ! empty( $new_instance['access_token_secret'] ) ) ? sanitize_text_field($new_instance['access_token_secret']) : '';

		return $new_instance;
	}

    /**
     * form function.
     *
     * @access public
     * @param mixed $instance
     * @return void
     */
    function form($instance) {
		$defaults	= array(
			'title' => 'Twitter Feed',
			'tweets_base' => 'username',
			'profile_photo' => 'display',
			'tweets_nr' => 5,
			'tweets_of' => ''
		);

		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', self::$text_domain); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo isset($instance['title']) ? esc_attr($instance['title']) : ''; ?>" />
		</p>

		<!-- Filters -->

		<p>
			<label for="<?php echo $this->get_field_id( 'tweets_base' ); ?>"><?php _e('Tweets based on:', self::$text_domain); ?></label>
			<select id="<?php echo $this->get_field_id( 'tweets_base' ); ?>" name="<?php echo $this->get_field_name( 'tweets_base' ); ?>">
				<option value="username" <?php if ( 'username' == $instance['tweets_base'] ) echo 'selected="selected"'; ?>><?php _e('Username', self::$text_domain); ?></option>
				<option value="hashtag" <?php if ( 'hashtag' == $instance['tweets_base'] ) echo 'selected="selected"'; ?>><?php _e('Hashtag', self::$text_domain); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'profile_photo' ); ?>"><?php _e('Twitter profile photo:', self::$text_domain); ?></label>
			<select id="<?php echo $this->get_field_id( 'profile_photo' ); ?>" name="<?php echo $this->get_field_name( 'profile_photo' ); ?>">
				<option value="display" <?php if ( 'display' == $instance['profile_photo'] ) echo 'selected="selected"'; ?>><?php _e('Display', self::$text_domain); ?></option>
				<option value="hide" <?php if ( 'hide' == $instance['profile_photo'] ) echo 'selected="selected"'; ?>><?php _e('Hide', self::$text_domain); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_name( 'tweets_nr' ); ?>"><?php _e('Number of Tweets?', self::$text_domain); ?></label>
			<select id="<?php echo $this->get_field_id( 'tweets_nr' ); ?>" name="<?php echo $this->get_field_name( 'tweets_nr' ); ?>">
			<?php
				for ( $i = 1; $i <= 10; ++$i )
				echo "<option value='$i' " . selected( $instance['tweets_nr'], $i, false ) . ">$i</option>";
			?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('tweets_of'); ?>"><?php _e('Enter Username or Hashtag', self::$text_domain); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('tweets_of'); ?>" name="<?php echo $this->get_field_name('tweets_of'); ?>" type="text" value="<?php echo isset($instance['tweets_of']) ? esc_attr($instance['tweets_of']) : ''; ?>" />
		</p>

		<!-- Keys -->

		<p>
			<label for="<?php echo $this->get_field_id('consumer_key'); ?>"><?php _e('API Key', self::$text_domain); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('consumer_key'); ?>" name="<?php echo $this->get_field_name('consumer_key'); ?>" type="text" value="<?php echo isset($instance['consumer_key']) ? esc_attr($instance['consumer_key']) : ''; ?>" />

			<label for="<?php echo $this->get_field_id('consumer_secret'); ?>"><?php _e('API Secret', self::$text_domain); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('consumer_secret'); ?>" name="<?php echo $this->get_field_name('consumer_secret'); ?>" type="text" value="<?php echo isset($instance['consumer_secret']) ? esc_attr($instance['consumer_secret']) : ''; ?>" />

			<label for="<?php echo $this->get_field_id('access_token_key'); ?>"><?php _e('Access Token Key', self::$text_domain); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('access_token_key'); ?>" name="<?php echo $this->get_field_name('access_token_key'); ?>" type="text" value="<?php echo isset($instance['access_token_key']) ? esc_attr($instance['access_token_key']) : ''; ?>" />

			<label for="<?php echo $this->get_field_id('access_token_secret'); ?>"><?php _e('Access Token Secret', self::$text_domain); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('access_token_secret'); ?>" name="<?php echo $this->get_field_name('access_token_secret'); ?>" type="text" value="<?php echo isset($instance['access_token_secret']) ? esc_attr($instance['access_token_secret']) : ''; ?>" />
		</p>

		<p><?php _e('Get recent Tweets based on an username or #hashtag. Visit <a target="_blank" href="https://dev.twitter.com/apps">https://dev.twitter.com/apps</a> to get your application and secret keys.', self::$text_domain); ?></p>

		<?php
	}


	/**
	 * gt_get_twitter_data function.
	 *
	 * @access private
	 * @param mixed $options
	 *
	 * @param mixed $tweets_base
	 * @return void
	 */
	private function gt_get_twitter_data($options, $tweets_base) {

		if ($options['consumer_key'] == '' || $options['consumer_secret'] == '' || $options['access_token_key'] == '' || $options['access_token_secret'] == '') {
			return __('Twitter Authentication data is incomplete','gabfire-widget-pack');
		}

		if (!class_exists('Codebird')) {
			require_once ('lib/codebird.php');
		}

		Codebird::setConsumerKey($options['consumer_key'], $options['consumer_secret']);
		$cb = Codebird::getInstance();
		$cb->setToken($options['access_token_key'], $options['access_token_secret']);
		$cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);

		$count = 0;
		$target = 'target="_blank"';

		$out = '<ul class="gabfire-tweets">';

		if ($tweets_base == 'hashtag') {
			$reply = get_transient('gabfire_socialmashup_widget_twitter_search_transient');
			if (false === $reply){
				try {
					$reply = $cb->search_tweets(array(
								'q'=>'#'.$options['tweets_of'],
								'count'=> $options['tweets_nr']
						));
				} catch (Exception $e) {
					return __('Error retrieving tweets','gabfire-widget-pack');
				}
				if (isset($reply['errors'])) {
					//error_log(serialize($reply['errors']));
				}
				set_transient('gabfire_socialmashup_widget_twitter_transient',$reply,300);
			}

			if (empty($reply) or count($reply) < 1) {
				return __('No public tweets with' . $reply . ' hashtag','gabfire-widget-pack');
			}

			if (isset($reply['statuses']) && is_array($reply['statuses'])) {

				foreach($reply['statuses'] as $message) {
					if ($count>=$options['tweets_nr']) {
						break;
					}

					if (!isset($message['text'])) {
						continue;
					}

					$msg = $message['text'];

					$out .= '<li style="list-style: none;margin-bottom: 10px;">';
					if ($options['profile_photo'] == 'display') {
						$out .= '<img class="alignright" src="'.$message['user']['profile_image_url_https'].'" alt="" />';
					}

					/* Code from really simpler twitter widget */

					$msg = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\" ".$target.">$1</a>", $msg);

					$msg = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\" ".$target.">$1</a>", $msg);

					$msg = preg_replace('/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i',"<a href=\"mailto://$1\" class=\"twitter-link\" ".$target.">$1</a>", $msg);

					$msg = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://twitter.com/#!/search/%23\2" class="twitter-link" '.$target.'>#\2</a>', $msg);

					$msg = preg_replace('/([\.|\,|\:|\¡|\¿|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/$2\" class=\"twitter-user\" ".$target.">@$2</a>$3 ", $msg);

					$out .= $msg;
					$out .= '</li>';
					$count++;
				}
			}

		} elseif ($tweets_base == 'username') {
			$reply = get_transient('gabfire_socialmashup_widget_twitter_username_transient');

			if (false === $reply){
				try {
					$twitter_data =  $cb->statuses_userTimeline(array(
								'screen_name'=>$options['tweets_of'],
								'count'=> $options['tweets_nr']
						));
				} catch (Exception $e) {
					return __('Error retrieving tweets','gabfire-widget-pack');
				}

				if (isset($reply['errors'])) {
					//error_log(serialize($reply['errors']));
				}

				set_transient('gabfire_socialmashup_widget_twitter_username_transient',$reply,300);
			}

			if (empty($twitter_data) or count($twitter_data)<1) {
				return __('No public tweets','gabfire-widget-pack');
			}

			if (isset($twitter_data) && is_array($twitter_data)) {
				foreach($twitter_data as $message) {
					if ($count>=$options['tweets_nr']) {
						break;
					}

					if (!isset($message['text'])) {
						continue;
					}

					$msg = $message['text'];

					$out .= '<li style="list-style: none;margin-bottom: 10px;">';
					if ($options['profile_photo'] == 'display') {
						$out .= '<img class="alignright" src="'.$message['user']['profile_image_url_https'].'" alt="" />';
					}


					/* Code from really simpler twitter widget */

					$msg = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\" ".$target.">$1</a>", $msg);

					$msg = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\" ".$target.">$1</a>", $msg);
					$msg = preg_replace('/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i',"<a href=\"mailto://$1\" class=\"twitter-link\" ".$target.">$1</a>", $msg);

					$msg = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://twitter.com/#!/search/%23\2" class="twitter-link" '.$target.'>#\2</a>', $msg);

					$msg = preg_replace('/([\.|\,|\:|\¡|\¿|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/$2\" class=\"twitter-user\" ".$target.">@$2</a>$3 ", $msg);


					$out .= $msg;
					$out .= '</li>';
					$count++;
				}
			}
		}

		$out .= '</ul>';

		return $out;
	}
}