<?php
/**
 * VK Components Posts
 *
 * @package VK Component
 *
 * *********************** CAUTION ***********************
 * The original of this file is located at:
 * https://github.com/vektor-inc/vektor-wp-libraries
 * If you want to change this file, please change the original file.
 */

if ( ! class_exists( 'VK_Component_Posts' ) ) {

	/**
	 * VK Component for Posts
	 */
	class VK_Component_Posts {

		/***********************************************
		 * Basic method
		 * Common Parts
		 * Layout patterns
		 * UI Helper method
		 */

		/***********************************************
		 * Basic method
		 */

		/**
		 * Get Loop Post View Options
		 *
		 * @param array $options options array.
		 * @return array options
		 */
		public static function get_loop_post_view_options( $options ) {
			$default = array(
				'layout'                     => 'card',
				'display_image'              => true,
				'display_image_overlay_term' => true,
				'display_excerpt'            => false,
				'display_author'             => false,
				'display_date'               => true,
				'display_new'                => true,
				'display_taxonomies'         => false,
				'display_btn'                => false,
				'image_default_url'          => false,
				'overlay'                    => false,
				'btn_text'                   => __( 'Read more', 'vk_components_textdomain' ),
				'btn_align'                  => 'text-right',
				'new_text'                   => __( 'New!!', 'vk_components_textdomain' ),
				'new_date'                   => 7,
				'textlink'                   => true,
				'class_outer'                => '',
				'class_title'                => '',
				'body_prepend'               => '',
				'body_append'                => '',
			);
			$return  = apply_filters( 'vk_post_options', wp_parse_args( $options, $default ) );
			return $return;
		}

		/**
		 * Post View
		 *
		 * @param object $post global post object.
		 * @param array  $options component options.
		 *
		 * @return string $html
		 */
		public static function get_view( $post, $options ) {

			$options = self::get_loop_post_view_options( $options );

			if ( 'card-horizontal' === $options['layout'] ) {
				$html = self::get_view_type_card_horizontal( $post, $options );
			} elseif ( 'media' === $options['layout'] ) {
				$html = self::get_view_type_media( $post, $options );
			} elseif ( 'postListText' === $options['layout'] ) {
				$html = self::get_view_type_text( $post, $options );
			} else {
				$html = self::get_view_type_card( $post, $options );
			}
			return $html;
		}

		/**
		 * Display single view
		 *
		 * @param object $post post oject.
		 * @param array  $options display options.
		 * @return void
		 */
		public static function the_view( $post, $options ) {

			echo wp_kses_post( self::get_view( $post, $options ) );
		}

		/**
		 * Get post loop
		 *
		 * @param object $wp_query query object.
		 * @param array  $options display options.
		 * @param array  $options_loop loop options.
		 *
		 * @var [type]
		 */
		public static function get_loop( $wp_query, $options, $options_loop = array() ) {

			// Outer Post Type classes.
			$patterns                    = self::get_patterns();
			$loop_outer_class_post_types = array();
			if ( ! isset( $wp_query->query['post_type'] ) ) {
				$loop_outer_class_post_types[] = 'vk_posts-postType-post';
			} else {
				if ( is_array( $wp_query->query['post_type'] ) ) {
					foreach ( $wp_query->query['post_type'] as $key => $value ) {
						$loop_outer_class_post_types[] = 'vk_posts-postType-' . $value;
					}
				} else {
					$loop_outer_class_post_types[] = 'vk_posts-postType-' . $wp_query->query['post_type'];
				}
			}

			$loop_outer_class_post_types[] = 'vk_posts-layout-' . $options['layout'];

			// Additional loop option.
			$loop_outer_class = implode( ' ', $loop_outer_class_post_types );

			if ( ! empty( $options_loop['class_loop_outer'] ) ) {
				$loop_outer_class .= ' ' . $options_loop['class_loop_outer'];
			}

			// Set post item outer col class.
			if ( 'postListText' !== $options['layout'] ) {
				// If get info of column that deploy col to class annd add.
				if ( empty( $options['class_outer'] ) ) {
					$options['class_outer'] = self::get_col_size_classes( $options );
				} else {
					$options['class_outer'] .= ' ' . self::get_col_size_classes( $options );
				}
			}

			// Set hidden class.
			$hidden_class = array();
			if ( ! empty( $options['vkb_hidden'] ) ) {
				array_push( $hidden_class, 'vk_hidden' );
			} elseif ( ! empty( $options['vkb_hidden_xxl'] ) ) {
				array_push( $hidden_class, 'vk_hidden-xxl' );
			} elseif ( ! empty( $options['vkb_hidden_xl'] ) ) {
				array_push( $hidden_class, 'vk_hidden-xl' );
			} elseif ( ! empty( $options['vkb_hidden_lg'] ) ) {
				array_push( $hidden_class, 'vk_hidden-lg' );
			} elseif ( ! empty( $options['vkb_hidden_md'] ) ) {
				array_push( $hidden_class, 'vk_hidden-md' );
			} elseif ( ! empty( $options['vkb_hidden_sm'] ) ) {
				array_push( $hidden_class, 'vk_hidden-sm' );
			} elseif ( ! empty( $options['vkb_hidden_xs'] ) ) {
				array_push( $hidden_class, 'vk_hidden-xs' );
			}

			$loop = '';
			if ( $wp_query->have_posts() ) :

				$loop .= '<div class="vk_posts ' . esc_attr( $loop_outer_class ) . ' ' . esc_attr( implode( ' ', $hidden_class ) ) . '">';

				// for infeed Ads Customize.
				global $vk_posts_loop_item_count;
				$vk_posts_loop_item_count = 0;

				while ( $wp_query->have_posts() ) {

					$vk_posts_loop_item_count++;

					$wp_query->the_post();
					global $post;
					$loop .= self::get_view( $post, $options );

					$loop .= apply_filters( 'vk_posts_loop_item_after', '', $options );

				}

				$loop .= '</div>';

				if ( ! empty( $options_loop['display_pagination'] ) ) {
					$args = array();
					if ( ! empty( $options_loop['pagination_mid_size'] ) ) {
						$args['mid_size'] = $options_loop['pagination_mid_size'];
					}
					$loop .= self::get_pagenation( $wp_query, $args );
				}

			endif;

			/*
			Caution
			wp_reset_query() がないとトップページでショートコードなどから呼び出した場合に
			固定ページのトップ指定が解除されて投稿一覧が表示される
			→ と言いたい所だが、そもそも global $wp_query を上書きするなという話で、
			wp_reset_query()をするという事は余分に1回クエリが走る事になるので、
			$wp_query を上書きしないルールにしてここでは wp_reset_query() を走らせない
			*/
			wp_reset_postdata();
			return $loop;
		}

		/**
		 * Display loop
		 *
		 * @param object $wp_query query object.
		 * @param array  $options display options.
		 * @param array  $options_loop loop options.
		 */
		public static function the_loop( $wp_query, $options, $options_loop = array() ) {
			$allowed_html = self::vk_kses_post();
			echo wp_kses( self::get_loop( $wp_query, $options, $options_loop ), $allowed_html );
		}

		/**
		 * Pagenation
		 *
		 * @param object $wp_query : post query.
		 * @param array  $args : setting parametors.
		 * @return string $html
		 */
		public static function get_pagenation( $wp_query, $args = array() ) {

			$args = wp_parse_args(
				$args,
				array(
					'mid_size'           => 1,
					'prev_text'          => '&laquo;',
					'next_text'          => '&raquo;',
					'screen_reader_text' => __( 'Posts navigation' ),
					'aria_label'         => __( 'Posts' ),
					'class'              => 'pagination',
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'lightning' ) . ' </span>',
					'type'               => 'list',
				)
			);

			$showitems = ( $args['mid_size'] * 2 ) + 1;

			$html = '';

			global $paged;

			// 最後のページ.
			$max_num_pages = $wp_query->max_num_pages;
			if ( ! $max_num_pages ) {
				$max_num_pages = 1;
			}

			if ( 1 !== $max_num_pages ) {
				$html .= '<nav class="navigation ' . $args['class'] . '" role="navigation" aria-label="' . $args['aria_label'] . '">';
				$html .= '<h2 class="screen-reader-text">' . $args['screen_reader_text'] . '</h2>';
				$html .= '<div class="nav-links"><ul class="page-numbers">';

				// Prevリンク
				// 現在のページが２ページ目以降の場合.
				if ( $paged > 1 ) {
					$html .= '<li><a class="prev page-numbers" href="' . get_pagenum_link( $paged - 1 ) . '">' . $args['prev_text'] . '</a></li>';
				}

				// 今のページから mid_size を引いて2以上ある場合 && 最大表示アイテム数より最第ページ数が大きい場合
				// （ mid_size 数のすぐ次の場合は表示する）
				// 1...３４５.
				if ( $paged - $args['mid_size'] >= 2 && $max_num_pages > $showitems ) {
					$html .= '<li><a class="page-numbers" href="' . get_pagenum_link( 1 ) . '">1</a></li>';
				}
				// 今のページから mid_size を引いて3以上ある場合 && 最大表示アイテム数より最第ページ数が大きい場合.
				if ( $paged - $args['mid_size'] >= 3 && $max_num_pages > $showitems ) {
					$html .= '<li><span class="page-numbers dots">&hellip;</span></li>';
				}

				// mid_size より前に追加する数.
				$add_prev_count = $paged + $args['mid_size'] - $max_num_pages;
				// mid_size より後に追加する数.
				$add_next_count = -( $paged - 1 - $args['mid_size'] ); // 今のページ数を遡ってカウントするために-1.

				for ( $i = 1; $i <= $max_num_pages; $i++ ) {
					$html .= '<li>';
					// 表示するアイテム.
					if ( $paged === $i ) {
						$page_item = '<span aria-current="page" class="page-numbers current">' . $i . '</span>';
					} else {
						$page_item = '<a href="' . get_pagenum_link( $i ) . '" class="page-numbers">' . $i . '</a>';
					}

					// 今のページから mid_size を引いた数～今のページから mid_size を足した数まで || 最大ページ数が最大表示アイテム数以下の場合.
					if ( ( $paged - $args['mid_size'] <= $i && $i <= $paged + $args['mid_size'] ) || $max_num_pages <= $showitems ) {
						$html .= $page_item;
						// 今のページから mid_size を引くと負数になる場合 && 今のページ+ mid_size +負数を mid_size に加算した数まで.
					} elseif ( $paged - 1 - $args['mid_size'] < 0 && $paged + $args['mid_size'] + $add_next_count >= $i ) {
						$html .= $page_item;
						// 今のページから mid_size を足すと　最後のページよりも大きくなる場合 && 今のページ+ mid_size +負数を mid_size に加算した数まで.
					} elseif ( $paged + $args['mid_size'] > $max_num_pages && $paged - $args['mid_size'] - $add_prev_count <= $i ) {
						$html .= $page_item;
					}
					$html .= '</li>';
				}

				// 現在のページに mid_size を足しても最後のページ数より２以上小さい時 && 最大表示アイテム数より最第ページ数が大きい場合.
				if ( $paged + $args['mid_size'] <= $max_num_pages - 2 && $max_num_pages > $showitems ) {
					$html .= '<li><span class="page-numbers dots">&hellip;</span></li>';
				}
				if ( $paged + $args['mid_size'] <= $max_num_pages - 1 && $max_num_pages > $showitems ) {
					$html .= '<li><a href="' . get_pagenum_link( $max_num_pages ) . '">' . $max_num_pages . '</a></li>';
				}
				// Nextリンク.
				if ( $paged < $max_num_pages ) {
					$html .= '<li><a class="next page-numbers" href="' . get_pagenum_link( $paged + 1 ) . '">' . $args['next_text'] . '</a></li>';
				}
				$html .= '</ul>';
				$html .= '</div>';
				$html .= '</nav>';
			}
			return $html;
		}

		/**
		 * Kses Escape
		 *
		 * It's need for wp_kses_post escape ruby and rt that cope with ruby and rt.
		 *
		 * @return array $allowed_html
		 */
		public static function vk_kses_post() {
			$common_attr = array(
				'id'    => array(),
				'class' => array(),
				'role'  => array(),
				'style' => array(),
			);
			$tags        = array(
				'div',
				'section',
				'article',
				'header',
				'footer',
				'span',
				'nav',
				'h1',
				'h2',
				'h3',
				'h4',
				'h5',
				'h6',
				'button',
				'p',
				'i',
				'a',
				'b',
				'strong',
				'table',
				'thead',
				'tbody',
				'tfoot',
				'th',
				'tr',
				'td',
				'tr',
				'ol',
				'ul',
				'li',
				'dl',
				'dt',
				'dd',
				'img',
				'ruby',
				'rt',
			);
			foreach ( $tags as $tag ) {
				$allowed_html[ $tag ] = $common_attr;
			}
			$allowed_html['a']['href']    = array();
			$allowed_html['a']['target']  = array();
			$allowed_html['img']['src']   = array();
			$allowed_html['img']['sizes'] = array();
			$allowed_html['ruby']         = array();
			$allowed_html['rt']           = array();
			return $allowed_html;
		}


		/***********************************************
		 * Common Parts
		 */

		/**
		 * Common Part _ first DIV
		 *
		 * @param object $post post oject.
		 * @param array  $options display options.
		 *
		 * @return string
		 */
		public static function get_view_first_div( $post, $options ) {

			// Add layout Class.
			if ( 'card-horizontal' === $options['layout'] ) {
				$class_outer = 'card card-post card-horizontal';
			} elseif ( 'card-noborder' === $options['layout'] ) {
				$class_outer = 'card card-noborder';
			} elseif ( 'card-intext' === $options['layout'] ) {
				$class_outer = 'card card-intext';
			} elseif ( 'media' === $options['layout'] ) {
				$class_outer = 'media';
			} elseif ( 'postListText' === $options['layout'] ) {
				$class_outer = 'postListText';
			} else {
				$class_outer = 'card card-post';
			}

			// Add Outer class.
			if ( ! empty( $options['class_outer'] ) ) {
				$class_outer .= ' ' . esc_attr( $options['class_outer'] );
			}

			// Add btn class.
			if ( $options['display_btn'] && 'postListText' !== $options['layout'] ) {
				$class_outer .= ' vk_post-btn-display';
			}
			global $post;
			$html = '<div id="post-' . esc_attr( $post->ID ) . '" class="vk_post vk_post-postType-' . esc_attr( $post->post_type ) . ' ' . join( ' ', get_post_class( $class_outer ) ) . '">';
			return $html;
		}


		/**
		 * Common Part _ post thumbnail
		 *
		 * @param object $post global post object.
		 * @param array  $options component options.
		 * @param array  $attr   get_the_post_thumbnail() image attr.
		 *
		 * @return string
		 */
		public static function get_thumbnail_image( $post, $options, $attr = array() ) {

			$default = array(
				'class_outer' => '',
				'class_image' => '',
			);
			$classes = wp_parse_args( $attr, $default );

			$html = '';
			if ( $options['display_image'] ) {
				if ( $classes['class_outer'] ) {
					$classes['class_outer'] = ' ' . $classes['class_outer'];
				}

				$image_src = get_the_post_thumbnail_url( $post->ID, 'large' );
				if ( ! $image_src && $options['image_default_url'] ) {
					$image_src = esc_url( $options['image_default_url'] );
				}
				$style = ' style="background-image:url(' . $image_src . ')"';

				$html .= '<div class="vk_post_imgOuter' . $classes['class_outer'] . '"' . $style . '>';

				if ( 'card-intext' !== $options['layout'] ) {
					$html .= '<a href="' . get_the_permalink( $post->ID ) . '">';
				}

				if ( $options['overlay'] ) {
					$html .= '<div class="card-img-overlay">';
					$html .= $options['overlay'];
					$html .= '</div>';
				}

				if ( $options['display_image_overlay_term'] ) {

					$html     .= '<div class="card-img-overlay">';
					$term_args = array(
						'class' => 'vk_post_imgOuter_singleTermLabel',
					);
					if ( method_exists( 'Vk_term_color', 'get_single_term_with_color' ) ) {
						$html .= Vk_term_color::get_single_term_with_color( $post, $term_args );
					}
					$html .= '</div>';

				}
				if ( $classes['class_image'] ) {
					$image_class = 'vk_post_imgOuter_img ' . $classes['class_image'];
				} else {
					$image_class = 'vk_post_imgOuter_img';
				}

				$image_attr = array( 'class' => $image_class );
				$img        = get_the_post_thumbnail( $post->ID, 'medium', $image_attr );
				if ( $img ) {
					$html .= $img;
				} elseif ( $options['image_default_url'] ) {
					$html .= '<img src="' . esc_url( $options['image_default_url'] ) . '" alt="" class="' . $image_class . '" loading="lazy" />';
				}

				if ( 'card-intext' !== $options['layout'] ) {
					$html .= '</a>';
				}

				$html .= '</div><!-- [ /.vk_post_imgOuter ] -->';
			}

			return $html;
		}

		/**
		 * Common Part _ post body
		 *
		 * @param object $post global post object.
		 * @param array  $options component options.
		 */
		public static function get_view_body( $post, $options ) {

			$layout_type = $options['layout'];
			if ( 'card-horizontal' === $layout_type ||
				'card-noborder' === $layout_type ||
				'card-intext' === $layout_type
				) {
				$layout_type = 'card';
			}

			$html = '';

			$html .= '<div class="vk_post_body ' . $layout_type . '-body">';

			if ( ! empty( $options['body_prepend'] ) ) {
				$html .= $options['body_prepend'];
			}

			$html .= '<h5 class="vk_post_title ' . $layout_type . '-title">';

			/*
			カードインテキストの場合、リンクの中にリンクがあるとブラウザでDOMが書き換えられるので
			中のリンクを解除する必要がある。
			*/
			if ( 'card-intext' === $options['layout'] ) {
				$options['textlink'] = false;
			}

			if ( $options['textlink'] ) {
				$html .= '<a href="' . get_the_permalink( $post->ID ) . '">';
			}

			$html .= apply_filters( 'vk_post_title', get_the_title( $post->ID ), $post, $options );

			if ( $options['display_new'] ) {
				$today = date_i18n( 'U' );
				$entry = get_the_time( 'U', $post );
				$kiji  = gmdate( 'U', ( $today - $entry ) ) / 86400;
				if ( $options['new_date'] > $kiji ) {
					$html .= '<span class="vk_post_title_new">' . $options['new_text'] . '</span>';
				}
			}

			if ( $options['textlink'] ) {
				$html .= '</a>';
			}

			$html .= '</h5>';

			if ( $options['display_date'] ) {
				$html .= '<div class="vk_post_date ' . $layout_type . '-date published">';
				$html .= esc_html( get_the_date( '', $post->ID ) );
				$html .= '</div>';
			}

			if ( $options['display_excerpt'] ) {
				$html .= '<p class="vk_post_excerpt ' . $layout_type . '-text">';
				$html .= wp_kses_post( get_the_excerpt( $post->ID ) );
				$html .= '</p>';
			}

			if ( $options['display_author'] ) {
				$author = get_the_author();
				if ( $author ) {
					$html .= '<p class="vcard vk_post_author" itemprop="author">';

					// VK Post Author Display の画像を取得.
					$profile_image_id = get_the_author_meta( 'user_profile_image' );
					$html            .= '<span class="vk_post_author_image">';
					if ( $profile_image_id ) {
						$profile_image_src = wp_get_attachment_image_src( $profile_image_id, 'thumbnail' );
						// Gravater の時はクラス名つけられないので、こちらにもつけないこと.
						$html .= '<img src="' . $profile_image_src[0] . '" alt="' . esc_attr( $author ) . '" />';
					} else {
						$html .= get_avatar( get_the_author_meta( 'email' ), 100 );
					}
					$html .= '</span>';

					$html .= '<span class="fn vk_post_author_name" itemprop="name">';
					$html .= esc_html( $author );
					$html .= '</span></p>';
				} // if author
			}

			if ( $options['display_taxonomies'] ) {
				$args       = array(
					'template'      => '<dt class="vk_post_taxonomy_title"><span class="vk_post_taxonomy_title_inner">%s</span></dt><dd class="vk_post_taxonomy_terms">%l</dd>',
					'term_template' => '<a href="%1$s">%2$s</a>',
				);
				$taxonomies = get_the_taxonomies( $post->ID, $args );
				$exclusion  = array( 'product_type' );
				// このフィルター名は投稿詳細でも使っているので注意.
				$exclusion = apply_filters( 'vk_get_display_taxonomies_exclusion', $exclusion );

				if ( is_array( $exclusion ) ) {
					foreach ( $exclusion as $key => $value ) {
						unset( $taxonomies[ $value ] );
					}
				}
				if ( $taxonomies ) {
					$html .= '<div class="vk_post_taxonomies">';
					foreach ( $taxonomies as $key => $value ) {
						$html .= '<dl class="vk_post_taxonomy vk_post_taxonomy-' . $key . '">' . $value . '</dl>';
					} // foreach
					$html .= '</div>';
				}
			}

			if ( $options['textlink'] ) {

				if ( $options['display_btn'] ) {
					$button_options = array(
						'outer_id'       => '',
						'outer_class'    => '',
						'btn_text'       => $options['btn_text'],
						'btn_url'        => get_the_permalink( $post->ID ),
						'btn_class'      => 'btn btn-sm btn-primary vk_post_btn',
						'btn_target'     => '',
						'btn_ghost'      => false,
						'btn_color_text' => '',
						'btn_color_bg'   => '',
						'shadow_use'     => false,
						'shadow_color'   => '',
					);

					$html .= '<div class="vk_post_btnOuter ' . $options['btn_align'] . '">';
					$html .= VK_Component_Button::get_view( $button_options );
					$html .= '</div>';
				}
			}

			if ( ! empty( $options['body_append'] ) ) {
				$html .= $options['body_append'];
			}

			$html .= '</div><!-- [ /.' . $layout_type . '-body ] -->';

			return $html;
		}

		/***********************************************
		 * Layout patterns
		 */

		/**
		 * Get Pattern
		 *
		 * @return array $patterns Post Layout pattern array
		 */
		public static function get_patterns() {

			$patterns = array(
				'card'            => array(
					'label'             => __( 'Card', 'vk_components_textdomain' ),
					'class_posts_outer' => '',
				),
				'card-noborder'   => array(
					'label'             => __( 'Card Noborder', 'vk_components_textdomain' ),
					'class_posts_outer' => '',
				),
				'card-horizontal' => array(
					'label'             => __( 'Card Horizontal', 'vk_components_textdomain' ),
					'class_posts_outer' => '',
				),
				'media'           => array(
					'label'             => __( 'Media', 'vk_components_textdomain' ),
					'class_posts_outer' => 'media-outer',
				),
				'postListText'    => array(
					'label'             => _x( 'Text 1 Column', 'post list type', 'vk_components_textdomain' ),
					'class_posts_outer' => 'postListText-outer',
				),
			);
			return $patterns;
		}

		/**
		 * Card
		 *
		 * @param object $post global post object.
		 * @param array  $options component options.
		 */
		public static function get_view_type_card( $post, $options ) {
			$html  = '';
			$html .= self::get_view_first_div( $post, $options );

			$attr = array(
				'class_outer' => '',
				'class_image' => 'card-img-top',
			);

			$html_body  = '';
			$html_body .= self::get_thumbnail_image( $post, $options, $attr );
			$html_body .= self::get_view_body( $post, $options );

			if ( 'card-intext' === $options['layout'] ) {

				$html .= '<a href="' . esc_url( get_the_permalink( $post->ID ) ) . '" class="card-intext-inner">';

				// aタグ内にaタグがあるとChromeなどはその時点で一旦aタグを閉じてしまって表示が崩れるので、aタグをspanに変換する.
				$html_body = str_replace( '<a', '<span', $html_body );
				$html_body = str_replace( 'href=', 'data-url=', $html_body );
				$html_body = str_replace( 'a>', 'span>', $html_body );

				$html .= $html_body;

				$html .= '</a>';

			} else {
				$html .= $html_body;
			}

			$html .= '</div><!-- [ /.card ] -->';
			return $html;
		}

		/**
		 * Card horizontal
		 *
		 * @param object $post global post object.
		 * @param array  $options component options.
		 */
		public static function get_view_type_card_horizontal( $post, $options ) {
			$html  = '';
			$html .= self::get_view_first_div( $post, $options );
			$html .= '<div class="row no-gutters card-horizontal-inner-row">';

			if ( $options['display_image'] ) {
				$html .= '<div class="col-5 card-img-outer">';
				$attr  = array(
					'class_outer' => '',
					'class_image' => 'card-img card-img-use-bg',
				);
				$html .= self::get_thumbnail_image( $post, $options, $attr );
				$html .= '</div><!-- /.col -->';
				$html .= '<div class="col-7">';
			}

			$html .= self::get_view_body( $post, $options );

			if ( $options['display_image'] ) {
				$html .= '</div><!-- /.col -->';
			}

			$html .= '</div><!-- [ /.row ] -->';
			$html .= '</div><!-- [ /.card ] -->';
			return $html;
		}

		/**
		 * Media
		 *
		 * @param object $post global post object.
		 * @param array  $options component options.
		 */
		public static function get_view_type_media( $post, $options ) {
			$html  = '';
			$html .= self::get_view_first_div( $post, $options );
			if ( $options['display_image'] ) {
				$attr  = array(
					'class_outer' => 'media-img',
					'class_image' => '',
				);
				$html .= self::get_thumbnail_image( $post, $options, $attr );
			}

			$html .= self::get_view_body( $post, $options );

			$html .= '</div><!-- [ /.media ] -->';
			return $html;
		}

		/**
		 * Text
		 *
		 * @param object $post global post object.
		 * @param array  $options component options.
		 */
		public static function get_view_type_text( $post, $options ) {

			$layout_type = $options['layout'];

			$html  = '';
			$html .= self::get_view_first_div( $post, $options );

			if ( $options['display_date'] ) {
				$html .= '<span class="postListText_date published">';
				$html .= esc_html( get_the_date( '', $post->ID ) );
				$html .= '</span>';
			}

			if ( $options['display_image_overlay_term'] ) {
				$html     .= '<span class="postListText_singleTermLabel">';
				$term_args = array(
					'class' => 'postListText_singleTermLabel_inner',
					'link'  => true,
				);
				if ( method_exists( 'Vk_term_color', 'get_single_term_with_color' ) ) {
					$html .= Vk_term_color::get_single_term_with_color( $post, $term_args );
				}
				$html .= '</span>';
			}

			$html .= '<p class="postListText_title"><a href="' . get_the_permalink( $post->ID ) . '">';
			$html .= get_the_title( $post->ID );
			$html .= '</a>';

			if ( $options['display_new'] ) {
				$today = date_i18n( 'U' );
				$entry = get_the_time( 'U' );
				$kiji  = gmdate( 'U', ( $today - $entry ) ) / 86400;
				if ( $options['new_date'] > $kiji ) {
					$html .= '<span class="vk_post_title_new">' . $options['new_text'] . '</span>';
				}
			}

			$html .= '</p>';

			$html .= '</div>';
			return $html;
		}

		/***********************************************
		 * UI Helper method
		 */

		/**
		 * Convert col-count from inputed column count.
		 *
		 * @param  integer $input_col user inputed col number.
		 * @return string             grid col number
		 */
		public static function get_col_converted_size( $input_col = 4 ) {
			$input_col = strval( $input_col );
			if ( '1' === $input_col ) {
				$col = 12;
			} elseif ( '2' === $input_col ) {
				$col = 6;
			} elseif ( '3' === $input_col ) {
				$col = 4;
			} elseif ( '4' === $input_col ) {
				$col = 3;
			} elseif ( '6' === $input_col ) {
				$col = 2;
			} else {
				$col = 4;
			}
			return strval( $col );
		}

		/**
		 * Get all size col classes
		 *
		 * @param array $attributes inputed col numbers array.
		 * @return string $col_class  class names
		 */
		public static function get_col_size_classes( $attributes ) {
			$col_class_array = array();
			$sizes           = array( 'xs', 'sm', 'md', 'lg', 'xl', 'xxl' );
			foreach ( $sizes as $key => $size ) {
				if ( ! empty( $attributes[ 'col_' . $size ] ) ) {
					$col_class_array[] = 'vk_post-col-' . $size . '-' . self::get_col_converted_size( $attributes[ 'col_' . $size ] );
				}
			}
			$col_class = implode( ' ', $col_class_array );
			return $col_class;
		}

	}
}
