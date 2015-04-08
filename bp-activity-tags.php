<?php
/* 
 * Plugin Name: BuddyPress Activity Tags
 * Plugin URI: http://grial.usal.es/agora/pfcgrial/bp-activity-tags
 * Description: Adds a widget that displays a tag cloud with tags from new blog posts in BuddyPress Activity tab.
 * Version: 1.2
 * Requires at least: BuddyPress 1.2.5
 * Tested up to: BuddyPress 1.9.2 + WordPress 3.8.1
 * Author: Alicia García Holgado
 * Author URI: http://agora.grial.eu/mambanegra
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Network: true
*/

/*  Copyright 2010  Alicia García Holgado  ( email : aliciagh@usal.es )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !defined( 'DIRECTORY_SEPARATOR' ) ) {
	if ( strpos( php_uname( 's' ), 'Win' ) !== false )
		define( 'DIRECTORY_SEPARATOR', '\\' );
	else
		define( 'DIRECTORY_SEPARATOR', '/' );
}

/**
 * Make sure BuddyPress is loaded before we do anything.
 */
require_once( ABSPATH.DIRECTORY_SEPARATOR.'wp-admin'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'plugin.php' );
if ( is_plugin_active( 'buddypress'.DIRECTORY_SEPARATOR.'bp-loader.php' ) ) {
	add_action( 'widgets_init', 'bp_activity_tags_register' );
} else {
	add_action( 'admin_notices', 'bp_activity_tags_install_buddypress_notice' );
	return;
}

/**
 * Make sure Multisite is activated before we do anything.
 */
if ( !defined( 'MULTISITE' ) || MULTISITE == false) {
	add_action( 'admin_notices', 'bp_activity_tags_install_multisite_notice' );
	return;
}

if( !function_exists( 'bp_activity_tags_install_buddypress_notice' ) ) {
	function bp_activity_tags_install_buddypress_notice() {
		echo '<div id="message" class="error fade"><p>';
		_e( '<strong>BuddyPress Activity Tags</strong></a> requires the BuddyPress plugin to work. Please <a href="http://buddypress.org/download">install BuddyPress</a> first, or <a href="plugins.php">deactivate BuddyPress Activity Tags</a>.' );
		echo '</p></div>';
	}
}

if( !function_exists( 'bp_activity_tags_install_multisite_notice' ) ) {
	function bp_activity_tags_install_multisite_notice() {
		echo '<div id="message" class="error fade"><p>';
		_e( '<strong>BuddyPress Activity Tags</strong></a> requires multisite installation. Please <a href="http://codex.wordpress.org/Create_A_Network">create a network</a> first, or <a href="plugins.php">deactivate BuddyPress Activity Tags</a>.' );
		echo '</p></div>';
	}
}

load_plugin_textdomain( 'bp-activity-tags', false, dirname( plugin_basename( __FILE__ ) ) . DIRECTORY_SEPARATOR. 'languages' );

/**
 * Widget definition.
 */
