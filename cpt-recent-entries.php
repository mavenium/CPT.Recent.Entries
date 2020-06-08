<?php

/**
 * Plugin Name: CPT Recent Entries Widgets
 * Plugin URI:  https://github.com/mavenium/CPT.Recent.Entries
 * Description: Display a list of the most recent "Custom Post Type" entries in the wordpress widgets.
 * Version:     1.0.0
 * Author:      Mehdi Namaki (Mavenium)
 * Author URI:  https://mavenium.github.io/
 * License:     GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain: cpt-recent-entries-widgets
 * Domain Path: /languages/
 */

add_action( 'widgets_init', 'register_cpt_recent_entries_widgets' );
function register_cpt_recent_entries_widgets() {
    load_plugin_textdomain( 'cpt-recent-entries-widgets', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    register_widget( 'cpt_recent_entries_widgets' );
}

class cpt_recent_entries_widgets extends WP_Widget {

    /**
     * cpt_recent_entries_widgets constructor.
     */
    public function __construct() {
        $widget_ops = array(
            'classname'                   => 'widget_cpt_recent_entries',
            'description'                 => __( 'Custom Post Type Recent Entries Widget', 'cpt-recent-entries-widgets' ),
            'customize_selective_refresh' => true,
        );

        parent::__construct( 'cpt_recent_entries', __( 'Custom Post Type Recent Entries', 'cpt-recent-entries-widgets' ), $widget_ops );
        $this->alt_option_name = 'widget_cpt_recent_entries';
    }

    /**
     * @param array $args       Including 'before_title', 'after_title', 'before_widget', 'after_widget'
     * @param array $instance   Current widget instance
     */
    function widget( $args, $instance ) {

        if ( ! isset( $args['widget_id'] ) ) {
            $args['widget_id'] = $this->id;
        }

        //Our variables from the widget settings.
        $title = ! empty( $instance['title'] ) ? apply_filters('widget_title', $instance['title'] ) : apply_filters('widget_title', __( 'Recent Entries', 'cpt-recent-entries-widgets' ) );
        $post_type = ! empty( $instance['post_type'] ) ? $instance['post_type'] : 'post';
        $number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;

        echo $args['before_widget'];

        // Display the widget title
        if ( $title )
            echo $args['before_title'] . $title . $args['after_title'];

        // Display Entries
        $the_query = new WP_Query( 'post_type='.$post_type.'&showposts='.$number );
        if ($the_query->have_posts()) :
            $out = "<ul>";
            // The Loop
            while ( $the_query->have_posts() ) : $the_query->the_post();
                $out .= '<li><a href="'.get_permalink().'" title="'.get_the_title().'">'.get_the_title().'</a></li>';
            endwhile;
            $out .= "</ul>";
            echo $out;
        else:
            echo "<p>'.__( 'No Recent Entries Found!', 'cpt-recent-entries-widgets' ).'</p>";
        endif;

        // Reset Post Data
        wp_reset_postdata();

        // Reset Query
        wp_reset_query();
        // Display Entries

        echo $args['after_widget'];
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        //Strip tags from title and post_type to remove HTML
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['post_type'] = wp_strip_all_tags( $new_instance['post_type'] );
        $instance['number'] = (int) $new_instance['number'];

        return $instance;
    }


    /**
     * @param array $instance
     * @return string|void
     */
    function form( $instance ) {

        $defaults = array(
            'title' => __( 'Recent Entries', 'cpt-recent-entries-widgets' ),
            'post_type' => 'post',
        );

        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        $number	= isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
        $post_type  = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';
        $instance = wp_parse_args( (array) $instance, $defaults ); ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'cpt-recent-entries-widgets' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
        </p>


        <p>
            <label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php esc_html_e( 'Post Type:', 'cpt-recent-entries-widgets' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>">
                <?php
                foreach ( $post_types as $pt_key => $pt_value ) {
                    if ('attachment' != $pt_key) {
                        printf(
                            '<option value="%s"%s>%s</option>',
                            esc_attr( $pt_key ),
                            selected( $pt_key, $post_type, false ),
                            __( $pt_value->label, 'cpt-recent-entries-widgets' )
                        );
                    }
                }
                ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Number Of Entries:', 'cpt-recent-entries-widgets' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" value="<?php echo $number; ?>" />
        </p>

        <?php
    }
}

?>