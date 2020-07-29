<?php
function dimag_enqueue_styles() {
 
    $parent_style = 'twentyseventeen-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
 
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );

    //if (is_front_page()) {
		// due to the bug  of getwid, we add this css manually.
	    //wp_enqueue_style( 'getwid-blocks-css', 'https://dimag.ibs.re.kr/cms/wp-content/plugins/getwid/assets/css/blocks.style.css');
    //}
    wp_enqueue_style( 'dimag-style',
        get_stylesheet_directory_uri() . '/dimag.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}

function dimag_widgets_init() {
        register_sidebar( array(
                'name'          => __( 'Event', 'twentyseventeen' ),
                'id'            => 'sidebar-event',
                'description'   => __( 'Add widgets here to appear in your footer.', 'twentyseventeen' ),
                'before_widget' => '<section id="%1$s" class="widget %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h2 class="widget-title">',
                'after_title'   => '</h2>',
        ) );
}
/**
 * Registers an editor stylesheet for the theme.
 */
function dimag_add_editor_style() {
    add_editor_style( 'editor-style.css' );
}
function dimag_talk_content(){
	//$pods=pods('talk',get_the_id());
	//$speakers=$pods->field('speaker');
	//$speaker_id=get_post_meta(get_the_id(),"speaker",true);
	//$speaker=get_the_title($speakers[0]['ID']);

	$talkdate=trim(get_post_meta(get_the_id(),"talkdate",true));
	$occasion=trim(get_post_meta(get_the_id(),"occasion",true));
	$full_dates=trim(get_post_meta(get_the_id(),"full_dates",true));
	$location=trim(get_post_meta(get_the_id(),"location",true));
	$city=trim(get_post_meta(get_the_id(),"city",true));
	$country=trim(get_post_meta(get_the_id(),"country",true));
	$url=trim(get_post_meta(get_the_id(),"url",true));
	echo $talkdate, ": ";
	$terms=get_the_terms(get_the_id(),'researcher');
	if ($terms && !is_wp_error($terms)){
		foreach ($terms as $speaker):
			echo "<strong>",$speaker->name, "</strong>, ";
		endforeach;
	}
	if (!empty($url)) :
		echo sprintf(
			' <a href="%1$s"><i>%2$s</i></a>',
			$url,$occasion);
	else:
		echo $occasion;
	endif;
	if (!empty($full_dates)):
		echo ' (', $full_dates,')';
	endif;
	if (!empty($location)):
		echo ", ", $location;
	endif;
	echo ", ", $city, ", ", $country,".";
	edit_post_link(
		'<span class="dashicons dashicons-edit"></span>',
		'<span class="edit-link">',
		'</span>'
	);
	echo "<ul><li>",get_the_title(),"</li></ul>";
}
function dimag_paper_content(){

	$content=strip_tags(
		apply_filters('the_content',
			str_replace('<br />',' ',get_the_content())),
		'<a><strong><em><i><sub><sup>');

	$doi=trim(get_post_meta(get_the_id(),"doi",true));
	$arxiv=trim(get_post_meta(get_the_id(),"arxiv",true));
	if (!empty($doi)) :
		if (substr($doi,0,4)==="http") {
			$link=sprintf(
				' <a href="%1$s">',
				str_replace('%2F','/',rawurlencode($doi)));
		}
		else {
			$link=sprintf(
				' <a href="https://doi.org/%1$s">',
				str_replace('%2F','/',rawurlencode($doi)));
		}
		echo str_replace(
			"</em>","</em></a>",
			str_replace("<em>",$link."<em>",$content));
	elseif (!empty($arxiv)):
		$link=sprintf(
			' <a href="https://arxiv.org/abs/%1$s">',
			rawurlencode($arxiv));
		echo str_replace(
			"</em>","</em></a>",
			str_replace("<em>",$link."<em>",$content));
	else:
		echo $content;
	endif;
	echo ' ';
	edit_post_link(
		'<span class="dashicons dashicons-edit"></span>',
		'<span class="edit-link">',
		'</span>'
	);
	echo '<div class="paperinfo">';
	if (!empty($doi) && !(substr($doi,0,4)==="http")) {
		echo sprintf(
		' <a class="doi" href="https://doi.org/%1$s"><span class="lb">doi</span>%2$s</a>',
		str_replace('%2F','/',rawurlencode($doi)),$doi);
	}
	if (!empty($arxiv)) {
		echo sprintf(
		' <a class="arxiv" href="https://arxiv.org/abs/%1$s"><span class="lb">arXiv</span>%2$s</a>',
		rawurlencode($arxiv),$arxiv);
	}
	echo '<span class="lastupdated">Last update: ';
	the_modified_date('F j, Y');
	echo '</span></div>';
}
function list_talks($atts){
    static $paper_no=0;
    ob_start();

    // define attributes and their defaults
    extract( shortcode_atts( array (
        'posts' => -1,
        'year' => -1,
    ), $atts ) );

    // define query parameters based on attributes
    if ($year==-1) {
	    $options = array(
		'post_type' => 'talk',
		'post_status' => 'publish',
		'orderby'=>'talkdate',
		'order'=>'DESC',
		'posts_per_page' => $posts);
    }
    else {
	$startdate=$year.'-01-01';
	$enddate=$year.'-12-31';
	    $options = array(
		'post_type' => 'talk',
		'post_status' => 'publish',
		'posts_per_page' => $posts,
		'orderby'=>'talkdate',
		'order'=>'DESC',
		'meta_key'=>'talkdate',
		'meta_query'=>array(array(
				'key'=>'talkdate',
				'value'=>array($startdate,$enddate),
				'compare'=> 'BETWEEN',
				'type'=>'DATE'))
	    );
    }
    $query = new WP_Query( $options );
    if ($query->have_posts()) {
	    // run the loop based on the query
	?> <ol class="dimag-papers dimag-talks" start="<?php echo $paper_no+1;?>"><?php 
	    while ( $query->have_posts() ): $query->the_post()  ?>
		<li class="<?php list_researcher_slug();?>">
		<?php
		$paper_no++;
		dimag_talk_content();
		?>
		</li>
	    <?php
	    endwhile;?>
		</ol>
	<?php
	    $myvariable = ob_get_clean();
	    wp_reset_query();
	    return $myvariable;
	}
}
function list_papers($atts){
    static $paper_no=0;
    ob_start();

    // define attributes and their defaults
    extract( shortcode_atts( array (
        'posts' => -1,
        'category' => '',
    ), $atts ) );

    // define query parameters based on attributes
    if ($category=='') {
	    $options = array(
		'post_status' => 'publish',
		'post_type' => 'paper',
		//'orderby' => 'modified',
		'posts_per_page' => $posts);
    }
    else {
	    $options = array(
		'post_status' => 'publish',
		'post_type' => 'paper',
		//'orderby' => 'modified',
		'posts_per_page' => $posts,
		'tax_query' => array(
			array(
			'taxonomy'=>'paper_type',
			'field'=>'slug',
		'terms'=>$category)
		)
	    );
    }
    $query = new WP_Query( $options );
    if ($query->have_posts()) {
	    // run the loop based on the query
	?> <ol class="dimag-papers <?php echo $category;?>" start="<?php
		echo $paper_no+1;
		?>"><?php 
	    while ( $query->have_posts() ): $query->the_post()  ?>
		<li class="<?php list_researcher_slug();?>">
		<?php
		$paper_no++;
		dimag_paper_content();
		?>
		</li>
	    <?php
	    endwhile;?>
		</ol>
	<?php
	    $myvariable = ob_get_clean();
	    wp_reset_query();
	    return $myvariable;
	}
}
function list_all_researchers() {
	ob_start();
	?>
	<div id="index-researcher" class="btn-group btn-group-toggle" data-toggle="buttons">
	<label class="btn btn-secondary active"  role="button"
	onclick='document.querySelectorAll("ol.dimag-papers>li").forEach(e=>e.style.display="list-item");var p=1; document.querySelectorAll("ol.dimag-papers").forEach(e=> (e.start=p,p+=e.querySelectorAll("ol>li").length))' >
	<input type="radio" checked autocomplete="off" name="author" id="all">All</label>
	<?php
	foreach (get_terms( array('taxonomy' => 'researcher',
				'orderby'=>'slug')) as $researcher): ?>
	<label class="btn btn-secondary " role="button"  
	onclick='document.querySelectorAll("ol.dimag-papers > li:not(<?php
        echo $researcher->slug?>)").forEach(e=>e.style.display="none");document.querySelectorAll("ol.dimag-papers > li.<?php echo $researcher->slug?>").forEach(e=>e.style.display="list-item");var p=1; document.querySelectorAll("ol.dimag-papers").forEach(e=> (e.start=p,p+=e.querySelectorAll("ol>li:not([style*=\"display: none;\"])").length))'>
		<input type="radio" autocomplete="off"
		name="author" 
		id="<?php echo $researcher->slug;?>"><?php echo $researcher->name;?></label>
		<?php
	endforeach;

	?>
	</div><?php
	return ob_get_clean();
}
function list_researcher_slug(){
	$taxonomy = 'researcher';
	// Get the term IDs assigned to post.
	$post_terms = wp_get_object_terms( get_the_id(), $taxonomy);
	 
	if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
	 
	    foreach ($post_terms as $researcher_id):
		echo $researcher_id->slug," ";
	    endforeach;
	}
}
function paper_category(){
	$taxonomy = 'paper_type';
	 
	// Get the term IDs assigned to post.
	$post_terms = wp_get_object_terms( get_the_id(), $taxonomy, array( 'fields' => 'ids' ) );
	 
	// Separator between links.
	$separator = ', ';
	 
	if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
	 
	    $term_ids = implode( ',' , $post_terms );
	 
	    $terms = wp_list_categories( array(
		'title_li' => '',
		'style'    => 'none',
		'echo'     => false,
		'taxonomy' => $taxonomy,
		'include'  => $term_ids
	    ) );
	 
	    $terms = rtrim( trim( str_replace( '<br />',  $separator, $terms ) ), $separator );
 
	    // Display post categories.
	    echo  $terms;
	}
}
// https://core.trac.wordpress.org/ticket/14652
// Escape iframe 
function nacin_filter_menu_target_sorry_world( $menu_item ) {
    if ( ! is_admin() && empty( $menu_item->target ) )
        $menu_item->target = '_top';
    return $menu_item;
}


// https://www.linode.com/community/questions/19293/youtube-embed-issues-in-london-and-atlanta#answer-70826
// Fix YouTube issues? Doesn't work
//add_action( 'http_api_curl', function( $curl_handle ) {
 //  curl_setopt( $curl_handle, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
//});

add_filter( 'wp_setup_nav_menu_item', 'nacin_filter_menu_target_sorry_world' );
add_shortcode( 'papers', 'list_papers' );
add_shortcode( 'talks', 'list_talks' );
add_shortcode( 'index-researchers', 'list_all_researchers' );

add_action( 'widgets_init', 'dimag_widgets_init' );
add_action( 'wp_enqueue_scripts', 'dimag_enqueue_styles' );
add_action( 'admin_init', 'dimag_add_editor_style' );