if( !class_exists( 'Bp_Activity_Tags_Widget' ) ) {
	class Bp_Activity_Tags_Widget extends WP_Widget {
		/**
		 * Widget actual processes.
		 */
		function Bp_Activity_Tags_Widget() {
			/* Widget settings. */
			$widget_ops = array( 'classname' => 'bp-activity-tags', 'description' => '' );
	
			/* Widget control settings. */
			$control_ops = array( 'id_base' => 'bp-activity-tags' );
	
			/* Create the widget. */
			$this->WP_Widget( 'bp-activity-tags', $name = __( 'BP Activity Tags', 'bp-activity-tags' ), $widget_ops, $control_ops );
		}
		
		/**
		 * Outputs the options form on admin.
		 */
		function form( $instance ) {
			/* Set up some default widget settings. */
			$defaults = array(
						'title' => __( 'BP Activity Tags', 'bp-activity-tags' ),
						'page' => __( 'activity-tags', 'bp-activity-tags' ),
						'max' => 45,
						'orderby' => 'random',
						'order'	=> 'asc',
						'sizemin' => 8,
						'sizemax' => 22,
						'unit' => 'pt',
						'colormin' => '#CCCCCC',
						'colormax' => '#000000' );
			
			$instance = wp_parse_args( ( array ) $instance, $defaults ); ?>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'bp-activity-tags' ); ?>:</label><br />
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:95%;" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'page' ); ?>"><?php _e( 'Page', 'bp-activity-tags' ); ?>:</label><br />
				<input id="<?php echo $this->get_field_id( 'page' ); ?>" name="<?php echo $this->get_field_name( 'page' ); ?>" value="<?php echo $instance['page']; ?>" style="width:95%;" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'max' ); ?>"><?php _e( 'Max tags to display: (default: 45)', 'bp-activity-tags' ); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'max' ); ?>" name="<?php echo $this->get_field_name( 'max' ); ?>" value="<?php echo $instance['max']; ?>" style="width:95%;" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order by for display tags:', 'bp-activity-tags' ); ?></label><br />
				<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
					<option <?php if ( $instance['orderby'] == 'name' ) echo 'selected="selected"'; ?> value="name"><?php _e( 'Name', 'bp-activity-tags' ); ?></option>
					<option <?php if ( $instance['orderby'] == 'count' ) echo 'selected="selected"'; ?> value="count"><?php _e( 'Counter', 'bp-activity-tags' ); ?></option>
					<option <?php if ( $instance['orderby'] == 'random' ) echo 'selected="selected"'; ?> value="random"><?php _e( 'Random (default)', 'bp-activity-tags' ); ?></option>
				</select>
			</p>
		
			<p>
				<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order for display tags:', 'bp-activity-tags' ); ?></label><br />
				<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
					<option <?php if ( $instance['order'] == 'asc' ) echo 'selected="selected"'; ?> value="asc"><?php _e( 'ASC', 'bp-activity-tags' ); ?></option>
					<option <?php if ( $instance['order'] == 'desc' ) echo 'selected="selected"'; ?> value="desc"><?php _e( 'DESC (default)', 'bp-activity-tags' ); ?></option>
				</select>
			</p>
		
			<p>
				<label for="<?php echo $this->get_field_id( 'sizemin' ); ?>"><?php _e( 'Font size mini: (default: 8)', 'bp-activity-tags' ); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'sizemin' ); ?>" name="<?php echo $this->get_field_name( 'sizemin' ); ?>" value="<?php echo $instance['sizemin']; ?>" style="width:95%;" />
			</p>
		
			<p>
				<label for="<?php echo $this->get_field_id( 'sizemax' ); ?>"><?php _e( 'Font size max: (default: 22)', 'bp-activity-tags' ); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'sizemax' ); ?>" name="<?php echo $this->get_field_name( 'sizemax' ); ?>" value="<?php echo $instance['sizemax']; ?>" style="width:95%;" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'unit' ); ?>"><?php _e( 'Unit font size:', 'bp-activity-tags' ); ?></label><br />
				<select id="<?php echo $this->get_field_id('unit'); ?>" name="<?php echo $this->get_field_name( 'unit' ); ?>">
					<option <?php if ( $instance['unit'] == 'pt' ) echo 'selected="selected"'; ?> value="pt"><?php _e( 'Point (default)', 'bp-activity-tags' ); ?></option>
					<option <?php if ( $instance['unit'] == 'px' ) echo 'selected="selected"'; ?> value="px"><?php _e( 'Pixel', 'bp-activity-tags' ); ?></option>
					<option <?php if ( $instance['unit'] == 'em' ) echo 'selected="selected"'; ?> value="em"><?php _e( 'Em', 'bp-activity-tags' ); ?></option>
					<option <?php if ( $instance['unit'] == '%' ) echo 'selected="selected"'; ?> value="%"><?php _e( 'Pourcent', 'bp-activity-tags' ); ?></option>
				</select>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'colormin' ); ?>"><?php _e( 'Font color mini: (default: #CCCCCC)', 'bp-activity-tags' ); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'colormin' ); ?>" name="<?php echo $this->get_field_name( 'colormin' ); ?>" value="<?php echo $instance['colormin']; ?>" style="width:95%;" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'colormax' ); ?>"><?php _e( 'Font color max: (default: #000000)', 'bp-activity-tags'); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'colormax' ); ?>" name="<?php echo $this->get_field_name( 'colormax' ); ?>" value="<?php echo $instance['colormax']; ?>" style="width:95%;" />
			</p>
			<?php
		}
	
		/**
		 * Processes widget options to be saved.
		 */
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
	
			/* Strip tags ( if needed ) and update the widget settings. */
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['page'] = strip_tags( $new_instance['page'] );
			$instance['max'] = strip_tags( $new_instance['max'] );
			$instance['orderby'] = strip_tags( $new_instance['orderby'] );
			$instance['order'] = strip_tags( $new_instance['order'] );
			$instance['sizemin'] = strip_tags( $new_instance['sizemin'] );
			$instance['sizemax'] = strip_tags( $new_instance['sizemax'] );
			$instance['colormin'] = strip_tags( $new_instance['colormin'] );
			$instance['colormax'] = strip_tags( $new_instance['colormax'] );
			$instance['unit'] = strip_tags( $new_instance['unit'] );
			
			return $instance;
		}
		
		/**
		 * Outputs the content of the widget.
		 */
		function widget( $args, $instance ) {
			extract( $args );
	
			/* User-selected settings. */
			$title = apply_filters( 'widget_title', $instance['title'] );
			$page = $instance['page'];
			$max = $instance['max'];
			$orderby = $instance['orderby'];
			$order = $instance['order'];
			$sizemin = $instance['sizemin'];
			$sizemax = $instance['sizemax'];
			$unit = $instance['unit'];
			$colormin = $instance['colormin'];
			$colormax = $instance['colormax'];
			
			/* Before widget ( defined by themes ). */
			echo $before_widget;
	
			/* Title of widget ( before and after defined by themes ). */
			if ( $title )
				echo $before_title . $title . $after_title;
			
			/* Show activity tags. */
			$this->show_activity_tags( $page, $max, $orderby, $order, $sizemin, $sizemax, $unit, $colormin, $colormax );
			
			/* After widget ( defined by themes ). */
			echo $after_widget;
		}
		
		function get_activity_tags() {
			global $wpdb, $bp;
			
			$tags = array();
			
			$activity = $wpdb->get_results( $wpdb->prepare( "SELECT item_id, secondary_item_id FROM {$bp->activity->table_name} WHERE component='blogs' AND type='new_blog_post'" ) );
			
			foreach( $activity as $a ) {
				$terms = $wpdb->get_results($wpdb->prepare( "SELECT terms.term_id, name FROM {$wpdb->get_blog_prefix( $a->item_id )}term_relationships ".
				"LEFT JOIN ({$wpdb->get_blog_prefix( $a->item_id )}terms as terms, {$wpdb->get_blog_prefix( $a->item_id )}term_taxonomy as tax) ".
				"ON (terms.term_id=tax.term_id and {$wpdb->get_blog_prefix( $a->item_id )}term_relationships.term_taxonomy_id=tax.term_taxonomy_id) ".
				"WHERE tax.taxonomy='post_tag' and object_id={$a->secondary_item_id}" ) );
				
				/* Get global count for each tag. */
				foreach( $terms as $t ) {
					if( empty( $tags[strtolower( $t->name )] ) ) {
						$tags[strtolower( $t->name )]= 1;
					} else {
						$tags[strtolower( $t->name )] = $tags[strtolower( $t->name )] + 1;
					}
				}
			}
			
			return $tags;
		}
		
		function show_activity_tags( $page, $max, $orderby, $order, $sizemin, $sizemax, $unit, $colormin, $colormax ) {
			$tags = $this->get_activity_tags();
			
			/* Order terms before output. */
		
			/* Tags number. */
			if( count( $tags ) > $max ) {
				arsort($tags);
				$tags = array_slice( $tags, 0, $max );
			} elseif ( $orderby == 'count' && $order == 'desc' ) {
				arsort($tags);
			}
			
			if ( $orderby == 'count' && $order == 'asc' ) {
				asort($tags);
			} elseif ( $orderby == 'name' && $order == 'asc' ) {
				ksort($tags);
			} elseif ( $orderby == 'name' && $order == 'desc' ) {
				krsort($tags);
			} elseif ($orderby == 'random' ) { /* Ramdom order. */
				$keys = array_keys($tags);
				shuffle($keys);
				foreach( (array) $keys as $key ) {
					$new[$key] = $tags[$key];
				}
				$tags = $new;
			}
			
			/* Use full RBG code. */
			if ( strlen($colormax) == 4 )
				$colormax = $colormax . substr($colormax, 1, strlen($colormax));
			if ( strlen($colormin) == 4 ) 
				$colormin = $colormin . substr($colormin, 1, strlen($colormin));
			
			/* Check as smallest inferior or egal to largest. */
			if ( $sizemin > $sizemax )
				$sizemin = $sizemax;

			/* Scaling. */
			$scale_min = 1;
			$scale_max = 10;
			
			$minval = min($tags);
			$maxval = max($tags);
			
			$minout = max($scale_min, 0);
			$maxout = max($scale_max, $minout);
			
			$scale = ($maxval > $minval) ? (($maxout - $minout) / ($maxval - $minval)) : 0; 
			
			echo '<div class="bp-activity-tags_div">';
			foreach( $tags as $name => $count ) {
				$scale_result = (int) (($count - $minval) * $scale + $minout);
				
				/* Styling each tag. */
				if ( $scale_result !== null ) {
					$font_size = 'font-size:'.$this->round(($scale_result - $scale_min)*($sizemax-$sizemin)/($scale_max - $scale_min) + $sizemin, 2).$unit.';';
					$color = 'color:'.$this->getColorByScale($this->round(($scale_result - $scale_min)*(100)/($scale_max - $scale_min), 2), $colormin, $colormax).';';
				}
				
				?>
				<a style="<?php echo $font_size.$color ?>" title="<?php echo $count.' '.__( 'posts', 'bp-activity-tags' )?>" href="<?php echo get_blog_details(BLOG_ID_CURRENT_SITE)->siteurl.'/'.$page.'/?bptags='.urlencode( $name ); ?>"><?php echo $name ?></a>
				<?php
			}
			echo '</div>';
		}
		
		/**
		 * From Simple Tags widget
		 */
		function round( $value, $approximation ) {
			$value = round( $value, $approximation );
			$value = str_replace( ',', '.', $value ); // Fixes locale comma
			$value = str_replace( ' ', '' , $value ); // No space
			return $value;
		}
	
		/**
		 * From Simple Tags widget
		 */
		function getColorByScale($scale_color, $min_color, $max_color) {
			$scale_color = $scale_color / 100;
			
			$minr = hexdec(substr($min_color, 1, 2));
			$ming = hexdec(substr($min_color, 3, 2));
			$minb = hexdec(substr($min_color, 5, 2));
			
			$maxr = hexdec(substr($max_color, 1, 2));
			$maxg = hexdec(substr($max_color, 3, 2));
			$maxb = hexdec(substr($max_color, 5, 2));
			
			$r = dechex(intval((($maxr - $minr) * $scale_color) + $minr));
			$g = dechex(intval((($maxg - $ming) * $scale_color) + $ming));
			$b = dechex(intval((($maxb - $minb) * $scale_color) + $minb));
			
			if (strlen($r) == 1) $r = '0'.$r;
			if (strlen($g) == 1) $g = '0'.$g;
			if (strlen($b) == 1) $b = '0'.$b;
			
			return '#'.$r.$g.$b;
		}
	}
}

