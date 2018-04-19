var gulp = require('gulp');

// sass compiler
var sass = require('gulp-sass');

var cmq = require('gulp-merge-media-queries');
// add vender prifix
var autoprefixer = require('gulp-autoprefixer');

var cleanCss = require('gulp-clean-css');

var cssmin = require('gulp-cssmin');
// ファイルリネーム（.min作成用）
var rename = require('gulp-rename');
// ファイル結合
var concat = require('gulp-concat');
// js最小化
var jsmin = require('gulp-jsmin');
// エラーでも監視を続行させる
var plumber = require('gulp-plumber');


gulp.task('sass_vk-admin', function() {
	// gulp.src( '**/_scss/**/*.scss' )
	gulp.src('vk-admin/package/_scss/**/*.scss')
		.pipe(plumber())
		.pipe(sass())
		.pipe(cmq({
			log: true
		}))
		.pipe(autoprefixer())
		.pipe(cleanCss())
		.pipe(gulp.dest('./vk-admin/package/css/'));
});

gulp.task('sass_vk-mobile-nav', function() {
	// gulp.src( '**/_scss/**/*.scss' )
	gulp.src('vk-mobile-nav/package/_scss/**/*.scss')
		.pipe(plumber())
		.pipe(sass())
		.pipe(cmq({
			log: true
		}))
		.pipe(autoprefixer())
		.pipe(cleanCss())
		.pipe(gulp.dest('./vk-mobile-nav/package/css/'));
});

gulp.task('sass_vk-widget-pr-content', function() {
	// gulp.src( '**/_scss/**/*.scss' )
	gulp.src('vk-widget-pr-content/package/_scss/**/*.scss')
		.pipe(plumber())
		.pipe(sass())
		.pipe(cmq({
			log: true
		}))
		.pipe(autoprefixer())
		.pipe(cleanCss())
		.pipe(gulp.dest('./vk-widget-pr-content/package/css/'));
});


// js最小化
gulp.task('jsmin_prlx', function() {
	gulp.src(['./vk-parallax/vk-prlx.js'])
		.pipe(plumber()) // エラーでも監視を続行
		.pipe(jsmin())
		.pipe(rename({
			suffix: '.min'
		}))
		.pipe(gulp.dest('./vk-parallax/package/js/'));
});
gulp.task('copy_prlx', function() {
	gulp.src('./vk-parallax/package/**')
		.pipe(gulp.dest('./vk-widget-pr-content/js/'));
});


