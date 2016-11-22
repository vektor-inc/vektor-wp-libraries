 /**
    * このファイルはテーマカスタマイザーライブプレビューをライブにします。
    * テーマ設定で 'postMessage' を指定し、ここで制御します。
    * JavaScript はコントロールからテーマ設定を取得し、 
    * jQuery を使ってページに変更を反映します。
    */
   ( function( $ ) {

      wp.customize( 'lightning_theme_options[header_top_contact_txt]', function( contact_txt_value ) {
        contact_txt_value.bind( function( contact_txt_new_val ) {
          wp.customize( 'lightning_theme_options[header_top_contact_url]', function( contact_url_value ) {
            contact_url_value.bind( function( contact_url_new_val ) {
              header_top_contact_btn( contact_txt_new_val, contact_url_new_val );
            } );
          } );
        } );
      } );
      wp.customize( 'lightning_theme_options[header_top_contact_url]', function( contact_url_value ) {
        contact_url_value.bind( function( contact_url_new_val ) {
          wp.customize( 'lightning_theme_options[header_top_contact_txt]', function( contact_txt_value ) {
            contact_txt_value.bind( function( contact_txt_new_val ) {
              header_top_contact_btn( contact_txt_new_val, contact_url_new_val );
            } );
          } );
        } );
      } );

      function header_top_contact_btn( contact_txt_new_val, contact_url_new_val ){
        if ( contact_txt_new_val && contact_url_new_val ){

          // 既にボタンが存在している場合はテキストのみ打ち替える
          if ( jQuery('#headerTop .container div:last-child' ).hasClass('headerTop_contactBtn') ){

            $( '.headerTop_contactBtn a' ).html( contact_txt_new_val );

          // ボタンが無い場合は新規追加
          } else {

            $( '#headerTop .container').append( '<div class="headerTop_contactBtn"><a href="'+contact_url_new_val+'" class="btn btn-primary">' + contact_txt_new_val + '</a></div>' );

          }
        } else {

          $( '.headerTop_contactBtn' ).remove();

        }
      }
       wp.customize( 'lightning_theme_options[header_top_tel_number]', function( value ) {
           value.bind( function( newval ) {
              var tel = '<a class="headerTop_tel_wrap" href="tel:' + newval+ '">' + newval + '</a></li>';
              // var tel
              if ( newval ){
                  // ulのid名はメニューエリアではなくカスタムメニューのidが入る（＝変動する）のでulのidでは指定しないこと
                  // 電話番号が存在しない場合
                  if ( jQuery('#headerTop ul.nav li:last-child').hasClass('headerTop_tel') ){
                    $( '#headerTop ul.nav li.headerTop_tel' ).html( tel );
                  } else {
                    $( '#headerTop ul.nav').append( '<li class="headerTop_tel">' + tel + '</li>' );
                  }
                } else {
                  // 入力欄を空にされたら削除する
                  $( '#headerTop ul.nav li.headerTop_tel' ).remove();
                }
           } );
       } );
       // リアルタイムにリンク色を変更
       // wp.customize( 'mytheme_options[link_textcolor]', function( value ) {
       //     value.bind( function( newval ) {
       //         $('a').css('color', newval );
       //     } );
       // } );


   } )( jQuery );