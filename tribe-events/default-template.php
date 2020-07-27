<?php
/**
 * Default Events Template
 * This file is the basic wrapper template for all the views if 'Default Events Template'
 * is selected in Events -> Settings -> Display -> Events Template.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/default-template.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.23
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

get_header();
?>
<div class="wrap">
        <div id="primary" class="content-area">
		<main id="main" class="tribe-events-pg-template">
	<?php tribe_events_before_html(); ?>
	<?php tribe_get_view(); ?>
	<?php tribe_events_after_html(); ?>
		</main> <!-- #tribe-events-pg-template -->
	</div>
<aside id="secondary" class="widget-area" role="complementary" aria-label="<?php esc_attr_e( 'Blog Sidebar', 'twentyseventeen' ); ?>">
        <?php dynamic_sidebar( 'sidebar-event' ); ?>
</aside><!-- #secondary -->
</div>
<?php
get_footer();
