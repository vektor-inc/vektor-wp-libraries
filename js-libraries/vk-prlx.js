/*!
 * vk-parallax.js v 0.0.1
 * Copyright 2018 Vektor,Inc.
 * MIT License
 */
/*
外枠のdivやsectionタグなどに .vk-prlx を付与する
外枠のdivのstyle属性に直接画像 url を記載する事で動作する
*/
;
(function($) {
	/*-------------------------------------*/
	/*
	/*-------------------------------------*/
	jQuery(document).ready(function() {
		prlx_scr();
	});
	jQuery(window).scroll(function() {
		prlx_scr();
	});
	jQuery(window).resize(function() {
		prlx_scr();
	});

	function prlx_scr() {

		// スクロールした量
		var scrled_Y = jQuery(window).scrollTop();
		// ウィンドウの高さ
		var window_H = document.documentElement.clientHeight;
		// 画像URLを取得する正規表現（background-image指定のurl()内だけを抽出する）
		var urlRegex = /.*url\(['"]*(.*?)['"]*\).*/g;

		// .scr_item のクラスが付いている要素を
		jQuery('.vk-prlx').each(function(i) {

			/*-------------------------------------*/
			/*	画像のサイズ取得 / リサイズ
			/*-------------------------------------*/
			// 画像のURLを取得
			var url = jQuery(this).css('background-image').replace(urlRegex, '$1');
			if ( url != 'none' ){

			var img = new Image();
			img.src = url;

			// 背景画像の元サイズの幅
			var img_W = img.width;
			// 背景画像の元サイズの高さ
			var img_H = img.height;
			// 画像の縦横比 横長の画像なら1より大きい数字になる
			var img_rate = img_W / img_H;

			var area_W = jQuery(this).outerWidth(); // paddingを含んだ幅
			var area_H = jQuery(this).outerHeight(); // paddingを含んだ高さ

			// 表示エリアの縦横比
			var area_rate = area_W / area_H;

			var bg_W = 0;
			var bg_H = 0;

			// スクロールに対して背景を動かす割合
			// 1...スクロールした分だけ画像が先に上がる（表示されている背景画像の下方向が見えるようになっていく）
			// 0...スクロールと同時に画像が動く（パララックス無しと同じ）
			// -1 ... スクロールした分だけ画像が遅れて上がる（表示されている背景画像の上方向が見えるようになっていく）
			var scrl_rate = -1;

			if (0 < scrl_rate) {
				// 最低限必要な画像の高さ = エリアの高さ + ( エリアの高さ * 動かす比率 )
				var need_H = area_H + (area_H * scrl_rate);
			} else {
				var need_H = area_H + (area_H * -scrl_rate);
			}

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

			// もし （高さは足りてたor高さが足りるまで拡大した後で）横幅が足りなかった場合
			if (bg_W < area_W) {
				// 背景幅 = エリア幅（に拡大）
				bg_W = area_W;
				// 背景高 = エリア幅 * 画像縦横比（に拡大）
				bg_H = area_W / img_rate;
			}

			/*-------------------------------------*/
			/*	画像のスタート位置計算
			/*-------------------------------------*/

			// 表示領域最上部から対象の最上部までの高さ
			var posi_Y = jQuery(this).offset().top;

			// 開始位置 = パララックスエリアの位置 - ウィンドウの高さ
			var prlx_start_Y = posi_Y - window_H;
			// はみ出してる高さ = 画像の高さ - 表示エリアの高さ
			var bg_over_H = bg_H - area_H;
			// 背景を動かす間の距離 = ウィンドウサイズ + パララックスエリアの高さ
			var prlx_area_H = window_H + area_H;
			// 必要高さよりはみ出している高さ
			var bg_need_over_H = bg_H - need_H;

			// 1スクロールで動かす距離 = はみ出している画像サイズ / パララックスが動作するスクロール量
			// ※ 必要な高さ以上に高い画像もあるので bg_H - area_H ではなく need_H - area_H になっている
			var bg_offset_distance = (need_H - area_H) / prlx_area_H;

			// パララックス開始
			if (prlx_start_Y < scrled_Y) {

				// 開始位置からスクロールした量
				var prlx_scrled_Y = (scrled_Y - prlx_start_Y);
				// 背景画像を移動させる距離
				var bg_posi_Y = prlx_scrled_Y * bg_offset_distance;

				// 最低限必要な画像高さから背景画像の高さがはみ出している場合
				if (need_H < bg_H) {
					// あらかじめずらしておく高さ
					var over_offset_H = bg_need_over_H / 2;
				} else {
					var over_offset_H = 0;
				}

				// 背景画像が先に上がる場合（背景画像の下方向が見えるようになる）
				if (0 < scrl_rate) {
					// 背景画像は表示エリアと上端が揃った状態でスタートし、上方向に移動するので - にする
					bg_posi_Y = -bg_posi_Y;
					// 画像がはみ出している分を更に上方向に補正
					bg_posi_Y = bg_posi_Y - over_offset_H;

					// 背景画像が遅れて上がる場合（背景画像の上方向が見えるようになる）
				} else if (0 > scrl_rate) {
					// 上方向が見えるようにするために予めマイナスオフセットして、スクロールする事でマイナスが減っていく

					// 遅れて上がる場合のマイナスオフセット値 = 必要高さ - 表示エリア
					var bottom_offset = -(need_H - area_H);
					// マイナスオフセットしてあった分からスクロールした分だけマイナスオフセットが減っていく（上が見えるようになる）
					bg_posi_Y = bottom_offset + bg_posi_Y;
					// 画像がはみ出している分を更に上方向に補正
					// ※必要高さよりも背景画像が高い場合は下端がはみ出した状態でスタートさせる形になる
					//  → 背景画像が必要高さからはみ出しているので、その分上にオフセットするためマイナス方向に増える
					bg_posi_Y = bg_posi_Y - over_offset_H;

				}

				/*-------------------------------------*/
				/*	デバッグ
				/*-------------------------------------*/
				// console.log('[' + i + '][H] エリア : ' + area_H + ' < 必要 : ' + need_H + ' < 背景画像 : ' + bg_H + ' / ' + 'ウィンドウ : ' + window_H);
				// console.log('[' + i + '][W] エリア : ' + area_W + ' < 背景画像 : ' + bg_W);
				// console.log('[' + i + '] 1スクロールで動かす背景の距離 : ' + bg_offset_distance);
				// console.log('[' + i + '] 必要高さからはみ出している画像の高さ : ' + bg_need_over_H);
				// console.log('[' + i + '] はみ出しているのでオフセットする高さ : ' + over_offset_H);
				// console.log('[' + i + '] Y : ' + bg_posi_Y);

				// 背景の位置を指定
				var backgroundPosition = "center " + bg_posi_Y + "px";

			}
			// console.log('area_W : ' + area_W + ' / area_H : ' + area_H);
			jQuery(this).css({
				"background-position": backgroundPosition,
				"background-size": bg_W + 'px ' + bg_H + 'px',
			});
			} // if ( url != 'none' ){
		}); // jQuery('.vk-prlx').each(function(i) {
	} // 	function prlx_scr() {

})(jQuery);
