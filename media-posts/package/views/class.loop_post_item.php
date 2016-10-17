<?php
if ( ! class_exists( 'Ltg_Media_Post_Item' ) ) {

    class Ltg_Media_Post_Item {

		public static function media_post( $media_post_class, $instance ){
			global $post;
			echo '<article class="media_post media_post_base'.$media_post_class.'" id="post-'.get_the_ID().'">'."\n";
			echo '<a href="'.esc_url( get_the_permalink() ).'">'."\n";
			echo '<div class="media_post_image">'."\n";
			$thumbnail = get_the_post_thumbnail( $post->ID,'media_thumbnail' );
			echo  ( $thumbnail ) ? $thumbnail : '<img src="'.LTG_MEDIA_POSTS_URL.'/images/no-image.png" alt="NO IMAGE" />';
			echo '</div>';

			$days  = isset( $instance['new_icon_display'] ) ? $instance['new_icon_display'] : 7 ; //Newを表示させたい期間の日数
			$today = date_i18n('U');
			$entry = get_the_time('U');
			$kiji  = date('U',($today - $entry)) / 86400 ;
			if( $days > $kiji ){
				echo '<div class="media_post_label_new">NEW</div>';
			}

			echo '<div class="media_post_text">'."\n";
			echo '<div class="media_post_meta">'."\n";
			echo '<span class="published media_post_date">'.esc_html( get_the_date() ).'</span>';
			$taxonomies = get_the_taxonomies();
			if ($taxonomies):
				// get $taxonomy name
				$taxonomy = key( $taxonomies );
				$terms  = get_the_terms( get_the_ID(),$taxonomy );
				$term_name	= esc_html($terms[0]->name);
				$term_color = Vk_term_color::get_term_color( $terms[0]->term_id );
				$term_color = ( $term_color ) ? ' style="background-color:'.$term_color.'"': '';
				echo '<span class="media_post_term"'.$term_color.'>'.$term_name.'</span>';
			endif;
			echo '<span class="vcard author"><span class="fn">'.get_the_author().'</span></span>';
			echo '</div>'."\n"; // entry-meta
			echo '<h4 class="media_post_title">'.esc_html( get_the_title() ).'</h4>';
			echo '<p class="media_post_excerpt">'.esc_html( get_the_excerpt() ).'</p>';
			echo '</div>';
			echo '</a>';
			echo '</article>';
		}

    }

}