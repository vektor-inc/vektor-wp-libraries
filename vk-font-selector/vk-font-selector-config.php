<?php

/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'Vk_Font_Selector_Customize' ) ) {
	require_once( 'vk-font-switching-function/class-vk-font-selector.php' );

	global $vk_font_selector_textdomain;
	$vk_font_selector_textdomain = 'XXXX_plugin_text_domain_XXXX';

}



/*-------------------------------------------*/
/* 初期状態
/*-------------------------------------------*/

/*
■ メニューボタン
.vk-mobile-nav-menu-btn は初期状態ではCSSで非表示にしてある
.vk-mobile-nav-menu-btn がクリックされる
	メニューボタン .vk-mobile-nav-menu-btn に .menu-open を付与
		.vk-mobile-nav-menu-btn.menu-open になったらCSSで見た目をクローズボタンに変更
	メニュー本体 .vk-mobile-nav に .vk-mobile-nav-open が付与される。
		.vk-mobile-nav.vk-mobile-nav-open に対してCSSで表示処理

■ body class
wp_is_mobile でモバイル判定された場合はPHPでフックを使用してbodyタグに .mobile-device を付与している

 */


/*	 モバイル端末の時
/*-------------------------------------------*/
/*
端末判定でモバイルの場合は強制的にモバイルメニューを利用する
→ CSSでPC版のメニューを表示しない
	→ モバイルの場合はbodyタグに対してPHPで識別用のclassを付与し、
	そのclassがある場合にPC用のナビゲーションをCSSで非表示にする
	（ メディアクエリで画面サイズが広い時に非表示にすると、タブレットで画面が広い時にモバイルメニューが非表示になってしまう為 ）
→ php側でPCメニューを表示しない制御をするのが理想

/*	 PC端末の時
/*-------------------------------------------*/
/*
* html上は普通に表示し、CSSでPC版とモバイル版のメニューを切り替える
* PCでもモバイルの表示が確認しやすくするため
* CSSで特定の画面サイズでどちらのメニューを表示・非表示にするのか切り替える
 */
