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

	/**
	 * ここからテーマ
	 */

	// Lightning (ja)
	$banner_array[] = array(
		'type' => 'theme',
		'slug' => 'lightning/style.css',
		'image_url'    => $imgge_base_url . 'lightning_bnr_ja.jpg',
		'link_url'     => 'https://lightning.nagoya/ja/',
		'alt'          => 'Lightning',
		'language'     => 'ja'
	);

	// Lightning (en)
	$banner_array[] = array(
		'type' => 'theme',
		'slug' => 'lightning/style.css',
		'image_url'    => $imgge_base_url . 'lightning_bnr_en.jpg',
		'link_url'     => 'https://lightning.nagoya/',
		'alt'          => 'Lightning',
		'language'     => 'en'
	);

	// Katawara (ja)
	$banner_array[] = array(
		'type' => 'theme',
		'slug' => 'katawara/style.css',
		'image_url'    => $imgge_base_url . 'katawara_bnr.jpg',
		'link_url'     => 'https://www.vektor-inc.co.jp/service/wordpress-theme/katawara/',
		'alt'          => 'Katawara',
		'language'     => 'ja'
	);

	// Bill Vektor (ja)
	$banner_array[] = array(
		'type' => 'theme',
		'slug' => 'bill-vektor/style.css',
		'image_url'    => $imgge_base_url . 'billvektor_banner.png',
		'link_url'     => 'https://billvektor.com',
		'alt'          => '見積書・請求書管理用WordPressテーマ Bill Vektor',
		'language'     => 'ja'
	);

	/**
	 * ここまでテーマ
	 */

	/**
	 * ここからプラグイン
	 */

	// VK Block Patterns (ja)
	$banner_array[] = array(
		'type' => 'plugin',
		'slug' => 'vk-block-patterns/vk-block-patterns.php',
		'image_url'    => $imgge_base_url . 'vk-block-patterns_bnr_ja.jpg',
		'link_url'     => admin_url('plugin-install.php?s=vk+block+patterns&tab=search&type=term'),
		'alt'          => 'VK Block Patterns',
		'language'     => 'ja'
	);

	// VK Block Patterns (en)
	$banner_array[] = array(
		'type' => 'plugin',
		'slug' => 'vk-block-patterns/vk-block-patterns.php',
		'image_url'    => $imgge_base_url . 'vk-block-patterns_bnr_en.jpg',
		'link_url'     => admin_url('plugin-install.php?s=vk+block+patterns&tab=search&type=term'),
		'alt'          => 'VK Block Patterns',
		'language'     => 'en'
	);

	// VK Link Target Controller (ja)
	$banner_array[] = array(
		'type' => 'plugin',
		'slug' => 'vk-link-target-controller/vk-link-target-controller.php',
		'image_url'    => $imgge_base_url . 'vk-link-target-controller_bnr.jpg',
		'link_url'     => admin_url( 'plugin-install.php?s=vk+link+target+controller&tab=search&type=term' ),
		'alt'          => 'VK Link Target Controller',
		'language'     => 'ja'
	);

	// VK Link Target Controller (en)
	$banner_array[] = array(
		'type' => 'plugin',
		'slug' => 'vk-link-target-controller/vk-link-target-controller.php',
		'image_url'    => $imgge_base_url . 'vk-link-target-controller_notxt_bnr.jpg',
		'link_url'     => admin_url( 'plugin-install.php?s=vk+link+target+controller&tab=search&type=term' ),
		'alt'          => 'VK Link Target Controller',
		'language'     => 'en'
	);

	// VK Aost Author Display (ja)
	$banner_array[] = array(
		'type' => 'plugin',
		'slug' => 'vk-post-author-display/post-author-display.php',
		'image_url'    => $imgge_base_url . 'post_author_display_bnr_ja.jpg',
		'link_url'     => admin_url( 'plugin-install.php?s=vk+link+target+controller&tab=search&type=term' ),
		'alt'          => 'VK Aost Author Display',
		'language'     => 'ja'
	);

	// VK Aost Author Display (en)
	$banner_array[] = array(
		'type' => 'plugin',
		'slug' => 'vk-post-author-display/post-author-display.php',
		'image_url'    => $imgge_base_url . 'post_author_display_bnr_en.jpg',
		'link_url'     => admin_url( 'plugin-install.php?s=vk+link+target+controller&tab=search&type=term' ),
		'alt'          => 'VK Aost Author Display',
		'language'     => 'en'
	);

	// VK All in One Expansion Unit (ja)
	$banner_array[] = array(
		'type' => 'plugin',
		'slug' => 'vk-all-in-one-expansion-unit/vkExUnit.php',
		'image_url'    => $imgge_base_url . 'ExUnit_bnr.png',
		'link_url'     => 'https://ex-unit.nagoya/ja/',
		'alt'          => 'VK All in One Expansion Unit',
		'language'     => 'ja'
	);

	/**
	 * ここまでプラグイン
	 */

	return $banner_array;
}