gulp.task('copy_vk-admin', function() {
	gulp.src('./vk-admin/package/**')
		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/plugins_admin/vk-admin/'))
		.pipe(gulp.dest('../plugins/vk-post-author-display/inc/vk-admin/'))
		.pipe(gulp.dest('../plugins/vk-call-to-action/inc/vk-admin/'))
		.pipe(gulp.dest('../plugins/vk-link-target-controller/inc/vk-admin/'))
		.pipe(gulp.dest('../plugins/lightning-skin-variety/inc/vk-admin/'))
		.pipe(gulp.dest('../plugins/lightning-origin-pro/inc/vk-admin/'))
		.pipe(gulp.dest('../plugins/wp-easy-responsive-tabs-to-accordion/vk-admin/'));
});
gulp.task('copy_term-color', function() {
	gulp.src('./term-color/package/**')
		.pipe(gulp.dest('../plugins/vk-post-author-display/inc/term-color/'))
		.pipe(gulp.dest('../plugins/lightning-skin-variety/inc/term-color/'))
		.pipe(gulp.dest('../plugins/lightning-skin-fort/inc/term-color/'))
		.pipe(gulp.dest('../plugins/lightning-skin-pale/inc/term-color/'))
		.pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/inc/term-color/'))
		.pipe(gulp.dest('../themes/lightning-child-rerise/inc/term-color/'))
		.pipe(gulp.dest('../plugins/lightning-origin-pro/inc/term-color/'));
});
gulp.task('copy_header-top', function() {
	gulp.src('./header-top/package/**')
		.pipe(gulp.dest('../plugins/lightning-skin-variety/inc/header-top/'))
		.pipe(gulp.dest('../plugins/lightning-skin-fort/inc/header-top/'))
		.pipe(gulp.dest('../plugins/lightning-skin-pale/inc/header-top/'))
		.pipe(gulp.dest('../plugins/lightning-origin-pro/inc/header-top/'));
});
gulp.task('copy_media-posts', function() {
	gulp.src('./media-posts/package/**')
		.pipe(gulp.dest('../plugins/lightning-skin-variety/inc/media-posts/'))
		.pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/inc/media-posts/'))
		.pipe(gulp.dest('../plugins/lightning-skin-fort/inc/media-posts/'))
		.pipe(gulp.dest('../plugins/lightning-skin-pale/inc/media-posts/'))
		.pipe(gulp.dest('../plugins/lightning-origin-pro/inc/media-posts/'));
	gulp.src('./media-posts/tests/**')
		.pipe(gulp.dest('../plugins/lightning-skin-variety/tests/'))
		.pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/tests/'))
		.pipe(gulp.dest('../plugins/lightning-skin-fort/tests/'))
		.pipe(gulp.dest('../plugins/lightning-skin-pale/tests/'))
		.pipe(gulp.dest('../plugins/lightning-origin-pro/tests/'));
});
// gulp.task( 'copy_font-awesome-selector', function() {
//     gulp.src( './font-awesome-selector/package/**' )
//     .pipe( gulp.dest( '../themes/lightning/library/font-awesome-selector/' ) );
// } );
gulp.task('copy_post-type-manager', function() {
	gulp.src('./post-type-manager/package/**')
		.pipe(gulp.dest('../themes/biz-vektor/plugins/post-type-manager/'))
		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/plugins/post-type-manager/'));
});
gulp.task('copy_custom-field-builder', function() {
	gulp.src('./custom-field-builder/package/**')
		.pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/inc/custom-field-builder/'))
		.pipe(gulp.dest('../plugins/lightning-origin-pro/inc/custom-field-builder/'))
		.pipe(gulp.dest('../themes/bill-vektor/inc/custom-field-builder/'));
});
gulp.task('copy_call-to-action', function() {
	gulp.src('./call-to-action/package/**')
		.pipe(gulp.dest('../plugins/vk-call-to-action/inc/vk-call-to-action-package/'))
		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/plugins/call-to-action/'));
});
gulp.task('copy_vk-mobile-nav', function() {
	gulp.src('./vk-mobile-nav/package/**')
		.pipe(gulp.dest('../plugins/lightning-advanced-unit/inc/vk-mobile-nav/'))
		.pipe(gulp.dest('../plugins/vk-mobile-nav/inc/vk-mobile-nav/'));
});
gulp.task('copy_template-tags', function() {
	gulp.src('./template-tags/package/**')
		.pipe(gulp.dest('../plugins/vk-call-to-action/inc/template-tags/'))
		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/plugins/template-tags/'));
});
gulp.task('copy_page-header', function() {
	gulp.src('./vk-page-header/package/**')
		.pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/inc/vk-page-header/'))
		.pipe(gulp.dest('../plugins/lightning-skin-fort/inc/vk-page-header/'))
		.pipe(gulp.dest('../plugins/lightning-skin-pale/inc/vk-page-header/'))
		.pipe(gulp.dest('../plugins/lightning-origin-pro/inc/vk-page-header/'));
});
gulp.task('copy_widget-pr-content', function() {
	gulp.src('./vk-widget-pr-content/package/**')
		// .pipe( gulp.dest( '../plugins/lightning-skin-jpnstyle/inc/vk-wodget-pr-content/' ) )
		.pipe(gulp.dest('../plugins/lightning-origin-pro/inc/vk-widget-pr-content/'))
		.pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/inc/vk-widget-pr-content/'))
		.pipe(gulp.dest('../plugins/lightning-skin-fort/inc/vk-widget-pr-content/'))
		.pipe(gulp.dest('../plugins/lightning-skin-pale/inc/vk-widget-pr-content/'))
		.pipe(gulp.dest('../plugins/lightning-skin-variety/inc/vk-widget-pr-content/'));
});

