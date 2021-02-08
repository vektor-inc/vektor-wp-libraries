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
	$vk_campaign_text_hook_point = array();
	// JPNSTYLE II との兼ね合いを考えるとせいぜいこれが精一杯 !?
	$vk_campaign_text_hook_point = apply_filters( 'lightning_campaign_text_hook_point', $vk_campaign_text_hook_point );

	// キャンペーンテキストの CSS を読み込む位置.
	// * 後で読み込むと読み込み順の都合上反映されない.
	global $vk_campaign_text_hook_style;
	$vk_campaign_text_hook_style = 'lightning-design-style';

	// 表示位置の配列.
	global $vk_campaign_text_display_position_array;
	$vk_campaign_text_display_position_array = array(
		'header_prepend'          => array(
			'hookpoint' => array( 'lightning_header_prepend' ),
			'label'     => __( 'Header Before', 'vk_campaign_text_text_domain'),
		),
		'header_append'           => array(
			'hookpoint' => array( 'lightning_header_append' ),
			'label'     => __( 'Header After', 'vk_campaign_text_text_domain'),
		),
		'slide_page_header_after' => array(
			'hookpoint' => array( 'lightning_top_slide_after', 'lightning_breadcrumb_before' ),
			'label'     => __( 'Slide and Page Header After', 'vk_campaign_text_text_domain'),
		),
	);

	$skin = get_option( 'lightning_design_skin' );
	if ( 'jpnstyle' !== $skin ) {
		require_once dirname( __FILE__ ) . '/package/class-vk-campaign-text.php';
	}
}

// なるべくLightnigの名前になるように class_exists の外でOK.
global $vk_campaign_text_prefix;
$vk_campaign_text_prefix = lightning_get_prefix_customize_panel();

global $vk_campaign_text_dir_uri;
