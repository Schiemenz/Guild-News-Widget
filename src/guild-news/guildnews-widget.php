<?php

/**
 * Plugin Name: Guild News Widget
 * Plugin URI: https://github.com/Schiemenz/Guild-News-Widget
 * Description: A widget that shows guild news retrieved via the Blizzard Community Platform API for World of Warcraft.
 * Version: 0.1
 * Author: Frank Schiemenz
 * Author URI: https://github.com/Schiemenz
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action('widgets_init', 'example_load_widgets');

/**
 * Register our widget.
 * 'GuildNews_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function example_load_widgets()
{
	register_widget('GuildNews_Widget');
}

/**
 * Guild News Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class GuildNews_Widget extends WP_Widget
{

	/**
	 * Widget setup.
	 */
	function GuildNews_Widget()
	{
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'guildnews',
				'description' => __('A widget that shows guild news retrieved via the Blizzard Community Platform API for World of Warcraft.', 'guildnews') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300,
				'height' => 400,
				'id_base' => 'guildnews-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'guildnews-widget', __('Guild News Widget', 'guildnews'), $widget_ops, $control_ops );

	}

	/**
	 * How to display the widget on the screen.
	 * @param unknown_type $args
	 * @param unknown_type $instance
	 */
	function widget($args, $instance)
	{
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title']);
		$guild = $instance['guild'];
		$region = $instance['region'];
		$realm = $instance['realm'];
		$max = $instance['max'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if($title)
		{
			echo $before_title . $title . $after_title;
		}

		/* Display the news items retrieves via REST. */
		if($region &&  $guild && $realm)
		{
			$api_guild_news_url = "http://" . $region . "/api/wow/guild/" . rawurlencode($realm) . "/" . rawurlencode($guild) . "?fields=news";

			$file_contents = @file_get_contents($api_guild_news_url); // omit warnings

			$guildNews = json_decode($file_contents, true);

			if(sizeof($guildNews['news']) == 0)
			{
				echo __('Keine aktuellen News gefunden.', 'guildnews');
			}
			else
			{
				$counter = 0;

				foreach($guildNews['news'] as $news)
				{
					if($counter++ == $max && $max != "")
					{
						break;
					}

					switch($news['type'])
					{
						case "guildLevel" :
							{
								echo '<p>';
								/* echo __('Am ', 'guildnews') . date("d.m.", $news['timestamp']/1000) . __(' hat die Gilde die Stufe ', 'guildnews') . $news['levelUp'] . __(' erreicht.', 'guildnews'); */
								echo $guild . __(' erreichte Stufe ', 'guildnews') . $news['levelUp'] . ".";
								echo '</p>';
								break;
							}

						case "guildAchievement" :
							{
								echo '<p>';
								/* echo __('Am ', 'guildnews') . date("d.m.", $news['timestamp']/1000) . __(' haben wir den Erfolg ', 'guildnews') .
								 '<a href="http://www.wowhead.com/achievement=' . $news['achievement']['id'] . '">' . $news['achievement']['title'] . '</a>' . __(' errungen.', 'guildnews'); */
								echo $guild . __(' errang ', 'guildnews') . ' <a href="http://www.wowhead.com/achievement=' . $news['achievement']['id'] . '">' . $news['achievement']['title'] . '</a>' . ".";
								echo '</p>';
								break;
							}

						case "playerAchievement" :
							{
								echo '<p>';
								/* echo __('Am ', 'guildnews') . date("d.m.", $news['timestamp']/1000) . __(' hat ', 'guildnews') . '<strong>' .  $news[character] . '</strong>' . __(' den Erfolg ', 'guildnews') .
								 '<a href="http://www.wowhead.com/achievement=' . $news['achievement']['id'] . '">' . $news['achievement']['title'] . '</a>' . __(' errungen.', 'guildnews'); */
								echo $news[character] . __(' errang ', 'guildnews') . ' <a href="http://www.wowhead.com/achievement=' . $news['achievement']['id'] . '">' . $news['achievement']['title'] . '</a>' . ".";
								echo '</p>';
								break;
							}

						case "itemPurchase" :
							{
								echo '<p>';
								/* echo __('Am ', 'guildnews') . date("d.m.", $news['timestamp']/1000) . __(' hat ', 'guildnews') . '<strong>' .  $news[character] . '</strong>' .
								 __(' einen ', 'guildnews') . '<a href="http://www.wowhead.com/item=' . $news['itemId'] . '">' .  __(' Gegenstand ', 'guildnews') .'</a>' . __(' erworben.', 'guildnews'); */
								echo $news[character] . __(' erwarb ', 'guildnews') . ' <a href="http://www.wowhead.com/item=' . $news['itemId'] . '">' . $this->getPropertyByItemId($instance, "name", $news['itemId']) .'</a>' . ".";
								echo '</p>';
								break;
							}

						case "itemLoot" :
							{
								echo '<p>';
								/* echo __('Am ', 'guildnews') . date("d.m.", $news['timestamp']/1000) . __(' hat ', 'guildnews') . '<strong>'  . $news[character] . '</strong>' .
								 __(' einen ', 'guildnews') . '<a href="http://www.wowhead.com/item=' . $news['itemId'] . '">' .  __(' Gegenstand ', 'guildnews') .'</a>' . __(' erbeutet.', 'guildnews'); */
								echo $news[character] . __(' lootete ', 'guildnews') . ' <a href="http://www.wowhead.com/item=' . $news['itemId'] . '">' . $this->getPropertyByItemId($instance, "name", $news['itemId']) .'</a>' . ".";
								echo '</p>';
								break;
							}

						case "itemCreated" :
							{
								// TODO newstype 'itemCreated'
								break;
							}

						case "guildCreated" :
							{
								// TODO newstype 'guildCreated'
								break;
							}
					}

				}

			}
		}
			
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Gets the property for the provided ID
	 * @param unknown_type $instance
	 * @param unknown_type $property
	 * @param unknown_type $id
	 * @return mixed
	 */

	function getPropertyByItemId($instance, $property, $id)
	{
		$region = $instance['region'];
		$locale = $instance['locale'];

		$api_item_url = "http://" . $region . "/api/wow/item/" . $id . "?locale=" . $locale;

		$item = json_decode(file_get_contents($api_item_url), true);

		return $item[$property];
	}

	/**
	 * Update the widget settings.
	 * @param unknown_type $new_instance
	 * @param unknown_type $old_instance
	 * @return string
	 */
	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['region'] = strip_tags( $new_instance['region'] );
		$instance['locale'] = strip_tags( $new_instance['locale'] );
		$instance['realm'] = strip_tags( $new_instance['realm'] );
		$instance['guild'] = strip_tags( $new_instance['guild'] );
		$instance['max'] = strip_tags( $new_instance['max'] );

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 * @param unknown_type $instance
	 */
	function form($instance)
	{
		/* Set up some default widget settings. */
		$defaults = array('title' => __('Guild News', 'guildnews'),
				'region' => 'eu.battle.net',
				'locale' => 'de_DE',
				'realm' => 'Blackhand',
				'guild' => 'Embargo Agency',
				'max' => '5'
		);

		$instance = wp_parse_args((array) $instance, $defaults); ?>

<!-- Widget Title: Text Input -->
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?>
	</label> <input id="<?php echo $this->get_field_id( 'title' ); ?>"
		name="<?php echo $this->get_field_name( 'title' ); ?>"
		value="<?php echo $instance['title']; ?>" style="width: 100%;" />
</p>

<!-- Guild Name: Text Input -->
<p>
	<label for="<?php echo $this->get_field_id( 'guild' ); ?>"><?php _e('Guild Name:', 'guildnews'); ?>
	</label> <input id="<?php echo $this->get_field_id( 'guild' ); ?>"
		name="<?php echo $this->get_field_name( 'guild' ); ?>"
		value="<?php echo $instance['guild']; ?>" style="width: 100%;" />
</p>

<!-- Realm Name: Text Input -->
<p>
	<label for="<?php echo $this->get_field_id( 'realm' ); ?>"><?php _e('Realm Name:', 'guildnews'); ?>
	</label> <input id="<?php echo $this->get_field_id( 'realm' ); ?>"
		name="<?php echo $this->get_field_name( 'realm' ); ?>"
		value="<?php echo $instance['realm']; ?>" style="width: 100%;" />
</p>

<!-- Region: Select Box -->
<p>
	<label for="<?php echo $this->get_field_id( 'region' ); ?>"><?php _e('Region:', 'guildnews'); ?>
	</label> <select id="<?php echo $this->get_field_id( 'region' ); ?>"
		name="<?php echo $this->get_field_name( 'region' ); ?>"
		class="widefat" style="width: 100%;">
		<option
		<?php if ( 'eu.battle.net' == $instance['region'] ) echo 'selected="selected"'; ?>>eu.battle.net</option>
		<option
		<?php if ( 'us.battle.net' == $instance['region'] ) echo 'selected="selected"'; ?>>us.battle.net</option>
		<option
		<?php if ( 'kr.battle.net' == $instance['region'] ) echo 'selected="selected"'; ?>>kr.battle.net</option>
		<option
		<?php if ( 'tw.battle.net' == $instance['region'] ) echo 'selected="selected"'; ?>>tw.battle.net</option>
	</select>
</p>

<!-- Locale: Select Box -->
<p>
	<label for="<?php echo $this->get_field_id( 'locale' ); ?>"><?php _e('Locale:', 'guildnews'); ?>
	</label> <select id="<?php echo $this->get_field_id( 'locale' ); ?>"
		name="<?php echo $this->get_field_name( 'locale' ); ?>"
		class="widefat" style="width: 100%;">
		<option
		<?php if ( 'en_US' == $instance['locale'] ) echo 'selected="selected"'; ?>>en_US</option>
		<option
		<?php if ( 'es_MX' == $instance['locale'] ) echo 'selected="selected"'; ?>>es_MX</option>
		<option
		<?php if ( 'pt_BR' == $instance['locale'] ) echo 'selected="selected"'; ?>>pt_BR</option>
		<option
		<?php if ( 'en_GB' == $instance['locale'] ) echo 'selected="selected"'; ?>>en_GB</option>
		<option
		<?php if ( 'es_ES' == $instance['locale'] ) echo 'selected="selected"'; ?>>es_ES</option>
		<option
		<?php if ( 'fr_FR' == $instance['locale'] ) echo 'selected="selected"'; ?>>fr_FR</option>
		<option
		<?php if ( 'ru_RU' == $instance['locale'] ) echo 'selected="selected"'; ?>>ru_RU</option>
		<option
		<?php if ( 'de_DE' == $instance['locale'] ) echo 'selected="selected"'; ?>>de_DE</option>
		<option
		<?php if ( 'pt_PT' == $instance['locale'] ) echo 'selected="selected"'; ?>>pt_PT</option>
		<option
		<?php if ( 'ko_KR' == $instance['locale'] ) echo 'selected="selected"'; ?>>ko_KR</option>
		<option
		<?php if ( 'zh_TW' == $instance['locale'] ) echo 'selected="selected"'; ?>>zh_TW</option>
	</select>
</p>

<!-- Number of News Items: Text Input -->
<p>
	<label for="<?php echo $this->get_field_id( 'max' ); ?>"><?php _e('Number of News Items: (empty for all)', 'guildnews'); ?>
	</label> <input id="<?php echo $this->get_field_id( 'max' ); ?>"
		name="<?php echo $this->get_field_name( 'max' ); ?>"
		value="<?php echo $instance['max']; ?>" style="width: 100%;" />
</p>

<?php

	}
}

?>