<?php
if ( ! class_exists( 'Ltg_Media_Post_View' ) ) {

	class Ltg_Media_Post_View {

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

		public static function media_post_item( $media_post_class, $instance = '' ) {
			global $post;
			echo '<article class="media_post media_post_base' . $media_post_class . '" id="post-' . get_the_ID() . '">' . "\n";
			echo '<a href="' . esc_url( get_the_permalink() ) . '">' . "\n";
			$thumbnail_id = get_post_thumbnail_id( $post->ID );
			if ( $thumbnail_id ) {
				$thumbnail_src  = wp_get_attachment_image_src( $thumbnail_id, 'large' );
				$thumbnail_src  = $thumbnail_src[0];
				$class_no_image = '';
			} else {
				$thumbnail_src  = LTG_MEDIA_POSTS_URL . '/images/no-image.png';
				$class_no_image = ' noimage';
			}
			echo '<div class="media_post_image' . $class_no_image . '" style="background-image:url(' . $thumbnail_src . ');">' . "\n";
			$thumbnail = get_the_post_thumbnail( $post->ID, 'media_thumbnail' );
			echo  ( $thumbnail ) ? $thumbnail : '<img src="' . $thumbnail_src . '" alt="NO IMAGE" />';
			echo '</div>';
			// ※アーカイブページの場合はこのメソッドが呼び出される時点で instance に数字が入っているで、ここの数字を変更しても反映されない
			$days  = isset( $instance['new_icon_display'] ) ? $instance['new_icon_display'] : 7; //Newを表示させたい期間の日数
			$today = date_i18n( 'U' );
			$entry = get_the_time( 'U' );
			$kiji  = date( 'U', ( $today - $entry ) ) / 86400;
			if ( $days > $kiji ) {
				echo '<div class="media_post_label_new">NEW</div>';
			}

			echo '<div class="media_post_text">' . "\n";
			echo '<div class="media_post_meta">' . "\n";
			echo '<span class="published media_post_date">' . esc_html( get_the_date() ) . '</span>';
			$taxonomies = get_the_taxonomies();
			if ( $taxonomies ) :
				// get $taxonomy name
				$taxonomy   = key( $taxonomies );
				$terms      = get_the_terms( get_the_ID(), $taxonomy );
				$term_name  = esc_html( $terms[0]->name );
				$term_color = Vk_term_color::get_term_color( $terms[0]->term_id );
				$term_color = ( $term_color ) ? ' style="background-color:' . $term_color . '"' : '';
				echo '<span class="media_post_term"' . $term_color . '>' . $term_name . '</span>';
			endif;
			echo '<span class="vcard author"><span class="fn">' . get_the_author() . '</span></span>';
			echo '</div>' . "\n"; // entry-meta
			echo '<h4 class="media_post_title">' . esc_html( get_the_title() ) . '</h4>';
			echo '<p class="media_post_excerpt">' . esc_html( get_the_excerpt() ) . '</p>';
			echo '</div>';
			echo '</a>';
			echo '</article>';
		}

	}

}
