<div class="fileedit-sub"></div>
<?php 
echo $mess;
global $vk_ltg_media_posts_textdomain; ?>
<div id="welcome-panel" class="message_intro">
	<p><?php _e( '各投稿タイプのアーカイブページもレイアウトを変更する事ができます。', $vk_ltg_media_posts_textdomain ); ?></p>
</div>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
	<?php wp_nonce_field( 'lightning-media-unit-submit', 'lightning-media-unit-nonce'); ?>
	<div class="sectionBox">
					<table>
						<?php
						$ltg_media_unit_archive_loop_layout = get_option('ltg_media_unit_archive_loop_layout');
						foreach ( $post_types as $type => $value ) {
							$selected = isset( $ltg_media_unit_archive_loop_layout[$type] ) ? $ltg_media_unit_archive_loop_layout[$type] : 'default';
							echo '<tr><th class="text-left">'.$post_types_label[$type].'</th><td>';
							echo '<select name="ltg_media_unit_archive_loop_layout['.$type.']" id="ltg_media_unit_archive_loop_layout_'.$type.'">';
							Lightning_media_posts::patterns_select_options( $selected );
							echo '</select></td></tr>'."\n";
						}

						echo '<tr><th>'.__( 'Author Archive', $vk_ltg_media_posts_textdomain ).'</th><td>';
						echo '<select name="ltg_media_unit_archive_loop_layout[author]" id="ltg_media_unit_archive_loop_layout_author">';
						$selected = isset( $ltg_media_unit_archive_loop_layout['author'] ) ? $ltg_media_unit_archive_loop_layout['author'] : 'default';
						Lightning_media_posts::patterns_select_options( $selected );
						echo '</select></td></tr>'."\n";

						?>
					</table>

	<p class="submit">
		<input type="submit" name="vk-media-unit-submit" class="button button-primary" value="<?php _e( 'Save Changes', $vk_ltg_media_posts_textdomain ); ?>" />
	</p>
	</div>
</form>