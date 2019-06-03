<?php
if ( ! class_exists( 'Ltg_Media_Post_View' ) ) {

	class Ltg_Media_Post_View {

		/**
		 * [post_loop description]
		 *
		 * @param  [type] $layout   [description]
		 * @param  [type] $instance [description]
		 * @return [type]           [description]
		 */
		public static function post_loop( $layout, $instance ) {
			$patterns = Lightning_media_posts::patterns();
			echo '<div class="' . $patterns[ $layout ]['class_outer'] . '">';
			if ( $layout == 'image_1st' ) {
				global $wp_query;
				$count = 1;
				/*
				1 左
				2 右
				3 右
				4 左 +
				5 左
				6 右
				7 左 +
				8 左
				9 右
				4 と 4に3の倍数を足した数の場合は改行
				*/
				while ( have_posts() ) :
					the_post();
					$media_post_class = ( $count == 1 ) ? ' image_card first' : ' image_card normal';

					if ( ( $count % 3 ) != 0 && $count != 2 ) {
						$media_post_class .= ' left';
					}
					if (
						$count == 4 ||
						( ( $count - 4 ) % 3 == 0 )
						) {
						$media_post_class .= ' clear';
					}
					Ltg_Media_Post_View::media_post_item( $media_post_class, $instance );
					$count++;
				endwhile;
			} else {
				while ( have_posts() ) :
					the_post();
					echo '<div class="' . $patterns[ $layout ]['class_post_outer'] . '">';
					Ltg_Media_Post_View::media_post_item( $patterns[ $layout ]['class_post_item'], $instance );
					echo '</div>';
				endwhile;
			}
			echo '</div>';
		}

		public static function get_media_post_item( $media_post_class, $instance = '' ) {
			global $post;
			$media_post_item  = '';
			$media_post_item .= '<article class="media_post media_post_base' . $media_post_class . '" id="post-' . get_the_ID() . '">' . "\n";
			$media_post_item .= '<a href="' . esc_url( get_the_permalink() ) . '">' . "\n";
			$thumbnail_id     = get_post_thumbnail_id( $post->ID );
			if ( $thumbnail_id ) {
				$thumbnail_src  = wp_get_attachment_image_src( $thumbnail_id, 'large' );
				$thumbnail_src  = $thumbnail_src[0];
				$class_no_image = '';
			} else {
				$thumbnail_src  = VK_MEDIA_POSTS_URL . 'images/no-image.png';
				$class_no_image = ' noimage';
			}
			$media_post_item .= '<div class="media_post_image' . $class_no_image . '" style="background-image:url(' . esc_url( $thumbnail_src ) . ');">' . "\n";
			$thumbnail        = get_the_post_thumbnail( $post->ID, 'media_thumbnail' );
			$media_post_item .= ( $thumbnail ) ? $thumbnail : '<img src="' . $thumbnail_src . '" alt="NO IMAGE" />';
			$media_post_item .= '</div>';
			// ※アーカイブページの場合はこのメソッドが呼び出される時点で instance に数字が入っているで、ここの数字を変更しても反映されない
			$days  = isset( $instance['new_icon_display'] ) ? $instance['new_icon_display'] : 7; // Newを表示させたい期間の日数
			$today = date_i18n( 'U' );
			$entry = get_the_time( 'U' );
			$kiji  = date( 'U', ( $today - $entry ) ) / 86400;
			if ( $days > $kiji ) {
				$media_post_item .= '<div class="media_post_label_new">NEW</div>';
			}

			$media_post_item .= '<div class="media_post_text">' . "\n";
			$media_post_item .= '<div class="media_post_meta">' . "\n";
			$media_post_item .= '<span class="published media_post_date">' . esc_html( get_the_date() ) . '</span>';
			$taxonomies       = get_the_taxonomies();
			if ( $taxonomies ) :
				// get $taxonomy name
				$taxonomy         = key( $taxonomies );
				$terms            = get_the_terms( get_the_ID(), $taxonomy );
				$term_name        = esc_html( $terms[0]->name );
				$term_color       = Vk_term_color::get_term_color( $terms[0]->term_id );
				$term_color       = ( $term_color ) ? ' style="background-color:' . $term_color . '"' : '';
				$media_post_item .= '<span class="media_post_term"' . $term_color . '>' . $term_name . '</span>';
			endif;
			$media_post_item .= '<span class="vcard author"><span class="fn">' . get_the_author() . '</span></span>';
			$media_post_item .= '</div>' . "\n"; // entry-meta

			$allowed_html     = array(
				'a'      => array(
					'href'   => array(),
					'class'  => array(),
					'target' => array(),
				),
				'span'   => array( 'class' => array() ),
				'b'      => array(),
				'br'     => array(),
				'strong' => array(),
			);
			$media_post_item .= '<h4 class="media_post_title">' . wp_kses( get_the_title(), $allowed_html ) . '</h4>';
			$media_post_item .= '<p class="media_post_excerpt">' . esc_html( get_the_excerpt() ) . '</p>';
			$media_post_item .= '</div>';
			$media_post_item .= '</a>';
			$media_post_item .= '</article>';
			return $media_post_item;
		}

		public static function media_post_item( $media_post_class, $instance = '' ) {
			// wp_kses_post は background-url の画像URLにパラメーターがついた時に削除されてしまうため、
			// 事前に他のタグで適時エスケープする
			echo Ltg_Media_Post_View::get_media_post_item( $media_post_class, $instance = '' );
		}

	} // class Ltg_Media_Post_View {

} // if ( ! class_exists( 'Ltg_Media_Post_View' ) ) {
