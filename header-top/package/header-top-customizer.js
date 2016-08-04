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
               $( '.headerTop_contactBtn a' ).html( newval );
           } );
       } );

       // リアルタイムにリンク色を変更
       // wp.customize( 'mytheme_options[link_textcolor]', function( value ) {
       //     value.bind( function( newval ) {
       //         $('a').css('color', newval );
       //     } );
       // } );


   } )( jQuery );