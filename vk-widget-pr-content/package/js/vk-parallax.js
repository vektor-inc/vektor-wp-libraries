;
(function($) {
	/*-------------------------------------*/
	/*
	/*-------------------------------------*/

	jQuery(window).scroll(function() {
		prlx_scr();
	});
	jQuery(window).resize(function() {
		prlx_scr();
	});


	function bg_resize() {
		// .scr_item のクラスが付いている要素を
		jQuery('.vk-prlx').each(function(i) {

		}); // jQuery('.vk-prlx').each(function(i) {
	}

	function prlx_scr() {

		// スクロールした量
		var scrolled_Y = jQuery(window).scrollTop();
		// ウィンドウの高さ
		var window_H = document.documentElement.clientHeight;
		// スクロールに対して背景を動かす割合
		var scroll_rate = 0.5;

		// .scr_item のクラスが付いている要素を
		jQuery('.vk-prlx').each(function(i) {

			/*-------------------------------------*/
			/*	画像のサイズ取得 / リサイズ
			/*-------------------------------------*/
			var urlRegex = /.*url\(['"]*(.*?)['"]*\).*/g;
			// 画像のURLを取得
			var url = jQuery(this).css('background-image').replace(urlRegex, '$1');

			var img = new Image();
			img.src = url;

			var img_W = img.width; // 幅
			var img_H = img.height; // 高さ
			var img_rate = img_W / img.height; // 画像の縦横比 横長の画像なら1より大きい数字になる

			var area_W = jQuery(this).outerWidth(); // paddingを含んだ幅
			var area_H = jQuery(this).outerHeight(); // paddingを含んだ高さ
			// console.log('area_W : ' + area_W + ' / area_H : ' + area_H);

			// 縦横比
			var area_rate = area_W / area_H;

			var bg_W = 0;
			var bg_H = 0;

			// 最低限必要な画像の高さ = エリアの高さ + エリアの高さ * 動かす比率
			var need_H = area_H + area_H * scroll_rate;

			// もし 必要な高さがもともとある場合
			if (need_H < img_H) {
				// 背景高さは画像のまま
				bg_H = img_H;
				// 背景横幅 = 現在の画像の高さ * 画像縦横比
				bg_W = img_H * img_rate;

				// 高さが足りなかった場合
			} else {
				bg_H = need_H;
				bg_W = need_H * img_rate;
			}

			// もし （高さは足りてたけど）横幅が足りなかった場合
			if (bg_W < area_W) {
				// 背景幅 = エリア幅
				bg_W = area_W;
				// 背景高 = エリア幅 * 画像縦横比
				bg_H = area_W / img_rate;
			}

			console.log('エリア高さ : ' + area_H + ' < 必要高さ : ' + need_H + ' < 背景画像高さ : ' + bg_H);
			// console.log('エリア幅 : ' + area_W + ' < 背景画像幅 : ' + bg_W);

			/*-------------------------------------*/
			/*	画像のスタート位置計算
			/*-------------------------------------*/

			// 表示領域最上部から対象の最上部までの高さ
			var position_Y = jQuery(this).offset().top;

			// 開始位置 = パララックスエリアの位置 - ウィンドウの高さ
			var prlx_start_Y = position_Y - window_H;
			// はみ出してる距離
			var bg_over_H = bg_H - area_H;
			// 背景を動かす間の距離
			var prlx_area_H = window_H + area_H;
			// 1スクロールで動かす距離
			var bg_offset_distance = bg_over_H / prlx_area_H;

			if (prlx_start_Y < scrolled_Y) {

				// // 開始位置からスクロールした量
				var bg_area_Y = (scrolled_Y - prlx_start_Y);

				// 最低限必要な画像高さと背景画像の高さが同じの場合
				if (need_H == bg_H) {
					var bg_position_Y = -bg_area_Y * bg_offset_distance;

					// 最低限必要な画像高さから背景画像の高さがはみ出している場合
				} else if (need_H < bg_H) {
					var over_offset_H = (bg_H - need_H) / 2;
					var bg_position_Y = -bg_area_Y * bg_offset_distance - over_offset_H;
				}

				console.log('Y : ' + bg_position_Y);

				// 背景の位置を指定
				var backgroundPosition = "center " + bg_position_Y + "px";

			}

			jQuery(this).css({
				"background-position": backgroundPosition,
				"background-size": bg_W + 'px ' + bg_H + 'px',
			});
		});
	}

})(jQuery);