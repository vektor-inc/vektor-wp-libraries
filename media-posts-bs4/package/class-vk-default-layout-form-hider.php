<?php
/**
 * VK Default Layout Form Hider
 *
 * @package VK Media Posts BS4
 */

/**
 * VK Default Layout Form Hider
 */
class VK_Default_Layout_Form_Hider extends WP_Customize_Control {
	/**
	 * Rendering Content
	 */
	public function render_content() {

		// アーカイブページのある投稿タイプを取得.
		$post_types           = array( 'post' => 0 );
		$post_types           = VK_Media_Posts_BS4::get_custom_types() + $post_types;
		$post_types['author'] = 'author';

		// ページを開いた状態のoption値を取得.
		$option = get_option( 'vk_post_type_archive' );

		// 投稿タイプでループ.
		foreach ( $post_types as $post_type => $value ) {
			?>
			<script type="text/javascript">
				;
				(function($) {

				// 投稿タイプslugのついたセレクタ.
				var target_select = '#customize-control-vk_post_type_archive-<?php echo esc_html( $post_type ); ?>-layout select';

				// カラム項目.
				var hidden_item_col = ['-col_xs', '-col_sm', '-col_md', '-col_lg', '-col_xl', '-col_xxl'];

				// その他の非表示要素（テキスト）.
				var hidden_item_element_text = ['-display_image', '-display_excerpt', '-display_btn','-new_mark_title', '-btn_setting_title','-btn_text','-btn_align'];

				// その他の非表示要素.
				var hidden_item_element_all = ['-display_item_title', '-display_image','-display_image_overlay_term', '-display_excerpt', '-display_date', '-display_new', '-display_btn','-new_mark_title', '-new_date', '-new_text','-btn_setting_title','-btn_text','-btn_align'];

				function setOptionHidden( value, control_target_array ){
					control_target_array.forEach(function( value ) {
						// デフォルトの場合に設定項目を非表示に.
						$('#customize-control-vk_post_type_archive-<?php echo esc_html( $post_type ); ?>' + value).css({"display":"none"});
					});
				}
				function setOptionDisplay( value, control_target_array ){
					control_target_array.forEach(function( value ) {
						// デフォルトの場合に設定項目を非表示に.
						$('#customize-control-vk_post_type_archive-<?php echo esc_html( $post_type ); ?>' + value).css({"display":"list-item"});
					});
				}

				<?php
				// ページを開いた時点で 標準 が選択してあった場合 不要項目を非表示に.
				if ( empty( $option[ $post_type ]['layout'] ) || 'default' === $option[ $post_type ]['layout'] ) {
					?>
				$(document).ready(function() {
					setOptionHidden( 'default', hidden_item_col );
					setOptionHidden( 'default', hidden_item_element_all );
				});
				<?php } ?>

				<?php
				// ページを開いた時点で テキスト が選択してあった場合 不要項目を非表示に.
				if ( ! empty( $option[ $post_type ]['layout'] ) && 'postListText' === $option[ $post_type ]['layout'] ) {
					?>
				$(document).ready(function() {
					// Display all item.
					setOptionDisplay( 'postListText', hidden_item_element_all );
					// Hide item.
					setOptionHidden( 'postListText', hidden_item_col );
					setOptionHidden( 'postListText', hidden_item_element_text );
				});
				<?php } ?>

				// レイアウトが変更された時.
				$(target_select).change(function() {
					var val = $(this).val();
					// レイアウト選択が 標準 になったら.
					if ( val === 'default' ){
						setOptionHidden( val, hidden_item_col );
						setOptionHidden( val, hidden_item_element_all );
					} else if ( val === 'postListText' ){
						// Display all item.
						setOptionDisplay( val, hidden_item_element_all );
						// Hide item.
						setOptionHidden( val, hidden_item_col );
						setOptionHidden( val, hidden_item_element_text );
					// レイアウト選択が 標準 以外の時
					} else {
						setOptionDisplay( val, hidden_item_col );
						setOptionDisplay( val, hidden_item_element_all );
					}

				});
				})(jQuery);
			</script>
			<?php
		}
	}
}
