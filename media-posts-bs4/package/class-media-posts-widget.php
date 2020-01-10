<?php

/*-------------------------------------------*/
/*  Media Post BS4 list widget
/*-------------------------------------------*/
class WP_Widget_Media_Posts_BS4 extends WP_Widget {

	public $taxonomies = array( 'category' );

	function __construct() {
		global $vk_media_post_prefix;

		$widget_name = $vk_media_post_prefix . __( 'Media Posts BS4', 'lightning-pro' );
		parent::__construct(
			'media_posts_bs4',
			$widget_name,
			array( 'description' => __( 'It is a widget that displays the post list. Various shapes can be selected.', 'lightning-pro' ) )
		);
	}

	/*-------------------------------------------*/
	/* helper method
	/*-------------------------------------------*/

	public static function widget_title( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} elseif ( isset( $instance['label'] ) ) {
			$title = $instance['label'];
		} else {
			$title = __( 'Recent Posts', 'lightning-pro' );
		}
		return $title;
	}

	// function _taxonomy_init( $post_type ) {
	// 	if ( $post_type == 'post' ) {
	// 		return; }
	// 	$this->taxonomies = get_object_taxonomies( $post_type );
	// }

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

	function options_default() {
		return $options_default = array(
			'count'                      => 6,
			'title'                      => __( 'Recent Posts', 'lightning-pro' ),
			'post_type'                  => array( 'post' => 1 ), // クエリに投げる形式は違うので要変換
			'terms'                      => '',
			'layout'                     => 'media',
			'col_xs'                     => 1,
			'col_sm'                     => 1,
			'col_md'                     => 1,
			'col_lg'                     => 1,
			'col_xl'                     => 1,
			'display_image'              => true,
			'display_image_overlay_term' => true,
			'display_excerpt'            => false,
			'display_date'               => true,
			'display_new'                => true,
			'new_date'                   => 7,
			'new_text'                   => 'New!!',
			'btn_text'                   => __( 'Read more', 'lightning-pro' ),
			'btn_align'                  => 'text-right',
		);
	}


	/*-------------------------------------------*/
	/* widget
	/*-------------------------------------------*/
	function widget( $args, $instance ) {

		$defaults = self::options_default();
		$instance = wp_parse_args( $instance, $defaults );

		if ( ! $instance['post_type'] ) {
			$instance['post_type'] = array( 'post' => 1 );
		}

		$title = self::widget_title( $instance );

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'];
			echo $title;
			echo $args['after_title'];
		}

		// 入力された投稿タイプ配列をクエリに投げる形式の配列に変換
		$throw_query_post_types = array();
		if ( ! $instance['post_type'] ) {
			return;
		}
		foreach ( $instance['post_type'] as $key => $value ) {
			if ( $value ) {
				$throw_query_post_types[] = $key;
			}
		}

		$q_args = array(
			'post_type' => $throw_query_post_types,
		);

		if ( isset( $instance['terms'] ) && $instance['terms'] ) {
			$taxonomies          = get_taxonomies( array() );
			$q_args['tax_query'] = array(
				'relation' => 'OR',
			);
			$terms_array         = explode( ',', $instance['terms'] );
			foreach ( $taxonomies as $taxonomy ) {
				$q_args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => $terms_array,
				);
			}
		}

		$q_args['posts_per_page'] = ( isset( $instance['count'] ) && $instance['count'] ) ? mb_convert_kana( $instance['count'], 'n' ) : 6;

		global $wp_query;
		$wp_query                     = new WP_Query( $q_args );
		$options_loop                 = array();
		$options                      = $instance;
		$options['image_default_url'] = VK_MEDIA_POSTS_BS4_URL . '/images/no-image.png';
		$options['class_outer']       = VK_Component_Posts::get_col_size_classes( $instance );
		VK_Component_Posts::the_loop( $wp_query, $options, $options_loop );

		echo $args['after_widget'];

	} // widget($args, $instance)


	/*-------------------------------------------*/
	/* form
	/*-------------------------------------------*/
	function form( $instance ) {

		$defaults = self::options_default();
		$instance = wp_parse_args( (array) $instance, $defaults );

		/*
			Form _ タイトル
		/*-------------------------------------------*/
		?>
		<br />
		<label><?php _e( 'Title:' ); ?><br/>
		<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="admin-custom-input" /></label>

		<?php
		/*
			Form _ 表示条件
		/*-------------------------------------------*/
		?>

		<div class="admin-custom-section">
		<h2 class="admin-custom-h2"><?php _e( 'Display conditions', 'lightning-pro' ); ?></h2>

		<h3 class="admin-custom-h3"><?php _e( 'Display post types', 'lightning-pro' ); ?></h3>
		<?php
		/*
		  Post Type Check Box
		/*-------------------------------------------*/
			$args = array(
				'post_types_args'    => array(
					'public' => true,
				),
				'name'               => $this->get_field_name( 'post_type' ),
				'checked'            => $instance['post_type'],
				'id'                 => '',
				'exclude_post_types' => array( 'attachment' ),
			);
		// $args       = wp_parse_args( $args, $default );
		$post_types = get_post_types( $args['post_types_args'], 'object' );
		echo '<ul>';
		foreach ( $post_types as $key => $value ) {

			if ( ! in_array( $key, $args['exclude_post_types'] ) ) {

				$checked = ( isset( $args['checked'][ $key ] ) && $args['checked'][ $key ] == 'true' ) ? ' checked' : '';

				if ( $args['id'] ) {
					$id = ' id="' . esc_attr( $args['id'] ) . '"';
				} elseif ( $args['name'] ) {
					$id = ' id="' . esc_attr( $args['name'] ) . '"';
				} else {
					$id = '';
				}

				echo '<li><label>';
				echo '<input type="checkbox" name="' . esc_attr( $args['name'] ) . '[' . $key . ']"' . $id . ' value="true"' . $checked . ' />' . esc_html( $value->label );
				echo '</label></li>';
			}
		}
		echo '</ul>';
		?>

		<h3 class="admin-custom-h3">
			<label for="<?php echo $this->get_field_id( 'terms' ); ?>"><?php _e( 'Category(Term) ID', 'lightning-pro' ); ?></label>
		</h3>

		<input type="text" id="<?php echo $this->get_field_id( 'terms' ); ?>" name="<?php echo $this->get_field_name( 'terms' ); ?>" value="<?php echo esc_attr( $instance['terms'] ); ?>" class="admin-custom-input" /><br />
		<?php
		_e( 'If you need filtering by category(term), add the category ID separate by ",".', 'lightning-pro' );
		echo '<br/>';
		_e( 'If empty this area, I will do not filtering.', 'lightning-pro' );
		echo '<br/><br/>';

		?>

		<?php
		/*
			Form _ 表示件数
		/*-------------------------------------------*/
		?>
		<h3 class="admin-custom-h3">
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Display count', 'lightning-pro' ); ?></label>
		</h3>
		<input type="text" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo $instance['count']; ?>" class="admin-custom-input" />

	</div>

	<?php
	/*
		表示形式とカラム
	/*-------------------------------------------*/
	?>

	<div class="admin-custom-section">

		<h2 class="admin-custom-h2"><?php _e( 'Display type and columns', 'lightning-pro' ); ?></h2>

		<h3 class="admin-custom-h3">
			<?php echo _e( 'Display type', 'lightning-pro' ); ?>
		</h3>

		<?php $patterns = Lightning_Media_Posts_BS4::patterns(); ?>

		<select id="<?php echo $this->get_field_name( 'layout' ); ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>" class="admin-custom-input">
			<?php

			foreach ( $patterns as $key => $value ) {
				if ( $instance['layout'] == $key ) {
					$selected = ' selected="selected"';
				} else {
					$selected = '';
				}

				echo '<option value="' . $key . '"' . $selected . '>' . $value['label'] . '</option>';
			}
			?>
		</select>


		<h3 class="admin-custom-h3">
			<?php _e( 'Columns', 'lightning-pro' ); ?>
		</h3>

		<p><?php _e( 'Please input column count to under the range of 1 to 4.', 'lightning-pro' ); ?></p>

		<?php

		$sizes = array(
			'xs' => array( 'label' => __( 'Extra small', 'lightning-pro' ) ),
			'sm' => array( 'label' => __( 'Small', 'lightning-pro' ) ),
			'md' => array( 'label' => __( 'Medium', 'lightning-pro' ) ),
			'lg' => array( 'label' => __( 'Large', 'lightning-pro' ) ),
			'xl' => array( 'label' => __( 'Extra large', 'lightning-pro' ) ),
		);

		foreach ( $sizes as $key => $value ) {
			$field = 'col_' . $key;
			echo '<label for="' . $this->get_field_id( $field ) . '">' . sprintf( __( 'Column ( Screen size : %s )', 'lightning-pro' ), $value['label'] ) . '</label>';
			echo '<input type="number" max="4" min="1" id="' . esc_attr( $this->get_field_id( $field ) ) . '" name="' . esc_attr( $this->get_field_name( $field ) ) . '" value="' . esc_attr( $instance[ $field ] ) . '" class="admin-custom-input" />';
		}

		?>

	</div>

	<?php
	/*
		表示アイテム
	/*-------------------------------------------*/
	?>

	<div class="admin-custom-section">

		<h2 class="admin-custom-h2"><?php _e( 'Display item', 'lightning-pro' ); ?></h2>

		<?php
		$items = array(
			'display_image'              => __( 'Image', 'lightning-pro' ),
			'display_image_overlay_term' => __( 'Term name', 'lightning-pro' ),
			'display_excerpt'            => __( 'Excerpt', 'lightning-pro' ),
			'display_date'               => __( 'Date', 'lightning-pro' ),
			'display_new'                => __( 'New mark', 'lightning-pro' ),
			'display_btn'                => __( 'Button', 'lightning-pro' ),
		);
		foreach ( $items as $key => $value ) {
			$checked = ( isset( $instance[ $key ] ) && $instance[ $key ] ) ? ' checked' : '';
			echo '<p>';
			echo '<input type="checkbox" value="true" id="' . $this->get_field_id( '' . $key ) . '" name="' . $this->get_field_name( '' . $key ) . '"' . $checked . ' />';
			echo '<label for="' . $this->get_field_id( '' . $key ) . '">' . esc_html( $value ) . '</label>';
			echo '</p>';
		}
		?>


		<h3 class="admin-custom-h3">
				<?php _e( 'New mark option', 'lightning-pro' ); ?>
		</h3>

		<?php
		//NEWアイコン表示期間
		$new_date = ( isset( $instance['new_date'] ) ) ? $instance['new_date'] : 7;
		?>
		<label for="<?php echo $this->get_field_id( 'new_date' ); ?>">
			<?php _e( 'Number of days to display the new post mark', 'lightning-pro' ); ?>
		</label>

		<input type="text" id="<?php echo $this->get_field_id( 'new_date' ); ?>" name="<?php echo $this->get_field_name( 'new_date' ); ?>" value="<?php echo $instance['new_date']; ?>" class="admin-custom-input" />

		<label for="<?php echo $this->get_field_id( 'new_text' ); ?>">
			<?php _e( 'New mark text', 'lightning-pro' ); ?>
		</label>
		<input type="text" id="<?php echo $this->get_field_id( 'new_text' ); ?>" name="<?php echo $this->get_field_name( 'new_text' ); ?>" value="<?php echo $instance['new_text']; ?>" class="admin-custom-input" />


		<h3 class="admin-custom-h3">
				<?php _e( 'Button option', 'lightning-pro' ); ?>
		</h3>

		<label for="<?php echo $this->get_field_id( 'btn_text' ); ?>">
			<?php _e( 'Button text', 'lightning-pro' ); ?>
		</label>
		<input type="text" id="<?php echo $this->get_field_id( 'btn_text' ); ?>" name="<?php echo $this->get_field_name( 'btn_text' ); ?>" value="<?php echo $instance['btn_text']; ?>" class="admin-custom-input" />

		<label for="<?php echo $this->get_field_id( 'btn_align' ); ?>">
			<?php _e( 'Button align', 'lightning-pro' ); ?>
		</label>
		<select id="<?php echo $this->get_field_name( 'btn_align' ); ?>" name="<?php echo $this->get_field_name( 'btn_align' ); ?>" class="admin-custom-input">
			<?php
			$btn_aligns = array(
				'text-left'   => array(
					'label' => __( 'Left', 'lightning-pro' ),
				),
				'text-center' => array(
					'label' => __( 'Center', 'lightning-pro' ),
				),
				'text-right'  => array(
					'label' => __( 'Right', 'lightning-pro' ),
				),
			);
			foreach ( $btn_aligns as $key => $value ) {
				if ( $instance['btn_align'] == $key ) {
					$selected = ' selected="selected"';
				} else {
					$selected;
				}

				echo '<option value="' . $key . '"' . $selected . '>' . $value['label'] . '</option>';
			}
			?>
		</select>

	</div>
	<?php
	}

	/*-------------------------------------------*/
	/* update
	/*-------------------------------------------*/
	function update( $new_instance, $instance ) {
		$instance                               = $new_instance;
		$instance['layout']                     = $new_instance['layout'];
		$instance['col_xs']                     = lightning_sanitize_number( $new_instance['col_xs'] );
		$instance['col_sm']                     = lightning_sanitize_number( $new_instance['col_sm'] );
		$instance['col_md']                     = lightning_sanitize_number( $new_instance['col_md'] );
		$instance['col_lg']                     = lightning_sanitize_number( $new_instance['col_lg'] );
		$instance['col_xl']                     = lightning_sanitize_number( $new_instance['col_xl'] );
		$instance['new_date']                   = ! empty( $new_instance['new_date'] ) ? mb_convert_kana( $new_instance['new_date'], 'n' ) : 7;
		$instance['display_image']              = lightning_sanitize_checkbox( $new_instance['display_image'] );
		$instance['display_image_overlay_term'] = lightning_sanitize_checkbox( $new_instance['display_image_overlay_term'] );
		$instance['display_excerpt']            = lightning_sanitize_checkbox( $new_instance['display_excerpt'] );
		$instance['display_date']               = lightning_sanitize_checkbox( $new_instance['display_date'] );
		$instance['display_new']                = lightning_sanitize_checkbox( $new_instance['display_new'] );
		$instance['display_btn']                = lightning_sanitize_checkbox( $new_instance['display_btn'] );
		$instance['btn_text']                   = wp_kses_post( $new_instance['btn_text'] );
		$instance['btn_align']                  = wp_kses_post( $new_instance['btn_align'] );
		$instance['new_text']                   = wp_kses_post( $new_instance['new_text'] );
		$instance['count']                      = $new_instance['count'];
		$instance['title']                      = esc_attr( $new_instance['title'] );
		$instance['post_type']                  = $new_instance['post_type'];
		$instance['terms']                      = preg_replace( '/([^0-9,]+)/', '', $new_instance['terms'] );
		return $instance;
	}
}
add_action( 'widgets_init', 'widget_register_media_posts_bs4' );
if ( ! function_exists( 'widget_register_media_posts_bs4' ) ) {
	function widget_register_media_posts_bs4() {
		return register_widget( 'WP_Widget_Media_Posts_BS4' );
	}
}
