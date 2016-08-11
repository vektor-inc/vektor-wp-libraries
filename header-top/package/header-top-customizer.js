 /**
    * このファイルはテーマカスタマイザーライブプレビューをライブにします。
    * テーマ設定で 'postMessage' を指定し、ここで制御します。
    * JavaScript はコントロールからテーマ設定を取得し、 
    * jQuery を使ってページに変更を反映します。
    */
   ( function( $ ) {
   	       // リアルタイムにサイトタイトルを変更
       wp.customize( 'lightning_theme_options[header_top_contact_txt]', function( value ) {
           value.bind( function( newval ) {
              if ( newval ){
                if ( jQuery('#headerTop .container div:last-child' ).hasClass('headerTop_contactBtn') ){
                  $( '.headerTop_contactBtn a' ).html( newval );
                } else {
                  $( '#headerTop .container').append( '<div class="headerTop_contactBtn"><a href="" class="btn btn-primary">' + newval + '</a></div>' );
                }
              } else {
                $( '.headerTop_contactBtn' ).remove();
              }
           } );
       } );
       wp.customize( 'lightning_theme_options[header_top_tel_number]', function( value ) {
           value.bind( function( newval ) {
              if ( newval ){
                  // ulのid名はメニューエリアではなくカスタムメニューのidが入る（＝変動する）のでulのidでは指定しないこと
                  // 電話番号が存在しない場合
                  if ( jQuery('#headerTop ul.nav li:last-child').hasClass('headerTop_tel') ){
                    $( '#headerTop ul.nav li.headerTop_tel' ).html( '<a class="headerTop_tel_wrap" href="tel:' + newval + '">' + newval + '</a>' );
                  } else {
                    $( '#headerTop ul.nav').append( '<li class="headerTop_tel"><a class="headerTop_tel_wrap" href="tel:' + newval+ '">' + newval + '</a></li>' );
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