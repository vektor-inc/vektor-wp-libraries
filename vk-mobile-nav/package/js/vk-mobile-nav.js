/* ************************************* */

/* **** Caution **** */

/*
This original file is following place.
https://github.com/vektor-inc/vektor-wp-libraries
If you want to change this file that, you have to change original file.
*/

/* ************************************* */

/*

@version 0.3.0

#vk-mobile-nav-menu-btn : メニューの開閉ボタン
.vk-mobile-nav-menu-btn : メニューの開閉ボタン
.menu-open : メニューが開いている時に .vk-mobile-nav-menu-btn に追加で付与されるクラス

#vk-mobile-nav : メニュー本体
.vk-mobile-nav : メニュー本体
.vk-mobile-nav-open : メニューが開いている時に .vk-mobile-nav に追加で付与されるクラス

.vk-menu-acc : 子階層をアコーディオンにする ul 要素
.vk-menu-acc-active : .vk-menu-acc がアコーディオン化された時に付与されるクラス

.acc-btn : 子階層の開閉ボタン
.acc-btn-open : 子階層が閉じている時に .acc-btn に追加で付与されるクラス
.acc-btn-close : 子階層が開いている時に .acc-btn に追加で付与されるクラス

.acc-child-open : 子階層の ul が開いている時に付与されるクラス
*/