/**
 * Register the Widget.
 */
if( !function_exists( 'bp_activity_tags_register' ) ) {
	function bp_activity_tags_register() {
		register_widget( 'Bp_Activity_Tags_Widget' );
	}
}

/**
 * Add style file if it exists.
 */
if( !function_exists( 'bp_activity_tags_style' ) ) {
	function bp_activity_tags_style() {
		$styleurl = WP_PLUGIN_URL."/".basename( dirname( __FILE__ ) ).DIRECTORY_SEPARATOR."style.css";
		$styledir = WP_PLUGIN_DIR."/".basename( dirname( __FILE__ ) ).DIRECTORY_SEPARATOR."style.css";
		
		if( file_exists( $styledir ) )
			wp_enqueue_style( 'bp_activity_tags_css_style', $styleurl );
	}
}
add_action( 'wp_print_styles', 'bp_activity_tags_style' );

/**
 * Init search variables.
 */
add_filter( 'query_vars', 'bp_activity_tags_queryvars' );
if( !function_exists( 'bp_activity_tags_queryvars' ) ) {
	function bp_activity_tags_queryvars( $qvars ) {
	  $qvars[] = 'bptags';
	  return $qvars;
	}
}

/**
 * Shortcodes definition.
 */
if( !function_exists( 'my_activity_user_link' ) ) {
	function my_activity_user_link($activity) {
		if ( empty( $activity->user_id ) || !function_exists( 'bp_core_get_user_domain' ) )
			$link = $activity->primary_link;
		else
			$link = bp_core_get_user_domain( $activity->user_id );
	
		return apply_filters( 'bp_get_activity_user_link', $link );
	}
}

