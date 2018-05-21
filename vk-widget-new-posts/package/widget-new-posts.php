<?php

/*-------------------------------------------*/
/*  Side Post list widget
/*-------------------------------------------*/
class WP_Widget_ltg_adv_post_list extends WP_Widget {

	public $taxonomies = array( 'category' );

	function __construct() {

		$widget_name = LIGHTNING_ADVANCED_SHORT_NAME . ' ' . __( 'Content Area Posts Widget', LIGHTNING_ADVANCED_TEXTDOMAIN );

		parent::__construct(
			'ltg_adv_post_list',
			$widget_name,
			array( 'description' => __( 'Displays a list of your most recent posts', LIGHTNING_ADVANCED_TEXTDOMAIN ) )
		);
	}

	/*-------------------------------------------*/
	/*  一覧へのリンクhtmlを出力する関数
	/*-------------------------------------------*/
	static public function more_link_html( $instance ) {
		if ( ! empty( $instance['more_text'] ) && ! empty( $instance['more_url'] ) ) {
			$more_link_html  = '<div class="text-right" style="margin-top:1em;">';
			$more_link_html .= '<a href="' . esc_url( $instance['more_url'] ) . '" class="btn btn-default btn-xs">' . wp_kses_post( $instance['more_text'] ) . '</a>';
			$more_link_html .= '</div>';
		} else {
			$more_link_html = '';
		}
		return $more_link_html;
	}

	function widget( $args, $instance ) {
		global $is_contentsarea_posts_widget;
		$is_contentsarea_posts_widget = true;
		if ( ! isset( $instance['format'] ) ) {
			$instance['format'] = 0; }

		echo $args['before_widget'];
		echo '<div class="pt_' . $instance['format'] . '">';
		if ( ! empty( $instance['label'] ) ) {
			echo $args['before_title'];
			echo $instance['label'];
			echo $args['after_title'];
		} elseif ( ! isset( $instance['label'] ) ) {
			echo $args['before_title'];
			echo __( 'Recent Posts', LIGHTNING_ADVANCED_TEXTDOMAIN );
			echo $args['after_title'];
		}

		$count     = ( isset( $instance['count'] ) && $instance['count'] ) ? $instance['count'] : 10;
		$post_type = ( isset( $instance['post_type'] ) && $instance['post_type'] ) ? $instance['post_type'] : 'post';

		if ( $instance['format'] ) {
			$this->_taxonomy_init( $post_type );
		}

		$p_args = array(
			'post_type'      => $post_type,
			'posts_per_page' => $count,
			'paged'          => 1,
		);

		if ( isset( $instance['terms'] ) && $instance['terms'] ) {
			$taxonomies          = get_taxonomies( array() );
			$p_args['tax_query'] = array(
				'relation' => 'OR',
			);
			$terms_array         = explode( ',', $instance['terms'] );
			foreach ( $taxonomies as $taxonomy ) {
				$p_args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => $terms_array,
				);
			}
		}

		$post_loop = new WP_Query( $p_args );

		if ( $post_loop->have_posts() ) :
			if ( ! $instance['format'] ) {

				while ( $post_loop->have_posts() ) :
					$post_loop->the_post();
					if (
						file_exists( get_stylesheet_directory() . '/module_loop_' . $post_type . '.php' ) &&
						$post_type != 'post'
					) {
						get_template_part( 'module_loop_' . $post_type );
					} else {
						get_template_part( 'module_loop_post' );
					}
				endwhile;
			} elseif ( $instance['format'] == 1 ) {
				while ( $post_loop->have_posts() ) :
					$post_loop->the_post();
					$this->display_pattern_1();
				endwhile;
			}

		endif;
		echo  $this->more_link_html( $instance );
		echo '</div>';
		echo $args['after_widget'];