(function() {
    var VkMobileNav = {};

	/*-------------------------------------*/
	/*  Functions
	/*-------------------------------------*/

	/**
	 * モバイルデバイスかどうかを判定する。
	 *
	 * @return {boolean} モバイルデバイスの UserAgent なら true。
	 */
	VkMobileNav.isMobileDevice = function() {
		return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
	}

	/**
	 * 判定結果に応じて body にデバイスクラス（device-mobile / device-pc）を付与する。
	 *
	 * @return {void}
	 */
	VkMobileNav.addDeviceClass = function() {
		// モバイルデバイスの場合は body に device-mobile クラスを追加
		// モバイルデバイスでない場合は body に device-pc クラスを追加
		const deviceClass = VkMobileNav.isMobileDevice() ? 'device-mobile' : 'device-pc';
		// あらかじめ付与されているクラスを削除
		document.body.classList.remove('device-mobile', 'device-pc');
		// デバイスクラスを追加
		document.body.classList.add(deviceClass);
	}

	/**
	 * メニューを開き、開いた状態を支援技術へ伝える（aria-expanded=true）。
	 *
	 * @return {void}
	 */
	VkMobileNav.openMenu = function() {

		// メニューボタンに .menu-open クラスを付与
		if (VkMobileNav.menuBtn) {
			VkMobileNav.menuBtn.classList.add('menu-open');
			// 開いた状態を支援技術へ伝えるため aria-expanded を true に同期
			VkMobileNav.menuBtn.setAttribute('aria-expanded', 'true');
		}

		// メニューに .vk-mobile-nav-open クラスを付与
		if ( VkMobileNav.menu ){
			VkMobileNav.menu.classList.add('vk-mobile-nav-open');
		}

	}

	/**
	 * メニューを閉じ、閉じた状態を支援技術へ伝える（aria-expanded=false）。
	 *
	 * @return {void}
	 */
	VkMobileNav.closeMenu = function() {

		if (VkMobileNav.menuBtn) {
			// ※ fix nav の方を押される事もある
			VkMobileNav.menuBtn.classList.remove('menu-open');
			// 閉じた状態を支援技術へ伝えるため aria-expanded を false に同期
			VkMobileNav.menuBtn.setAttribute('aria-expanded', 'false');
		}

		// メニューから .vk-mobile-nav-open クラスを削除
		if ( VkMobileNav.menu ){
			VkMobileNav.menu.classList.remove('vk-mobile-nav-open');
		}
	}

	/*-------------------------------------*/
	/*  Run Functions
	/*-------------------------------------*/
	// HTML要素の読み込みが完了してから実行
	window.addEventListener('DOMContentLoaded', () => {

		/**
		 * 初期設定。デバイスクラス付与・要素取得を行い、
		 * メニューボタンに aria 属性・キーボード操作用属性が欠落していれば補完する。
		 *
		 * @return {void}
		 */
		const init = () => {
			// デバイスクラスの付与
			VkMobileNav.addDeviceClass();
			// メニューボタンの取得
			VkMobileNav.menuBtn = document.getElementById('vk-mobile-nav-menu-btn');
			// メニュー本体の取得
			VkMobileNav.menu = document.getElementById('vk-mobile-nav');

			// 要素が存在することを確認
			if (!VkMobileNav.menuBtn || !VkMobileNav.menu) {
				console.error('Required elements not found');
				return;
			}

			// aria 属性・キーボード操作用属性のフォールバック付与
			// 通常は PHP 側マークアップで初期付与されるが、固定ナビ側など別経路でボタンが
			// 描画され属性が欠落している場合に備え、未設定なら初期状態を補完する。
			if (!VkMobileNav.menuBtn.hasAttribute('aria-expanded')) {
				VkMobileNav.menuBtn.setAttribute('aria-expanded', 'false');
			}
			if (!VkMobileNav.menuBtn.hasAttribute('aria-controls')) {
				VkMobileNav.menuBtn.setAttribute('aria-controls', 'vk-mobile-nav');
			}
			// div 要素はそのままではキーボードフォーカス・操作ができないため、
			// role="button" / tabindex="0" が欠落していれば補完する。
			if (!VkMobileNav.menuBtn.hasAttribute('role')) {
				VkMobileNav.menuBtn.setAttribute('role', 'button');
			}
			if (!VkMobileNav.menuBtn.hasAttribute('tabindex')) {
				VkMobileNav.menuBtn.setAttribute('tabindex', '0');
			}
		};

		/**
		 * メニュー開閉ボタンの開閉トグル（click / keydown で共通利用）。
		 * 開いていれば閉じ、閉じていれば開く。
		 *
		 * @param {HTMLElement} [targetBtn] 判定対象のボタン要素。省略時は取得済みの menuBtn を使う。
		 * @return {void}
		 */
		VkMobileNav.toggleMenu = function(targetBtn) {
			// 引数が無い場合は取得済みの menuBtn を使う
			const btn = targetBtn || VkMobileNav.menuBtn;
			if (!btn) {
				return;
			}
			// メニューボタンと本体のクラスを切り替える
			if (btn.classList.contains('menu-open')) {
				// 開いている場合 → 閉じる
				VkMobileNav.closeMenu();
			} else {
				// 閉じている場合 → 開く
				VkMobileNav.openMenu();
			}
		};

		init();

		// メニュー開閉ボタンがクリックされた時の処理 //////////////////////////////////////
		/*
		モバイル固定ナビ利用時にアイコンフォントのタグを押されてしまうので
		addEventListener('click', (e) => からの e.target.classList などで取得しても
		fontawesome のクラス名が返ってきて誤動作してしまうため、buttn に一旦格納している
		*/
		let button = document.getElementById('vk-mobile-nav-menu-btn');
		if (button) {
			button.addEventListener('click', () => {
				// クリックでもキーボードでも同じトグル処理を共通化して呼ぶ
				VkMobileNav.toggleMenu(button);
			})
			// キーボード操作対応：Enter / Space で開閉する。
			// div + role="button" はネイティブボタンと違い Enter/Space で click が発火しないため、
			// keydown を明示的に拾ってトグルする。Space は既定のページスクロールを抑止する。
			button.addEventListener('keydown', (e) => {
				if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
					e.preventDefault();
					VkMobileNav.toggleMenu(button);
				}
			})
		}

		// ナビゲーションリンクがクリックされた時の処理 //////////////////////////////////////
		const navLinks = document.querySelectorAll('.vk-mobile-nav li > a');
		navLinks.forEach((link) => {
			link.addEventListener('click', (e) => {
				let me = e.target
				let href = me.getAttribute('href')

				// クリックされたリンク先がページ内リンクかどうか
				if(href.indexOf('#' == 0)) {
					// ページ内リンクの場合はメニューを閉じる
					VkMobileNav.closeMenu();
				}else{
					// ページ内リンク以外で閉じるとモバイルSafariにおいて
					// 閉じる動作の途中で画面遷移時に画面を停止させられるため
					// ページ内リンク以外では閉じないようにする
				}
			})
		})

	});

	/*-------------------------------------*/
	/*  sub item accordion
	/*-------------------------------------*/
	/**
	 * 子階層のアコーディオンを有効にする。
	 * 開閉ボタン（.acc-btn）を生成して aria 属性・キーボード操作用属性を付与し、
	 * click / keydown（Enter・Space）で開閉できるようにする。
	 *
	 * @return {void}
	 */
	VkMobileNav.runAcc = function() {

		// 子階層をアコーディオンにするメニュー（ul.vk-menu-acc）に対して、.vk-menu-acc-active クラスを付与
		const accMenus = document.querySelectorAll('ul.vk-menu-acc');

		// サブメニュー展開用のボタン要素 subMenuButton を span タグで定義して .acc-btn , .acc-btn-open クラスを付与
		const subMenuButton = document.createElement('span');
		subMenuButton.classList.add('acc-btn', 'acc-btn-open');
		// .acc-btn はアイコンのみで表示テキストを持たないため、アクセシブルな名前を aria-label で付与する。
		// 文言は PHP から渡される翻訳値を優先し、未提供時は英語フォールバックを使う。
		const accBtnLabel = (window.vkMobileNavL10n && window.vkMobileNavL10n.openSubMenu) ? window.vkMobileNavL10n.openSubMenu : 'Submenu';
		subMenuButton.setAttribute('aria-label', accBtnLabel);
		// 子階層は初期状態で閉じているため aria-expanded を false で初期化する。
		subMenuButton.setAttribute('aria-expanded', 'false');
		// span 要素はそのままではキーボードフォーカス・操作ができないため、
		// role="button" / tabindex="0" を付与して到達・操作可能にする。
		subMenuButton.setAttribute('role', 'button');
		subMenuButton.setAttribute('tabindex', '0');
		
		// ul.vk-menu-acc ul.sub-menu がある場合（子階層をアコーディオンにするメニューの中に子階層がある場合）
		accMenus.forEach((elm) => {
			// ul.vk-menu-acc に .vk-menu-acc-active クラスを付与
			elm.classList.add('vk-menu-acc-active');
			// ul.vk-menu-acc ul.sub-menu をループ処理
			elm.querySelectorAll('ul.sub-menu').forEach((subMenu) => {
				// ul.vk-menu-acc ul.sub-menu の前に subMenuButton を追加
				subMenu.before(subMenuButton.cloneNode(true));
				// 該当の ul.sub-menu に acc-child-close クラスを付与
				subMenu.classList.add('acc-child-close');
				// 追加した subMenuButton（.acc-btn） がクリックされたら VkMobileNav.accAction に .acc と subMenu の要素を渡して実行
				subMenu.previousElementSibling.addEventListener('click', () => {
					VkMobileNav.accAction(subMenu);
				});
				// キーボード操作対応：Enter / Space で click と同じ開閉処理を発火する。
				// span + role="button" は Enter/Space で click が発火しないため keydown を明示的に拾う。
				// Space は既定のページスクロールを抑止する。
				subMenu.previousElementSibling.addEventListener('keydown', (e) => {
					if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
						e.preventDefault();
						VkMobileNav.accAction(subMenu);
					}
				});

			});
		});
	}

	/**
	 * 子階層のアコーディオンを開閉し、開閉状態を支援技術へ伝える（aria-expanded を同期）。
	 *
	 * @param {HTMLElement} subMenu 開閉対象の子階層 ul.sub-menu 要素。
	 * @return {void}
	 */
	VkMobileNav.accAction = function(subMenu) {
		// subMenu の前要素の .acc-btn を取得して accBtn に格納
		const accBtn = subMenu.previousElementSibling;

		// subMenu が acc-child-close クラスを持っている場合
		if (subMenu.classList.contains('acc-child-close')) {
			// subMenu に acc-child-open クラスを付与
			subMenu.classList.remove('acc-child-close');
			subMenu.classList.add('acc-child-open');
			accBtn.classList.remove('acc-btn-open');
			accBtn.classList.add('acc-btn-close');
			// 子階層を開いた状態を支援技術へ伝えるため aria-expanded を true に同期
			accBtn.setAttribute('aria-expanded', 'true');
			// subMenu の親要素の li 要素に .acc-parent-open クラスを付与
			subMenu.parentNode.classList.remove('acc-parent-close');
			subMenu.parentNode.classList.add('acc-parent-open');
		} else {
			// subMenu に acc-child-close クラスを付与
			subMenu.classList.remove('acc-child-open');
			subMenu.classList.add('acc-child-close');
			accBtn.classList.remove('acc-btn-close');
			accBtn.classList.add('acc-btn-open');
			// 子階層を閉じた状態を支援技術へ伝えるため aria-expanded を false に同期
			accBtn.setAttribute('aria-expanded', 'false');
			// subMenu の親要素の li 要素に .acc-parent-open クラスを付与
			subMenu.parentNode.classList.remove('acc-parent-open');
			subMenu.parentNode.classList.add('acc-parent-close');
		}
	}

	/**
	 * 子階層のアコーディオン関連クラスをすべてリセットする。
	 *
	 * @return {void}
	 */
	VkMobileNav.resetAccordion = function() {
		const accMenus = document.querySelectorAll('ul.vk-menu-acc');
		accMenus.forEach((elm) => {
			elm.classList.remove('vk-menu-acc-active');
		});

		const accLis = document.querySelectorAll('ul.vk-menu-acc li');
		accLis.forEach((elm) => {
			elm.classList.remove('acc-parent-open');
		});

		const accChildClose = document.querySelectorAll('ul.vk-menu-acc li .acc-child-close');
		accChildClose.forEach((elm) => {
			elm.classList.remove('acc-child-close');
		});

		const accChildOpen = document.querySelectorAll('ul.vk-menu-acc li .acc-child-open');
		accChildOpen.forEach((elm) => {
			elm.classList.remove('acc-child-open');
		});

	}

	window.addEventListener('DOMContentLoaded', () => {
		// アコーディオンを有効にする
		VkMobileNav.runAcc();
	});

})();