if( !function_exists( 'my_activity_avatar' ) ) {
	function my_activity_avatar( $activity, $args = '' ) {
		$defaults = array(
			'type' => 'thumb',
			'width' => 20,
			'height' => 20,
			'class' => 'avatar',
			'alt' => __( 'Avatar', 'buddypress' ),
			'email' => false
		);
	
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
	
		$item_id = false;
		if ( (int)$activity->user_id )
			$item_id = $activity->user_id;
		else if ( $activity->item_id )
			$item_id = $activity->item_id;
	
		$object = apply_filters( 'bp_get_activity_avatar_object_blogs', 'user' );
	
		/* If this is a user object pass the users' email address for Gravatar so we don't have to refetch it. */
		if ( empty($email) && function_exists( 'bp_core_get_user_email' ) )
			$email = bp_core_get_user_email($activity->user_id);
	
		return apply_filters( 'bp_get_activity_avatar', bp_core_fetch_avatar( array( 'item_id' => $item_id, 'object' => $object, 'type' => $type, 'alt' => $alt, 'class' => $class, 'width' => $width, 'height' => $height, 'email' => $email ) ) );
	}
}

if( !function_exists( 'bp_activity_tags_page' ) ) {
	function bp_activity_tags_page( $atts ) {
		global $wpdb, $bp;
		
		$tag = apply_filters( 'get_search_query', get_query_var( 'bptags' ) );
	
		if( !empty( $tag ) ) {
			
			$search = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->activity->table_name} WHERE component='blogs' AND type='new_blog_post'" ) );
	
			if( empty( $search ) ) { ?>
				<div id="message" class="bp-activity-tags_info info">
					<p><?php echo __( "There aren't new activity with tag", 'bp-activity-tags' ).' '.$tag; ?></p>
				</div>
			<?php 
			} else { ?>
	        	<h3 class="bp-activity-tags_header"><?php echo __( 'Recent activity posts with tag', 'bp-activity-tags' ) ?> <span class='bp-activity-tags_term'><?php echo $tag; ?></span></h3>
	        
	            <ul id="activity-stream" class="bp-activity-tags_ul activity-list item-list">
	            
	            <?php foreach( $search as $activity ) {
	            	$check = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->get_blog_prefix( $activity->item_id )}terms as terms ".
	            		"LEFT JOIN ({$wpdb->get_blog_prefix( $activity->item_id )}term_relationships as rel, {$wpdb->get_blog_prefix( $activity->item_id )}term_taxonomy as tax) ".
	            			"ON (rel.term_taxonomy_id=tax.term_taxonomy_id and tax.taxonomy='post_tag' and terms.term_id=tax.term_id) ".
	            		"WHERE rel.object_id={$activity->secondary_item_id} AND terms.name='{$tag}'" ) );
	            	
	            	if( !empty( $check->term_id )) { ?>
	                
		                <li class="bp-activity-tags_li blogs new_blog_post" id="activity-<?php echo $activity->id; ?>">
							<div class="bp-activity-tags_avatar activity-avatar">
								<a href="<?php echo my_activity_user_link( $activity ); ?>">
									<?php echo my_activity_avatar( $activity, 'type=full&width=100&height=100' ); ?>
								</a>
							</div>
					
							<div class="bp_activity-tags_content activity-content">
								<div class="bp_activity-tags_action activity-header">
									<?php echo $activity->action; ?>
								</div>
					
								<?php if ( !empty( $activity->content ) ) : ?>
									<div class="bp_activity-tags_inner activity-inner">
										<?php echo $activity->content; ?>
									</div>
								<?php endif; ?>
							</div>
						</li>
					
				<?php } 
	            } ?>
				
				</ul>
	    <?php }
	    } else { ?>
		    <div id="message" class="bp-activity-tags_info info">
				<p><?php _e( "Sorry, but you are looking for something that isn't here.", 'bp-activity-tags' ) ?></p>
			</div>
	    <?php
	    }
	}
}
add_shortcode( 'bp_activity_tags', 'bp_activity_tags_page' );

?>