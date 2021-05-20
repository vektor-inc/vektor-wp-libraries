<?php
/**
 * VK Admin Banners
 */

/**
 * Register VK Admin Banners
 */
function vk_admin_registrer_banners() {
	$banner_array = array();
	$imgge_base_url = plugin_dir_url( __FILE__ ) . 'images/';

	/*
		// プロダクトを追加する場合
			$banner_array[] = array(
			'product_type'   => 'theme', // テーマかプラグインか
			'product_exists' => 'lightning/style.css', // テーマの場合は
			'image-url'      => $imgge_base_url . 'lightning_bnr_ja.jpg',
			'alt'            => 'lightning_bnr_ja',
			'language'       => 'ja'
		);
	 */

	// Lightning (Ja)
	$banner_array[] = array(
		'product_type' => 'theme',
		'product_slug' => 'lightning/style.css',
		'image-url'    => $imgge_base_url . 'lightning_bnr_ja.jpg',
		'link-url'     => 'https://lightning.nagoya/ja/',
		'alt'          => 'Lightning',
		'language'     => 'ja'
	);

	// Lightning (En)
	$banner_array[] = array(
		'product_type' => 'theme',
		'product_slug' => 'lightning/style.css',
		'image-url'    => $imgge_base_url . 'lightning_bnr_en.jpg',
		'link-url'     => 'https://lightning.nagoya/',
		'alt'          => 'Lightning',
		'language'     => 'en'
	);

	// Bill Vektor (ja)
	$banner_array[] = array(
		'product_type' => 'theme',
		'product_slug' => 'bill-vektor/style.css',
		'image-url'    => $imgge_base_url . 'billvektor_banner.png',
		'link-url'     => 'https://billvektor.com',
		'alt'          => '見積書・請求書管理用WordPressテーマ Bill Vektor',
		'language'     => 'en'
	);

	/*
	// Lightning Copyright Customizer
	$banner_array[] = array(
		'product_type' => 'plugin',
		'product_slug' => 'katawara/style.css',
		'image-url'    => $imgge_base_url . 'katawara_bnr.jpg',
		'link-url'     => 'https://lightning.nagoya/lightning_copyright_customizer/',
		'alt'          => 'katawara_bnr_ja',
		'language'     => 'ja'
	);

	// 採用情報
	$banner_array[] = array(
		'product_type' => 'other',
		'product_slug' => '',
		'image-url'    => $imgge_base_url . 'katawara_bnr.jpg',
		'link-url'     => 'https://lightning.nagoya/lightning_copyright_customizer/',
		'alt'          => 'katawara_bnr_ja',
		'language'     => 'ja'
	);
	*/

	// Katawara
	$banner_array[] = array(
		'product_type' => 'theme',
		'product_slug' => 'katawara/style.css',
		'image-url'    => $imgge_base_url . 'katawara_bnr.jpg',
		'link-url'     => 'https://www.vektor-inc.co.jp/service/wordpress-theme/katawara/',
		'alt'          => 'Katawara',
		'language'     => 'ja'
	);

	// VK Block Patterns (ja)
	$banner_array[] = array(
		'product_type' => 'plugin',
		'product_slug' => 'vk-block-patterns/vk-block-patterns.php',
		'image-url'    => $imgge_base_url . 'vk-block-patterns_bnr_ja.jpg',
		'link-url'     => admin_url('plugin-install.php?s=vk+block+patterns&tab=search&type=term'),
		'alt'          => 'VK Block Patterns',
		'language'     => 'ja'
	);

	// VK Block Patterns (en)
	$banner_array[] = array(
		'product_type' => 'plugin',
		'product_slug' => 'vk-block-patterns/vk-block-patterns.php',
		'image-url'    => $imgge_base_url . 'vk-block-patterns_bnr_en.jpg',
		'link-url'     => admin_url('plugin-install.php?s=vk+block+patterns&tab=search&type=term'),
		'alt'          => 'VK Block Patterns',
		'language'     => 'en'
	);

	// VK Link Target Controller (ja)
	$banner_array[] = array(
		'product_type' => 'plugin',
		'product_slug' => 'vk-link-target-controller/vk-link-target-controller.php',
		'image-url'    => $imgge_base_url . 'vk-link-target-controller_bnr.jpg',
		'link-url'     => admin_url( 'plugin-install.php?s=vk+link+target+controller&tab=search&type=term' ),
		'alt'          => 'VK Link Target Controller',
		'language'     => 'ja'
	);

	// VK Link Target Controller (en)
	$banner_array[] = array(
		'product_type' => 'plugin',
		'product_slug' => 'vk-link-target-controller/vk-link-target-controller.php',
		'image-url'    => $imgge_base_url . 'vk-link-target-controller_notxt_bnr.jpg',
		'link-url'     => admin_url( 'plugin-install.php?s=vk+link+target+controller&tab=search&type=term' ),
		'alt'          => 'VK Link Target Controller',
		'language'     => 'en'
	);

	// VK Aost Author Display (ja)
	$banner_array[] = array(
		'product_type' => 'plugin',
		'product_slug' => 'vk-post-author-display/post-author-display.php',
		'image-url'    => $imgge_base_url . 'post_author_display_bnr_ja.jpg',
		'link-url'     => admin_url( 'plugin-install.php?s=vk+link+target+controller&tab=search&type=term' ),
		'alt'          => 'VK Aost Author Display',
		'language'     => 'ja'
	);

	// VK Aost Author Display (en)
	$banner_array[] = array(
		'product_type' => 'plugin',
		'product_slug' => 'vk-post-author-display/post-author-display.php',
		'image-url'    => $imgge_base_url . 'post_author_display_bnr_en.jpg',
		'link-url'     => admin_url( 'plugin-install.php?s=vk+link+target+controller&tab=search&type=term' ),
		'alt'          => 'VK Aost Author Display',
		'language'     => 'en'
	);

	// VK All in One Expansion Unit (ja)
	$banner_array[] = array(
		'product_type' => 'plugin',
		'product_slug' => 'vk-all-in-one-expansion-unit/vkExUnit.php',
		'image-url'    => $imgge_base_url . 'ExUnit_bnr.png',
		'link-url'     => 'https://ex-unit.nagoya/ja/',
		'alt'          => 'VK All in One Expansion Unit',
		'language'     => 'ja'
	);

	return $banner_array;
}