// Watch
gulp.task('watch', function() {
	gulp.watch('./vk-admin/package/images/**', ['copy_vk-admin']);
	gulp.watch('./vk-admin/package/css/**', ['copy_vk-admin']);
	gulp.watch('./vk-admin/package/js/**', ['copy_vk-admin']);
	gulp.watch('./vk-admin/package/class.vk-admin.php', ['copy_vk-admin']);
	gulp.watch('./term-color/package/**', ['copy_term-color']);
	gulp.watch('./header-top/package/**', ['copy_header-top']);
	gulp.watch('./header-top/package/css/**', ['copy_header-top']);
	gulp.watch('./media-posts/package/**', ['copy_media-posts']);
	gulp.watch('./post-type-manager/package/**', ['copy_post-type-manager']);
	gulp.watch('./custom-field-builder/package/**', ['copy_custom-field-builder']);
	gulp.watch('./call-to-action/package/**', ['copy_call-to-action']);
	gulp.watch('./vk-mobile-nav/package/**', ['copy_vk-mobile-nav']);

	gulp.watch('./template-tags/package/**', ['copy_template-tags']);
	// gulp.watch('./font-awesome-selector/package/**', ['copy_font-awesome-selector']);
	// gulp.watch('js/*.js', ['scripts']);
	// gulp.watch('vk-admin', ['scripts','scripts_header_fixed']);
});

// Watch
gulp.task('watch_term', function() {
	gulp.watch('./term-color/package/**', ['copy_term-color']);
});
gulp.task('watch_mobile', function() {
	gulp.watch('./vk-mobile-nav/package/_scss/**', ['sass_vk-mobile-nav']);
	gulp.watch('./vk-mobile-nav/package/**', ['copy_vk-mobile-nav']);
});
gulp.task('watch_cf', function() {
	gulp.watch('./custom-field-builder/package/**', ['copy_custom-field-builder']);
});
gulp.task('watch_ptm', function() {
	gulp.watch('./post-type-manager/package/**', ['copy_post-type-manager']);
});
gulp.task('watch_media', function() {
	gulp.watch('./media-posts/tests/**', ['copy_media-posts']);
	gulp.watch('./media-posts/package/**', ['copy_media-posts']);
	gulp.watch('./media-posts/package/**', ['copy_term-color']);
});
gulp.task('watch_admin', function() {
	gulp.watch('./vk-admin/package/_scss/**', ['sass_vk-admin']);
	gulp.watch('./vk-admin/package/**', ['copy_vk-admin']);
});
gulp.task('watch_template-tags', function() {
	gulp.watch('./template-tags/package/**', ['copy_template-tags']);
});
gulp.task('watch_page-header', function() {
	gulp.watch('./vk-page-header/package/**', ['copy_page-header', 'copy_custom-field-builder']);
});
gulp.task('watch_pr-content', function() {
	gulp.watch('./vk-widget-pr-content/package/_scss/**', ['sass_vk-widget-pr-content']);
	gulp.watch('./vk-widget-pr-content/package/**', ['copy_widget-pr-content']);
	gulp.watch('./vk-parallax/package/**', ['jsmin_prlx', 'copy_prlx']);
});
gulp.task('watch_header-top', function() {
	gulp.watch('./header-top/package/**', ['copy_header-top']);
});
gulp.task('watch_cta', function() {
	gulp.watch('./call-to-action/package/**', ['copy_call-to-action']);
});

gulp.task('default', ['watch']);


// ファイル結合
// gulp.task('concat', function() {
// 	// return gulp.src(['./js/lightning-adv-common.js','./inc/navigation/js/navigation.js','./inc/sidebar-fix/js/sidebar-fix.js'])
// 	return gulp.src(['./js/lightning-adv-common.js', './inc/sidebar-fix/js/sidebar-fix.js'])
// 		.pipe(concat('lightning-adv.js'))
// 		.pipe(gulp.dest('./js/'));
// });