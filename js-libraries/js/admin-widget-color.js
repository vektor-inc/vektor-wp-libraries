/*
最初からカラーピッカーのクリアボタンに処理したいが、
クリアボタンが隠れているのでストレートに処理できない。
そのためカラーピッカーが展開されてから、クリアボタンが押された時の処理を書いている。
だか、カラーピッカーの展開も最初は観測できないので、カスタマイズコントロールを監視し、
カスタマイズコントロールのclass名に変化があった時に発火させている
 */

jQuery(document).ready(function(i){

	// 各ウィジェットコントロールごとに処理
	jQuery('.customize-control-widget_form').each(function(){
		// idを取得
		var id = jQuery(this).attr('id');

		//監視ターゲットの取得
		const target = document.getElementById(id);

		//オブザーバーの作成
		const observer = new MutationObserver(records => {

			// カラーピッカーがクリクされたら
			jQuery(this).find('.wp-color-result').click(function() {
				// console.log('Picker Open');

				// クリアボタンが押されたら
				jQuery('.wp-picker-clear').click(function() {
					// console.log('Click Clear');

					// valueを空にして再読込み
					jQuery(this).prev().find('input.wp-color-picker').attr({'value':''}).change();
				});
			});
		});

		//監視オプションの作成
		const options = {
		    attributes: true,
		    attributeFilter: ["class"]
		};

		//監視の開始
		observer.observe(target, options);

	});
	//監視の停止（実際には特定条件下で実行）
	// let shouldStopObserving = false;
	// if(shouldStopObserving){
	//     observer.disconnect();
	// }
});
