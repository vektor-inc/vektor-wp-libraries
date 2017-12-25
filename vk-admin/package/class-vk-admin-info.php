<?php

/*--------------------------------------------------*/
/*  Display a vk_admin_widget information on the dashboard
/*--------------------------------------------------*/

/**
 * ダッシュボードにウィジェットを追加する。
 *
 * この関数は以下の 'wp_dashboard_setup' アクションにフックされています。
 */

 // add_filter( 'vkExUnit_is_plugin_dashboard_info_widget', 'vkExUnit_dash_beacon', 10, 1 );
 // function vkExUnit_dash_beacon( $flag ) {
	//  $flag = true;
	//  return $flag;
 // }


 function vk_admin_add_dashboard_widgets() {
 		wp_add_dashboard_widget(
 	 'vk_admin_add_dashboard_widgets', // Widget slug.
 	 __( 'Vektor WordPress Information','vkExUnit' ), // Title.
 	 'vk_dashboard_widget_body' // Display function.
 	 );

 	}
 add_action( 'wp_dashboard_setup', 'vk_admin_add_dashboard_widgets' );

 /**
  * ダッシュボードウィジェットのコンテンツを出力する関数を作成する。
  */
 function vk_dashboard_widget_body() {

  // 表示したいものを出力する。
	echo vk_dashboard_get_news_body();
  echo '<br />_|＼○_ﾋｬｯ ε=＼＿○ﾉ ﾎｰｳ!!'.'<br />'."\n";

 }



function vk_dashboard_get_news_body() {

	 $output = '';

	 include_once( ABSPATH . WPINC . '/feed.php' );

	 if ( 'ja' == get_locale() ) {
		 // $exUnit_feed_url = 'https://www.vektor-inc.co.jp/feed/?category_name=wordpress-info';
		 $exUnit_feed_url = 'https://www.vektor-inc.co.jp/feed/?category_name=internship';
		 // $exUnit_feed_url = 'https://www.vektor-inc.co.jp/feed/';
		 // $exUnit_feed_url = 'https://forum.bizvektor.com/?post_type=topic';
		 // $exUnit_feed_url = 'https://ex-unit.nagoya/ja/feed';
		 // $exUnit_feed_url = 'http://sk8-life.info/feed?category_name=sk8item';
		 $exUnit_feed_url = apply_filters( 'vkAdmin_news_RSS_URL_ja',$exUnit_feed_url );
	 } else {
		 $exUnit_feed_url = apply_filters( 'vkAdmin_news_RSS_URL', 'https://ex-unit.nagoya/feed' );
	 }

	 $my_feeds = array(
		 array( 'feed_url' => $exUnit_feed_url ),
	 );

	 foreach ( $my_feeds as $feed ) {
		 $rss = fetch_feed( $feed['feed_url'] );
		 print '<pre style="text-align:left">';print_r($rss);print '</pre>';

		 if ( ! is_wp_error( $rss ) ) {
			 $output = '';

			 $maxitems = $rss->get_item_quantity( 5 ); //number of news to display (maximum)
			 $rss_items = $rss->get_items( 0, $maxitems );

			 $output .= '<div class="rss-widget">';
			 $output .= '<h4 class="adminSub_title">'.apply_filters( 'vk-admin-sub-title-text', 'Information' ).'</h4>';
			 $output .= '<ul>';

			 if ( $maxitems == 0 ) {
				 $output .= '<li>';
				 $output .= __( 'Sorry, there is no post', 'vkExUnit' );
				 $output .= '</li>';
			 } else {
				 foreach ( $rss_items as $item ) {
					 $test_date 	= $item->get_local_date();
					 $content 	= $item->get_content();

					 if ( isset( $test_date ) && ! is_null( $test_date ) ) {
						 $item_date = $item->get_date( get_option( 'date_format' ) ) . '<br />'; } else {
						 $item_date = ''; }

						 $output .= '<li style="color:#777;">';
						 $output .= $item_date;
						 $output .= '<a href="' . esc_url( $item->get_permalink() ) . '" title="' . $item_date . '" target="_blank">';
						 $output .= esc_html( $item->get_title() );
						 $output .= '</a>';
						 $output .= '</li>';
				 }
			 }

			 $output .= '</ul>';
			 $output .= '</div>';
		 }

	 } // if ( ! is_wp_error( $rss ) ) {

	 return $output;
 }
