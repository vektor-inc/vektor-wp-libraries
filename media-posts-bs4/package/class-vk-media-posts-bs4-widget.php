<?php
/**
 * VK Media Posts BS4 Widget
 *
 * @package VK_Media_Posts_BS4_Widget
 */

/**
 * VK Media Posts BS4 Widget
 */
class VK_Media_Posts_BS4_Widget extends WP_Widget {
	/**
	 * Taxonomies
	 *
	 * @var $taxonomies
	 */
	public $taxonomies = array( 'category' );

	/**
	 * Constructor
	 */
	public function __construct() {
		global $vk_media_post_prefix;

		$widget_name = $vk_media_post_prefix . __( 'Media Posts BS4', 'media-posts-bs4-textdomain' );
		parent::__construct(
			'media_posts_bs4',
			$widget_name,
			array( 'description' => __( 'It is a widget that displays the post list. Various shapes can be selected.', 'media-posts-bs4-textdomain' ) )
		);
	}

	/**
	 * Widget Title
	 *
	 * @param array $instance widget instance.
	 */
	public static function widget_title( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} elseif ( isset( $instance['label'] ) ) {
			$title = $instance['label'];
		} else {
			$title = __( 'Recent Posts', 'media-posts-bs4-textdomain' );
		}
		return $title;
	}

	/*
	Get Taxonomy?
	public function _taxonomy_init( $post_type ) {
		if ( $post_type == 'post' ) {
			return;
		}
		$this->taxonomies = get_object_taxonomies( $post_type );
	}
	*/

	/**
	 * Taxonomy List
	 *
	 * @param int    $post_id post id.
	 * @param string $before 前に入れる文字列.
	 * @param string $sep タームを区切る文字列.
	 * @param string $after 後に続く文字列.
	 */
	public function taxonomy_list( $post_id = 0, $before = ' ', $sep = ',', $after = '' ) {
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

	/**
	 * デフォルトオプション
	 */
	public function options_default() {
		$default_option = array(
			'orderby'                    => 'date',
			'orderby'                    => 'DESC',
			'count'                      => 6,
			'offset'                     => '',
			'title'                      => __( 'Recent Posts', 'media-posts-bs4-textdomain' ),
			'post_type'                  => array( 'post' => 1 ), // クエリに投げる形式は違うので要変換.
			'terms'                      => '',
			'layout'                     => 'media',
			'col_xs'                     => 1,
			'col_sm'                     => 1,
			'col_md'                     => 1,
			'col_lg'                     => 1,
			'col_xl'                     => 1,
			'col_xxl'                    => 1,
			'display_image'              => true,
			'display_image_overlay_term' => true,
			'display_excerpt'            => false,
			'display_date'               => true,
			'display_new'                => true,
			'display_taxonomies'         => false,
			'new_date'                   => 7,
			'new_text'                   => 'New!!',
			'btn_text'                   => __( 'Read more', 'media-posts-bs4-textdomain' ),
			'btn_align'                  => 'text-right',
		);
		$default_option = apply_filters( 'vk_media_posts_bs4_widget_default_options', $default_option );
		return $default_option;
	}


	/**
	 * Widget
	 *
	 * @param array $args options of Widget Area.
	 * @param array $instance options of Widget.
	 */
	public function widget( $args, $instance ) {

		$defaults = self::options_default();
		$instance = wp_parse_args( $instance, $defaults );

		if ( ! $instance['post_type'] ) {
			$instance['post_type'] = array( 'post' => 1 );
		}

		$title = self::widget_title( $instance );

		echo wp_kses_post( $args['before_widget'] );

		if ( $title ) {
			echo wp_kses_post( $args['before_title'] );
			echo wp_kses_post( $title );
			echo wp_kses_post( $args['after_title'] );
		}

		// 入力された投稿タイプ配列をクエリに投げる形式の配列に変換.
		$throw_query_post_types = array();
		if ( ! $instance['post_type'] ) {
			return;
		}
		$outer_class_post_types = '';
		foreach ( $instance['post_type'] as $key => $value ) {
			if ( $value ) {
				$throw_query_post_types[] = $key;
			}
			$outer_class_post_types .= 'vk_posts-postType-' . $key . ' ';
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

		if ( ! empty( $instance['offset'] ) ) {
			$q_args['offset'] = mb_convert_kana( $instance['offset'], 'n' );
		}
		if ( ! empty( $instance['order'] ) ) {
			$q_args['order'] = $instance['order'];
		}
		if ( ! empty( $instance['orderby'] ) ) {
			$q_args['orderby'] = $instance['orderby'];
		}

		global $media_posts_query;
		$media_posts_query            = new WP_Query( $q_args );
		$options                      = $instance;
		$options['image_default_url'] = VK_MEDIA_POSTS_BS4_URL . '/images/no-image.png';
		VK_Component_Posts::the_loop( $media_posts_query, $options );

		wp_reset_postdata();
		/* wp_reset_query() でリセットしないと $media_posts_query がリセットされないので誤動作を引き起こす */
		wp_reset_query();

		echo wp_kses_post( $args['after_widget'] );

	} // widget($args, $instance)


	/**
	 * Form
	 *
	 * @param array $instance Widget options.
	 */
	public function form( $instance ) {

		$defaults = self::options_default();
		$instance = wp_parse_args( (array) $instance, $defaults );

		// Form _ タイトル.
		?>
		<br />
		<label>
			<?php esc_html_e( 'Title:' ); ?><br/>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="admin-custom-input" />
		</label>

		<?php
		// Form _ 表示条件.
		?>

		<div class="admin-custom-section">
			<h2 class="admin-custom-h2"><?php esc_html_e( 'Display conditions', 'media-posts-bs4-textdomain' ); ?></h2>

			<h3 class="admin-custom-h3"><?php esc_html_e( 'Display post types', 'media-posts-bs4-textdomain' ); ?></h3>
			<?php
			// Post Type Check Box.
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

				if ( ! in_array( $key, $args['exclude_post_types'], true ) ) {

					$checked = ( isset( $args['checked'][ $key ] ) && 'true' === $args['checked'][ $key ] ) ? ' checked' : '';

					if ( $args['id'] ) {
						$id = ' id="' . esc_attr( $args['id'] ) . '"';
					} elseif ( $args['name'] ) {
						$id = ' id="' . esc_attr( $args['name'] ) . '"';
					} else {
						$id = '';
					}

					echo '<li><label>';
					echo '<input type="checkbox" name="' . esc_attr( $args['name'] ) . '[' . esc_attr( $key ) . ']"' . esc_attr( $id ) . ' value="true"' . esc_attr( $checked ) . ' />' . esc_html( $value->label );
					echo '</label></li>';
				}
			}
			echo '</ul>';
			?>

			<h3 class="admin-custom-h3">
				<label for="<?php echo esc_attr( $this->get_field_id( 'terms' ) ); ?>"><?php esc_html_e( 'Category(Term) ID', 'media-posts-bs4-textdomain' ); ?></label>
			</h3>

			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'terms' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'terms' ) ); ?>" value="<?php echo esc_attr( $instance['terms'] ); ?>" class="admin-custom-input" />
			<br />
			<?php
			esc_html_e( 'If you need filtering by category(term), add the category ID separate by ",".', 'media-posts-bs4-textdomain' );
			echo '<br/>';
			esc_html_e( 'If empty this area, I will do not filtering.', 'media-posts-bs4-textdomain' );
			echo '<br/><br/>';
			?>

			<?php
			// Form _ 表示件数.
			?>
			<h3 class="admin-custom-h3">
				<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Display count', 'media-posts-bs4-textdomain' ); ?></label>
			</h3>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" value="<?php echo esc_attr( $instance['count'] ); ?>" class="admin-custom-input" />

			<?php
			// Form _ オフセット.
			?>
			<h3 class="admin-custom-h3">
				<label for="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>"><?php esc_html_e( 'Offset count', 'media-posts-bs4-textdomain' ); ?></label>
			</h3>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'offset' ) ); ?>" value="<?php echo esc_attr( $instance['offset'] ); ?>" class="admin-custom-input" />

		</div>

		<?php
		// 表示形式とカラム.
		?>

		<div class="admin-custom-section">

			<h2 class="admin-custom-h2"><?php esc_html_e( 'Display type and columns', 'media-posts-bs4-textdomain' ); ?></h2>

			<h3 class="admin-custom-h3">
				<?php echo esc_html_e( 'Display type', 'media-posts-bs4-textdomain' ); ?>
			</h3>

			<?php
			/*
			Cope with old vk-component version at VK Blocks Pro
			$patterns = VK_Component_Posts::get_patterns();
			*/
			$patterns = VK_Media_Posts_BS4::patterns();
			?>

			<select id="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>" class="admin-custom-input">
				<?php

				foreach ( $patterns as $key => $value ) {
					if ( $instance['layout'] === $key ) {
						$selected = ' selected="selected"';
					} else {
						$selected = '';
					}

					echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $selected ) . '>' . esc_html( $value['label'] ) . '</option>';
				}
				?>
			</select>


			<h3 class="admin-custom-h3">
				<?php esc_html_e( 'Columns', 'media-posts-bs4-textdomain' ); ?>
			</h3>

			<p><?php esc_html_e( 'Please input column count to under the range of 1 to 4.', 'media-posts-bs4-textdomain' ); ?></p>

			<?php

			$sizes = array(
				'xs'  => array( 'label' => __( 'Extra small', 'media-posts-bs4-textdomain' ) ),
				'sm'  => array( 'label' => __( 'Small', 'media-posts-bs4-textdomain' ) ),
				'md'  => array( 'label' => __( 'Medium', 'media-posts-bs4-textdomain' ) ),
				'lg'  => array( 'label' => __( 'Large', 'media-posts-bs4-textdomain' ) ),
				'xl'  => array( 'label' => __( 'Extra large', 'media-posts-bs4-textdomain' ) ),
				'xxl' => array( 'label' => __( 'XX large', 'media-posts-bs4-textdomain' ) ),
			);
			$sizes = apply_filters( 'vk_media_post_bs4_size', $sizes );

			foreach ( $sizes as $key => $value ) {
				$field = 'col_' . $key;
				// translators: column of each screnn size of xs, sm, md, lg, xl and xxl.
				echo '<label for="' . esc_attr( $this->get_field_id( $field ) ) . '">' . esc_html( sprintf( __( 'Column ( Screen size : %s )', 'media-posts-bs4-textdomain' ), $value['label'] ) ) . '</label>';
				echo '<input type="number" max="4" min="1" id="' . esc_attr( $this->get_field_id( $field ) ) . '" name="' . esc_attr( $this->get_field_name( $field ) ) . '" value="' . esc_attr( $instance[ $field ] ) . '" class="admin-custom-input" />';
			}

			?>

		</div>

		<?php
		// 並び順.
		?>
		<div class="admin-custom-section">
			<h2 class="admin-custom-h2"><?php esc_html_e( 'Order Option', 'media-posts-bs4-textdomain' ); ?></h2>
			<h3 class="admin-custom-h3"><?php esc_html_e( 'Order by', 'media-posts-bs4-textdomain' ); ?></h3>
			<select id="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>" class="admin-custom-input">
				<?php
				$orderby_patterns = array(
					'date'     => __( 'Published Day', 'media-posts-bs4-textdomain' ),
					'modefied' => __( 'Modefied Day', 'media-posts-bs4-textdomain' ),
					'title'    => __( 'Title', 'media-posts-bs4-textdomain' ),
					'rand'     => __( 'Random', 'media-posts-bs4-textdomain' ),
				);

				foreach ( $orderby_patterns as $key => $value ) {
					if ( $instance['orderby'] === $key ) {
						$selected = ' selected="selected"';
					} else {
						$selected = '';
					}

					echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $selected ) . '>' . esc_html( $value ) . '</option>';
				}
				?>
			</select>

			<h3 class="admin-custom-h3"><?php esc_html_e( 'Order', 'media-posts-bs4-textdomain' ); ?></h3>
			<select id="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>" class="admin-custom-input">
				<?php
				$order_patterns = array(
					'DESC' => __( 'DESC', 'media-posts-bs4-textdomain' ),
					'ASC'  => __( 'ASC', 'media-posts-bs4-textdomain' ),
				);

				foreach ( $order_patterns as $key => $value ) {
					if ( $instance['order'] === $key ) {
						$selected = ' selected="selected"';
					} else {
						$selected = '';
					}

					echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $selected ) . '>' . esc_html( $value ) . '</option>';
				}
				?>
			</select>

		</div>

		<?php
		// 表示アイテム.
		?>

		<div class="admin-custom-section">

			<h2 class="admin-custom-h2"><?php esc_html_e( 'Display item', 'media-posts-bs4-textdomain' ); ?></h2>

			<?php
			$items = array(
				'display_image'              => __( 'Image', 'media-posts-bs4-textdomain' ),
				'display_image_overlay_term' => __( 'Term name', 'media-posts-bs4-textdomain' ),
				'display_excerpt'            => __( 'Excerpt', 'media-posts-bs4-textdomain' ),
				'display_date'               => __( 'Date', 'media-posts-bs4-textdomain' ),
				'display_new'                => __( 'New mark', 'media-posts-bs4-textdomain' ),
				'display_taxonomies'         => __( 'Taxonomies', 'media-posts-bs4-textdomain' ),
				'display_btn'                => __( 'Button', 'media-posts-bs4-textdomain' ),
			);
			foreach ( $items as $key => $value ) {
				$checked = ( isset( $instance[ $key ] ) && $instance[ $key ] ) ? ' checked' : '';
				echo '<p>';
				echo '<input type="checkbox" value="true" id="' . esc_attr( $this->get_field_id( '' . $key ) ) . '" name="' . esc_attr( $this->get_field_name( '' . $key ) ) . '"' . esc_attr( $checked ) . ' />';
				echo '<label for="' . esc_attr( $this->get_field_id( '' . $key ) ) . '">' . esc_html( $value ) . '</label>';
				echo '</p>';
			}
			?>


			<h3 class="admin-custom-h3"><?php esc_html_e( 'New mark option', 'media-posts-bs4-textdomain' ); ?></h3>

			<?php
			// NEWアイコン表示期間.
			$new_date = ( isset( $instance['new_date'] ) ) ? $instance['new_date'] : 7;
			?>
			<label for="<?php echo esc_attr( $this->get_field_id( 'new_date' ) ); ?>">
				<?php esc_html_e( 'Number of days to display the new post mark', 'media-posts-bs4-textdomain' ); ?>
			</label>

			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'new_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'new_date' ) ); ?>" value="<?php echo esc_attr( $instance['new_date'] ); ?>" class="admin-custom-input" />

			<label for="<?php echo esc_attr( $this->get_field_id( 'new_text' ) ); ?>">
				<?php esc_html_e( 'New mark text', 'media-posts-bs4-textdomain' ); ?>
			</label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'new_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'new_text' ) ); ?>" value="<?php echo esc_attr( $instance['new_text'] ); ?>" class="admin-custom-input" />


			<h3 class="admin-custom-h3">
					<?php esc_html_e( 'Button option', 'media-posts-bs4-textdomain' ); ?>
			</h3>

			<label for="<?php echo esc_attr( $this->get_field_id( 'btn_text' ) ); ?>">
				<?php esc_html_e( 'Button text', 'media-posts-bs4-textdomain' ); ?>
			</label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'btn_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'btn_text' ) ); ?>" value="<?php echo esc_attr( $instance['btn_text'] ); ?>" class="admin-custom-input" />

			<label for="<?php echo esc_attr( $this->get_field_id( 'btn_align' ) ); ?>">
				<?php esc_html_e( 'Button align', 'media-posts-bs4-textdomain' ); ?>
			</label>
			<select id="<?php echo esc_attr( $this->get_field_name( 'btn_align' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'btn_align' ) ); ?>" class="admin-custom-input">
				<?php
				$btn_aligns = array(
					'text-left'   => array(
						'label' => __( 'Left', 'media-posts-bs4-textdomain' ),
					),
					'text-center' => array(
						'label' => __( 'Center', 'media-posts-bs4-textdomain' ),
					),
					'text-right'  => array(
						'label' => __( 'Right', 'media-posts-bs4-textdomain' ),
					),
				);
				foreach ( $btn_aligns as $key => $value ) {
					if ( $instance['btn_align'] === $key ) {
						$selected = ' selected="selected"';
					} else {
						$selected;
					}

					echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $selected ) . '>' . esc_html( $value['label'] ) . '</option>';
				}
				?>
			</select>

		</div>
		<?php
	}

	/**
	 * Update
	 *
	 * @param array $new_instance Widget New Option.
	 * @param array $instance Widget Option.
	 */
	public function update( $new_instance, $instance ) {
		$instance                               = $new_instance;
		$instance['order']                      = $new_instance['order'];
		$instance['orderby']                    = $new_instance['orderby'];
		$instance['layout']                     = $new_instance['layout'];
		$instance['col_xs']                     = VK_Helpers::sanitize_number( $new_instance['col_xs'] );
		$instance['col_sm']                     = VK_Helpers::sanitize_number( $new_instance['col_sm'] );
		$instance['col_md']                     = VK_Helpers::sanitize_number( $new_instance['col_md'] );
		$instance['col_lg']                     = VK_Helpers::sanitize_number( $new_instance['col_lg'] );
		$instance['col_xl']                     = VK_Helpers::sanitize_number( $new_instance['col_xl'] );
		$instance['col_xxl']                    = VK_Helpers::sanitize_number( $new_instance['col_xxl'] );
		$instance['new_date']                   = ! empty( $new_instance['new_date'] ) ? mb_convert_kana( $new_instance['new_date'], 'n' ) : 7;
		$instance['display_image']              = VK_Helpers::sanitize_checkbox( $new_instance['display_image'] );
		$instance['display_image_overlay_term'] = VK_Helpers::sanitize_checkbox( $new_instance['display_image_overlay_term'] );
		$instance['display_excerpt']            = VK_Helpers::sanitize_checkbox( $new_instance['display_excerpt'] );
		$instance['display_date']               = VK_Helpers::sanitize_checkbox( $new_instance['display_date'] );
		$instance['display_new']                = VK_Helpers::sanitize_checkbox( $new_instance['display_new'] );
		$instance['display_btn']                = VK_Helpers::sanitize_checkbox( $new_instance['display_btn'] );
		$instance['btn_text']                   = wp_kses_post( $new_instance['btn_text'] );
		$instance['btn_align']                  = wp_kses_post( $new_instance['btn_align'] );
		$instance['new_text']                   = wp_kses_post( $new_instance['new_text'] );
		$instance['count']                      = $new_instance['count'];
		$instance['offset']                     = $new_instance['offset'];
		$instance['title']                      = esc_attr( $new_instance['title'] );
		$instance['post_type']                  = $new_instance['post_type'];
		$instance['terms']                      = preg_replace( '/([^0-9,]+)/', '', $new_instance['terms'] );
		return $instance;
	}
}
