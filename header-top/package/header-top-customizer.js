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
                // 最後のdiv要素が headerTop_contactBtn だったら = 既にボタンがある場合
                if ( jQuery('#headerTop .container div:last-child' ).hasClass('headerTop_contactBtn') ){
                  // テキストのみ打ち替える
                  $( '.headerTop_contactBtn a' ).html( newval );

                // ボタンが無い場合
                } else {
                  // if ()
                  // 最後にボタンを追加する
                  $( '#headerTop .container').append( '<div class="headerTop_contactBtn"><a href="" class="btn btn-primary">' + newval + '</a></div>' );
                }

              // ボタンのテキストがない場合
              } else {
                // ボタンを削除
                $( '.headerTop_contactBtn' ).remove();
              }
           } );
       } );
       wp.customize( 'lightning_theme_options[header_top_contact_url]', function( value ) {
           value.bind( function( newval ) {
                jQuery('#headerTop .headerTop_contactBtn a' ).attr({href: newval});

                if ( !newval ){
                  $( '.headerTop_contactBtn' ).remove();
                }

           } ) ;
       } );
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