		wp_reset_postdata();
		wp_reset_query();

	} // widget($args, $instance)

	/*-------------------------------------------*/
	/*  display_pattern_1 Cointent Body
	/*-------------------------------------------*/
	function display_pattern_1() {
		global $post;
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header>
			<?php get_template_part( 'module_loop_post_meta' ); ?>
			<h1 class="entry-title"><?php the_title(); ?></h1>
			</header>

			<div class="entry-body">

			<?php
			$lightning_adv_more_btn_txt = '<span class="btn btn-default btn-block">' . __( 'Read more', LIGHTNING_ADVANCED_TEXTDOMAIN ) . '</span>';
			$more_btn                   = apply_filters( 'lightning-adv-more-btn-txt', $lightning_adv_more_btn_txt );
			global $is_pagewidget;
			$is_pagewidget = true;
			the_content( $more_btn );
			$is_pagewidget = false;
			?>
			</div><!-- [ /.entry-body ] -->

			<div class="entry-footer">
			<?php
			$args          = array(
				'before'      => '<nav class="page-link"><dl><dt>Pages :</dt><dd>',
				'after'       => '</dd></dl></nav>',
				'link_before' => '<span class="page-numbers">',
				'link_after'  => '</span>',
				'echo'        => 1,
			);
			wp_link_pages( $args );
			?>

			<?php
			/*-------------------------------------------*/
			/*  Category and tax data
			/*-------------------------------------------*/
			$args          = array(
				'template'      => __( '<dl><dt>%s</dt><dd>%l</dd></dl>', 'lightning' ),
				'term_template' => '<a href="%1$s">%2$s</a>',
			);
			$taxonomies    = get_the_taxonomies( $post->ID, $args );
			$taxnomiesHtml = '';
			if ( $taxonomies ) {
				foreach ( $taxonomies as $key => $value ) {
					if ( $key != 'post_tag' ) {
						$taxnomiesHtml .= '<div class="entry-meta-dataList">' . $value . '</div>';
					}
				} // foreach
			} // if ($taxonomies)
			$taxnomiesHtml = apply_filters( 'lightning_taxnomiesHtml', $taxnomiesHtml );
			echo $taxnomiesHtml;
			?>

			<?php
			$tags_list = get_the_tag_list();
			if ( $tags_list ) :
			?>
			<div class="entry-meta-dataList entry-tag">
			<dl>
			<dt><?php _e( 'Tags', 'lightning' ); ?></dt>
			<dd class="tagCloud"><?php echo $tags_list; ?></dd>
			</dl>
			</div><!-- [ /.entry-tag ] -->
			<?php endif; ?>
			</div><!-- [ /.entry-footer ] -->

		</article>

	<?php
	}

	function _taxonomy_init( $post_type ) {
		if ( $post_type == 'post' ) {
			return; }
		$this->taxonomies = get_object_taxonomies( $post_type );
	}

	function taxonomy_list( $post_id = 0, $before = ' ', $sep = ',', $after = '' ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID(); }

		$taxo_catelist = array();

		foreach ( $this->taxonomies as $taxonomy ) {
			$terms = get_the_term_list( $post_id, $taxonomy, $before, $sep, $after );
			if ( $terms ) {
				$taxo_catelist[] = $terms; }
		}

		if ( count( $taxo_catelist ) ) {
			return join( $taxo_catelist, $sep ); }
		return '';
	}

	function form( $instance ) {
		$defaults = array(
			'count'     => 10,
			'label'     => __( 'Recent Posts', LIGHTNING_ADVANCED_TEXTDOMAIN ),
			'post_type' => 'post',
			'terms'     => '',
			'format'    => '0',
			'more_url'  => '',
			'more_text' => '',
		);

		$instance = wp_parse_args( (array) $instance, $defaults );
		//タイトル
		?>
		<br/>
		<?php echo _e( 'Display Format', LIGHTNING_ADVANCED_TEXTDOMAIN ); ?>:<br/>
		<ul>
		<li><label><input type="radio" name="<?php echo $this->get_field_name( 'format' ); ?>" value="0"
														<?php
														if ( $instance['format'] == 0 ) {
															echo 'checked'; }
?>
/><?php echo __( 'Thumbnail', LIGHTNING_ADVANCED_TEXTDOMAIN ) . '/' . __( 'Date', LIGHTNING_ADVANCED_TEXTDOMAIN ) . '/' . __( 'Category', LIGHTNING_ADVANCED_TEXTDOMAIN ) . '/' . __( 'Title', LIGHTNING_ADVANCED_TEXTDOMAIN ) . '/' . __( 'Excerpt', LIGHTNING_ADVANCED_TEXTDOMAIN ); ?></label>
		</li>
		<li><label><input type="radio" name="<?php echo $this->get_field_name( 'format' ); ?>" value="1"
														<?php
														if ( $instance['format'] == 1 ) {
															echo 'checked'; }
?>
/><?php echo __( 'Thumbnail', LIGHTNING_ADVANCED_TEXTDOMAIN ) . '/' . __( 'Date', LIGHTNING_ADVANCED_TEXTDOMAIN ) . '/' . __( 'Category', LIGHTNING_ADVANCED_TEXTDOMAIN ) . '/' . __( 'Title', LIGHTNING_ADVANCED_TEXTDOMAIN ) . '/' . __( 'Content', LIGHTNING_ADVANCED_TEXTDOMAIN ); ?></label>
		</li>
		</ul>
		<br/>
		<?php //タイトル ?>
		<label for="<?php echo $this->get_field_id( 'label' ); ?>"><?php _e( 'Title:' ); ?></label><br/>
		<input type="text" id="<?php echo $this->get_field_id( 'label' ); ?>-title" name="<?php echo $this->get_field_name( 'label' ); ?>" value="<?php echo $instance['label']; ?>" />
		<br/><br />

		<?php //表示件数 ?>
		<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Display count', LIGHTNING_ADVANCED_TEXTDOMAIN ); ?>:</label><br/>
		<input type="text" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo $instance['count']; ?>" />
		<br /><br />

		<?php //投稿タイプ ?>
		<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Slug for the custom type you want to display', LIGHTNING_ADVANCED_TEXTDOMAIN ); ?>:</label><br />
		<input type="text" id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>" value="<?php echo esc_attr( $instance['post_type'] ); ?>" />
		<br/><br/>

		<?php // Terms ?>
		<label for="<?php echo $this->get_field_id( 'terms' ); ?>"><?php _e( 'taxonomy ID', LIGHTNING_ADVANCED_TEXTDOMAIN ); ?>:</label><br />
		<input type="text" id="<?php echo $this->get_field_id( 'terms' ); ?>" name="<?php echo $this->get_field_name( 'terms' ); ?>" value="<?php echo esc_attr( $instance['terms'] ); ?>" /><br />
		<?php
		_e( 'if you need filtering by term, add the term ID separate by ",".', LIGHTNING_ADVANCED_TEXTDOMAIN );
		echo '<br/>';
		_e( 'if empty this area, I will do not filtering.', LIGHTNING_ADVANCED_TEXTDOMAIN );
		echo '<br/><br/>';
		?>

		<?php // Read more ?>
		<label for="<?php echo $this->get_field_id( 'more_url' ); ?>"><?php _e( 'Destination URL:', LIGHTNING_ADVANCED_TEXTDOMAIN ); ?></label><br/>
		<input type="text" id="<?php echo $this->get_field_id( 'more_url' ); ?>" name="<?php echo $this->get_field_name( 'more_url' ); ?>" value="<?php echo esc_attr( $instance['more_url'] ); ?>" />
		<br /><br />
		<label for="<?php echo $this->get_field_id( 'more_text' ); ?>"><?php _e( 'Notation text:', LIGHTNING_ADVANCED_TEXTDOMAIN ); ?></label><br/>
		<input type="text" placeholder="最新記事一覧 ≫" id="<?php echo $this->get_field_id( 'more_text' ); ?>" name="<?php echo $this->get_field_name( 'more_text' ); ?>" value="<?php echo esc_attr( $instance['more_text'] ); ?>" />
				<br /><br />
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance              = $old_instance;
		$instance['format']    = $new_instance['format'];
		$instance['count']     = $new_instance['count'];
		$instance['label']     = $new_instance['label'];
		$instance['post_type'] = ! empty( $new_instance['post_type'] ) ? strip_tags( $new_instance['post_type'] ) : 'post';
		$instance['terms']     = preg_replace( '/([^0-9,]+)/', '', $new_instance['terms'] );
		$instance['more_url']  = $new_instance['more_url'];
		$instance['more_text'] = $new_instance['more_text'];
		return $instance;
	}
}
add_action( 'widgets_init', create_function( '', 'return register_widget("WP_Widget_ltg_adv_post_list");' ) );
