;
(function($) {
	/*-------------------------------------*/
	/*
	/*-------------------------------------*/

	jQuery(window).scroll(function () {
		prlx_scr();
	});
	function prlx_scr(){

		// スクロールした量
		var scrolled_Y = jQuery(window).scrollTop();
		// ウィンドウの高さ
		var window_height = document.documentElement.clientHeight;

		// .scr_item のクラスが付いている要素を
		jQuery('.vk-prlx').each(function(i){

			// 表示領域最上部から対象の最上部までの高さ
			var position_Y = jQuery(this).offset().top;
			console.log( 'ページ上部からの距離（変動しない） : ' + position_Y );
			// 開始位置
			var prlx_start_Y = position_Y - window_height;

			if ( prlx_start_Y < scrolled_Y ){

				// 開始位置 - スクロール量
				var bg_position = ( prlx_start_Y - scrolled_Y ) / 3;
				//
				console.log('かわったらいかんやつ:'+position_Y);

				// スクロールした位置の半分位置
				var halfPosition = position_Y / 2 ;
				console.log(halfPosition);
				// 背景の位置を指定
				var backgroundPosition = "center " + bg_position + "px";
			}



			// アニメーションを動作させるポイント
			// var run_scrolled_Y = position_Y - run_offset_Y;
			jQuery(this).css({"background-position":backgroundPosition});
		});
	}
	// jQuery(document).ready(function() {
	// 	// 初期設定
	// 		// 対象の位置を取得
	// 	// スクロールしたら
	// 	// 現在のスクロール料を取得
	// 	var bodyWidth = jQuery(window).width();
	// 	// スクロールしたときの動作
	//
	// 	// とりあえず背景を固定に
	// 	// jQuery("#wrap").css({"background-attachment":"fixed"});
	//
	// 	// ウィンドウサイズが960より小さい場合
	// 	// if ( 980 < bodyWidth ) {
	// 		jQuery(window).scroll(function () {
	// 			// スクロールした位置
	// 			var topPosition = jQuery(window).scrollTop();
	//
	// 			// スクロールした位置の半分位置
	// 			var halfPosition = topPosition / 2 ;
	// 			// 背景の位置を指定
	// 			var backgroundPosition = "center -" + halfPosition + "px";
	// 			console.log(backgroundPosition);
	// 			jQuery(".vk-prlx").css({"background-position":backgroundPosition});
	// 		});
	// 	// }
	// });

})(jQuery);
