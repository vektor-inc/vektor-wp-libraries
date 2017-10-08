jQuery(document).ready(function(){
	run_slide_menu_control();
});
jQuery(window).resize(function(){
	run_menuResize();
});
/*-------------------------------------------*/
/*	メニューの開閉
/*	<div id="menu" onclick="showHide('menu');" class="itemOpen">MENU</div>
/*  * header.siteHeader を left で制御しているのは Safariでは
/*  position:fixed しているとウィンドウにfixしてしまってwrapを横にずらしてもついて来ないため
/*-------------------------------------------*/

function run_slide_menu_control(){
	jQuery('.menuBtn').prependTo('#bodyInner');
	jQuery('.menuBtn').each(function(){
		jQuery(this).click(function(){
			if ( jQuery(this).hasClass('menuBtn_left') ){
				var menuPosition = 'left';
			} else {
				var menuPosition = 'right';
			}
			// ※ この時点でLightning本体のmaster.jsによって既に menuOpenに新しく切り替わっている
			if ( jQuery(this).hasClass('menuOpen') ) {
				slide_menu_open(menuPosition);
			} else {
				slide_menu_close(menuPosition);
			}
		});
	});
}

function slide_menu_open(menuPosition){
	var navSection_open_position = 'navSection_open_' + menuPosition;
	jQuery('#navSection').addClass(navSection_open_position);

	var wrap_width = jQuery('body').width();
	jQuery('#bodyInner').css({"width":wrap_width});
	jQuery('#wrap').css({"width":wrap_width});

	var menu_width = wrap_width - 60 + 'px';

	jQuery('#headerTop').appendTo('#navSection');
	jQuery('#gMenu_outer').appendTo('#navSection');

	if ( menuPosition == 'right' ){
		jQuery('#wrap').stop().animate({
			// 右にメニューを表示するために左に逃げる
			"margin-left": "-" + menu_width,
		},200);
		jQuery('header.siteHeader').stop().animate({
			"left":"-"+menu_width,
		},200);
		jQuery('#navSection').css({"display":"block","width":menu_width, "right" :"-"+menu_width }).stop().animate({
			"right":0,
		},200);

	} else if ( menuPosition == 'left' ){
		jQuery('#wrap').stop().animate({
			"margin-left":menu_width,
		},200);
		jQuery('header.siteHeader').stop().animate({
			"left":menu_width,
		},200);
		jQuery('#navSection').css({"display":"block","width":menu_width, "left" :"-"+menu_width }).stop().animate({
			"left":0,
		},200,function(){
		});
	}
}
function slide_menu_close(menuPosition){

	if ( !menuPosition ){
		if ( jQuery('#navSection').hasClass('navSection_open_right') ){
			menuPosition = 'right';
		} else {
			menuPosition = 'left';
		}
	}

	var wrap_width = jQuery('body').width();
	jQuery('#bodyInner').css({"width":wrap_width});
	jQuery('#wrap').css({"width":wrap_width});

	var menu_width = wrap_width - 60 + 'px';

	jQuery('#wrap').stop().animate({ "margin-left":"0" },200);
	jQuery('header.siteHeader').stop().animate({ "left":"0" },200);

	if ( menuPosition == 'right' ) {jQuery('header.siteHeader').stop().animate({ "left":"0" },200);
		jQuery('#navSection').stop().animate({ "right":"-"+menu_width },200,function(){
			menuClose_common();
		});
	} else if ( menuPosition == 'left' ){
		jQuery('#navSection').stop().animate({ "left":"-"+menu_width },200,function(){
			menuClose_common();
		});
	}
}
function menuClose_common(){
	// アニメーションが終わってから実行
	jQuery('#navSection').removeClass('navSection_open_right');
	jQuery('#navSection').removeClass('navSection_open_left');
	jQuery('#navSection').css({ "right":"", "left":"", "display":"" });
	jQuery('#headerTop').prependTo('header.siteHeader');
	jQuery('#bodyInner').css({"width":""});
	jQuery('#wrap').css({"width":""});
	// judge animate execution
	if(jQuery('#navSection').is(':animated')){
		jQuery('#gMenu_outer').insertAfter('.navbar-header');
	}
}
function run_menuResize(){
	var wrap_width = jQuery('body').width();
	jQuery('#bodyInner').css({"width":wrap_width});
	// jQuery('#wrap').css({"width":wrap_width,"margin-left":"","margin-right":""});
	// menuClose_common();
	var headerHeight = jQuery('header.siteHeader').height;
	jQuery('#top__fullcarousel').css({"margin-top":headerHeight});
	if ( wrap_width > 991 ) {
		slide_menu_close();
	}
}
