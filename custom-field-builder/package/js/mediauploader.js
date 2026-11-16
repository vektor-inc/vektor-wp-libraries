/*-------------------------------------------*/
/* メディアアップローダー
/*-------------------------------------------*/
jQuery(document).ready(function($){
    var custom_uploader;
    // var media_id = new Array(2);　//配列の宣言
    // media_id[0] = "head_logo";
    // var media_btn = '#media_' + media_id[i];
    // var media_target = '#' + media_id[i];
    jQuery('.cfb_media_btn').click(function(e) {

        // 画像URLで値を返す場合はフォームの input タグのIDにsrcを含める
        // クリックされたボタンのidを取得 → ボタンの id名 から media_src_ を削除して かわりに # をつける
        media_target_src    = jQuery(this).attr('id').replace(/media_src_/g,'#');

        // id で値を返す場合
        media_target    = jQuery(this).attr('id').replace(/media_/g,'#');
        thumb_src       = jQuery(this).attr('id').replace(/media_/g,'#thumb_');

        e.preventDefault();
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
        custom_uploader = wp.media({
            title: vk_cfb.select_images,
            // library: {
            //     type: 'image'
            // },
            button: {
                text: vk_cfb.select_images
            },
            multiple: false, // falseにすると画像を1つしか選択できなくなる
        });
        custom_uploader.on('select', function() {
            var images = custom_uploader.state().get('selection');
            images.each(function(file){
                // urlを返す場合
                jQuery(media_target_src).attr('value', file.toJSON().url );
                // idを返す場合
                jQuery(media_target).attr('value', file.toJSON().id );
                jQuery(thumb_src).attr('src', file.toJSON().url );
            });
        });
        custom_uploader.open();
    });

		/*	リセット処理
		/*-------------------------------------------*/
		jQuery('.media_reset_btn').click(function(e) {
        media_target_id			= jQuery(this).attr('id').replace(/media_reset_/g,'#');
        thumb_id						= jQuery(this).attr('id').replace(/media_reset_/g,'#thumb_');
        jQuery(media_target_id).attr('value', '' );
        jQuery(thumb_id).attr('src', jQuery(thumb_id).attr('data-no-image') || '' );
				jQuery(thumb_id).attr('srcset', '' );
    });

    /*	プレビュー画像の読み込み失敗時の処理
    /*-------------------------------------------*/
    const thumb_selector = '.input_thumb';

    /**
     * 読み込みに失敗したプレビュー画像を、data-no-image の「画像なし」の画像へ差し替える。
     * 保存値（hidden 入力）は変更しない。
     *
     * @param {HTMLImageElement} img プレビュー画像
     */
    function replace_thumb_with_no_image( img ) {
        const no_image = img.getAttribute('data-no-image');

        // 属性が無い場合と、差し替え済みの場合（「画像なし」自体の読み込み失敗を含む）は何もしない
        if ( ! no_image || img.getAttribute('src') === no_image ) {
            return;
        }

        // srcset が残ると src より優先されるため空にする
        img.setAttribute('srcset', '' );
        img.setAttribute('src', no_image );
    }

    // error イベントは bubble しないため、キャプチャで拾う（後から増えた画像項目にも効く）
    document.addEventListener('error', function(e) {
        if ( e.target && e.target.matches && e.target.matches( thumb_selector ) ) {
            replace_thumb_with_no_image( e.target );
        }
    }, true );

    // このスクリプトは admin_footer で読み込まれるため、束縛前に読み込みに失敗した画像も差し替える
    document.querySelectorAll( thumb_selector ).forEach(function(img) {
        if ( img.getAttribute('src') && img.complete && 0 === img.naturalWidth ) {
            replace_thumb_with_no_image( img );
        }
    });
});
