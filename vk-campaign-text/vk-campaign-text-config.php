<?php
/**
 * VK Campaign Text Config
 *
 * @package Lightning Pro
 */

if ( ! class_exists( 'VK_Campaign_Text' ) ) {

	// キャンペーンテキストを挿入する位置.
	// * 後で読み込むと読み込み順の都合上反映されない.
	global $vk_campaign_text_hook_point;
	$vk_campaign_text_hook_point = 'lightning_header_append';

	require_once 'package/class-vk-campaign-text.php';
}

// なるべくLightnigの名前になるように class_exists の外でOK.
global $vk_campaign_text_prefix;
$vk_campaign_text_prefix = lightning_get_prefix_customize_panel();
