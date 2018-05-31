/**
 * このファイルはテーマカスタマイザーライブプレビューをライブにします。
 * テーマ設定で 'postMessage' を指定し、ここで制御します。
 * JavaScript はコントロールからテーマ設定を取得し、
 * jQuery を使ってページに変更を反映します。
 */
(function($) {

	var contact_txt = jQuery('.headerTop_contactBtn a').html();
	var contact_url = jQuery('.headerTop_contactBtn a').attr("href");

	// テキストが変更された時
	wp.customize('lightning_theme_options[header_top_hidden]', function(header_top_hidden_value) {
		header_top_hidden_value.bind(function(header_top_hidden_new_value) {
			header_top_hidden = header_top_hidden_value.get();
			if (header_top_hidden) {
				jQuery('.headerTop').css({
					'display': 'none'
				});
			} else {
				jQuery('.headerTop').css({
					'display': 'block'
				});
			}

		});
	});

	// テキストが変更された時
	wp.customize('lightning_theme_options[header_top_contact_txt]', function(contact_txt_value) {
		contact_txt_value.bind(function(contact_txt_new_val) {
			// グローバルのテキストを書き換えておく
			// contact_txt = contact_txt_value.get();
			contact_txt = contact_txt_new_val;
			// console.log( contact_txt_new_val + ' : ' + contact_url );
			header_top_contact_btn(contact_txt, contact_url);
		});
	});

	// URLが変更された時
	wp.customize('lightning_theme_options[header_top_contact_url]', function(contact_url_value) {
		contact_url_value.bind(function(contact_url_new_val) {
			// グローバルのURLを書き換えておく
			// contact_url = contact_url_value.get();
			contact_url = contact_url_new_val;
			// console.log( contact_txt + ' : ' + contact_url_new_val );
			header_top_contact_btn(contact_txt, contact_url);
		});
	});

	function header_top_contact_btn(contact_txt_new_val, contact_url_new_val) {
		if ( jQuery('body').hasClass('fa_v5_css') || jQuery('body').hasClass('fa_v5_svg') ){
			var icon = 'far fa-envelope';
		} else {
			var icon = 'fa fa-envelope-o';
		}
		if (contact_txt_new_val && contact_url_new_val) {

			// 既にボタンが存在している場合はテキストのみ打ち替える
			if (jQuery('#headerTop .container div:last-child').hasClass('headerTop_contactBtn')) {

				$('.headerTop_contactBtn a').html('<i class="' + icon + '"></i>' + contact_txt_new_val);

				// ボタンが無い場合は新規追加
			} else {

				$('#headerTop .container').append('<div class="headerTop_contactBtn"><a href="' + contact_url_new_val + '" class="btn btn-primary"><i class="' + icon + '"></i>' + contact_txt_new_val + '</a></div>');

			}
		} else {

			$('.headerTop_contactBtn').remove();

		}
	}

	// 電話番号
	wp.customize('lightning_theme_options[header_top_tel_number]', function(value) {
		value.bind(function(newval) {
			if ( jQuery('body').hasClass('fa_v5_css') || jQuery('body').hasClass('fa_v5_svg') ){
				var icon = 'fas fa-phone';
			} else {
				var icon = 'fa fa-phone';
			}
			var tel = '<a class="headerTop_tel_wrap" href="tel:' + newval + '"><i class="' + icon + '"></i>' + newval + '</a></li>';
			// var tel
			if (newval) {
				// ulのid名はメニューエリアではなくカスタムメニューのidが入る（＝変動する）のでulのidでは指定しないこと
				// 電話番号が存在しない場合
				if (jQuery('#headerTop ul.nav li:last-child').hasClass('headerTop_tel')) {
					$('#headerTop ul.nav li.headerTop_tel').html(tel);
				} else {
					$('#headerTop ul.nav').append('<li class="headerTop_tel">' + tel + '</li>');
				}
			} else {
				// 入力欄を空にされたら削除する
				$('#headerTop ul.nav li.headerTop_tel').remove();
			}
		});
	});

})(jQuery);
