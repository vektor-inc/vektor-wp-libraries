
/*-------------------------------------*/
/*	Copyright
/*-------------------------------------*/
/*	Font
/*-------------------------------------*/
/*	media posts
/*-------------------------------------*/
/*	term color
/*-------------------------------------*/
/*	header top
/*-------------------------------------*/
/*	Font Awesome
/*-------------------------------------*/
/*	vk-mobile-nav
/*-------------------------------------*/
/*	vk-mobile-fix-nav
/*-------------------------------------*/
/*	page-header
/*-------------------------------------*/
/*	widget pr content
/*-------------------------------------*/


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








/*-------------------------------------*/
/*	sass
/*-------------------------------------*/

// js最小化
gulp.task('jsmin_jslibs', function () {
    gulp.src(['./js-libraries/vk-prlx.js', './js-libraries/admin-widget-color.js'])
        .pipe(plumber()) // エラーでも監視を続行
        .pipe(jsmin())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./js-libraries/'));
});
gulp.task('copy_jslibs', function () {
    gulp.src('./js-libraries/vk-prlx.min.js')
        .pipe(gulp.dest('./vk-widget-pr-content/package/js/'));
    gulp.src('./js-libraries/admin-widget-color.min.js')
        .pipe(gulp.dest('./vk-widget-pr-content/package/js/'));
});

// parallax
gulp.task('parallax', function () {
    gulp.watch('./js-libraries/vk-prlx.js', function () {
        gulp.src(['./js-libraries/vk-prlx.js'])
            .pipe(plumber()) // エラーでも監視を続行
            .pipe(jsmin())
            .pipe(rename({
                suffix: '.min'
            }))
            .pipe(gulp.dest('./js-libraries/'))
						.pipe(concat('_vk-prlx.min.js'))
            .pipe(gulp.dest('../themes/lightning/assets/js/'))
            .pipe(gulp.dest('../themes/lightning-pro/assets/js/'));
    });
});

/*-------------------------------------*/
/*	copy
/*-------------------------------------*/
gulp.task('copy_post-type-manager', function() {
	gulp.src('./post-type-manager/package/**')
		.pipe(gulp.dest('../themes/biz-vektor/plugins/post-type-manager/'))
		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/plugins/post-type-manager/package/'));
});

// gulp.task('copy_call-to-action', function() {
// 	gulp.src('./call-to-action/package/**')
// 		.pipe(gulp.dest('../plugins/vk-call-to-action/inc/vk-call-to-action-package/'))
// 		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/plugins/call-to-action/package/'));
// });

gulp.task('copy_template-tags', function() {
	gulp.src('./template-tags/package/**')
		// .pipe(gulp.dest('../plugins/vk-call-to-action/inc/template-tags/'))
		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/plugins/template-tags/'));
});

/*-------------------------------------*/
/*	Watch
/*-------------------------------------*/
gulp.task('copy_custom-field-builder', function () {
  gulp.src('./custom-field-builder/package/**')
    .pipe(gulp.dest('../plugins/vk-google-job-posting-manager/inc/custom-field-builder/package/'))
    .pipe(gulp.dest('../plugins/vk-version-table/inc/custom-field-builder/package/'))
    .pipe(gulp.dest('../themes/bill-vektor/inc/custom-field-builder/'))
    .pipe(gulp.dest('../themes/lightning-pro/inc/custom-field-builder/'));
});
gulp.task('watch_cf', function () {
    gulp.watch('./custom-field-builder/package/**', ['copy_custom-field-builder']);
});
gulp.task('watch_ptm', function () {
    gulp.watch('./post-type-manager/package/**', ['copy_post-type-manager']);
});


gulp.task('watch_template-tags', function () {
    gulp.watch('./template-tags/package/**', ['copy_template-tags']);
});


// gulp.task('watch_cta', function () {
//     gulp.watch('./call-to-action/package/**', ['copy_call-to-action']);
// });



gulp.task('watch_full-title', function () {
    gulp.watch('./vk-widget-full-wide-title/**', ['copy_full-title']);
});

// gulp.task('default', ['watch']);

/*-------------------------------------*/
/*	vk admin
/*-------------------------------------*/
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
gulp.task('copy_vk-admin', function() {
	gulp.src('./vk-admin/package/**')
		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/admin/vk-admin/package/'))
		.pipe(gulp.dest('../plugins/vk-post-author-display/inc/vk-admin/'))
		// .pipe(gulp.dest('../plugins/vk-call-to-action/inc/vk-admin/'))
		.pipe(gulp.dest('../plugins/vk-link-target-controller/inc/vk-admin/'))
		.pipe(gulp.dest('../plugins/wp-easy-responsive-tabs-to-accordion/vk-admin/'));
});
gulp.task('watch_admin', function () {
    gulp.watch('./vk-admin/package/_scss/**', ['sass_vk-admin']);
    gulp.watch('./vk-admin/package/**', ['copy_vk-admin']);
});

/*-------------------------------------*/
/*	Copyright
/*-------------------------------------*/
gulp.task('copy_copyright-custom', function () {
	gulp.src('./copyright-customizer/package/**')
		.pipe(gulp.dest('../plugins/lightning_copyright_customizer/inc/copyright-customizer/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/copyright-customizer/'));
});
gulp.task('watch_copyright', function () {
    gulp.watch('./copyright-customizer/package/**', ['copy_copyright-custom']);
});
/*-------------------------------------*/
/*	Font
/*-------------------------------------*/
gulp.task('sass_vk-font-selector', function () {
    // gulp.src( '**/_scss/**/*.scss' )
    gulp.src('vk-font-selector/package/_scss/**/*.scss')
        .pipe(plumber())
        .pipe(sass())
        .pipe(cmq({
            log: true
        }))
        .pipe(autoprefixer())
        .pipe(cleanCss())
        .pipe(gulp.dest('./vk-font-selector/package/css/'));
});
gulp.task('copy_font-selector', function () {
	gulp.src('./vk-font-selector/package/**')
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-font-selector/'));
});
gulp.task('watch_font', function () {
		gulp.watch('./vk-font-selector/package/**', ['copy_font-selector']);
    gulp.watch('./vk-font-selector/package/_scss/**', ['sass_vk-font-selector']);
});

/*-------------------------------------*/
/*	media posts
/*-------------------------------------*/
gulp.task('sass_media-posts', function () {
	gulp.src('media-posts/package/_scss/**/*.scss')
		.pipe(plumber())
		.pipe(sass())
		.pipe(cmq({
		log: true
		}))
		.pipe(autoprefixer())
		.pipe(cleanCss())
		.pipe(gulp.dest('./media-posts/package/css/'));
});
gulp.task('copy_media-posts', function () {
    gulp.src('./media-posts/package/**')
        // .pipe(gulp.dest('../plugins/lightning-skin-variety/inc/media-posts/'))
        // .pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/inc/media-posts/'))
        // .pipe(gulp.dest('../plugins/lightning-skin-fort/inc/media-posts/'))
        // .pipe(gulp.dest('../plugins/lightning-skin-pale/inc/media-posts/'))
        // .pipe(gulp.dest('../plugins/lightning-origin-pro/inc/media-posts/'))
        .pipe(gulp.dest('../plugins/lightning-pro/inc/media-posts/'))
        .pipe(gulp.dest('../themes/lightning-pro/inc/media-posts/'));
    gulp.src('./media-posts/tests/**')
        // .pipe(gulp.dest('../plugins/lightning-skin-variety/tests/'))
        // .pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/tests/'))
        // .pipe(gulp.dest('../plugins/lightning-skin-fort/tests/'))
        // .pipe(gulp.dest('../plugins/lightning-skin-pale/tests/'))
        // .pipe(gulp.dest('../plugins/lightning-origin-pro/tests/'))
        .pipe(gulp.dest('../plugins/lightning-pro/tests/'))
        .pipe(gulp.dest('../themes/lightning-pro/tests/'));
});
gulp.task('watch_media', function () {
		gulp.watch('./media-posts/package/_scss/**', ['sass_media-posts']);
    gulp.watch('./media-posts/tests/**', ['copy_media-posts']);
    gulp.watch('./media-posts/package/**', ['copy_media-posts']);
    gulp.watch('./media-posts/package/**', ['copy_term-color']);
});

/*-------------------------------------*/
/*	term color
/*-------------------------------------*/
gulp.task('copy_term-color', function() {
	gulp.src('./term-color/package/**')
		.pipe(gulp.dest('../plugins/vk-post-author-display/inc/term-color/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-variety/inc/term-color/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-fort/inc/term-color/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-pale/inc/term-color/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/inc/term-color/'))
		.pipe(gulp.dest('../themes/lightning-child-rerise/inc/term-color/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/term-color/'))
		// .pipe(gulp.dest('../plugins/lightning-origin-pro/inc/term-color/'));
});
gulp.task('watch_term', function () {
    gulp.watch('./term-color/package/**', ['copy_term-color']);
});

/*-------------------------------------*/
/*	header top
/*-------------------------------------*/
gulp.task('sass_header-top', function () {
	gulp.src('header-top/package/_scss/**/*.scss')
		.pipe(plumber())
		.pipe(sass())
		.pipe(cmq({
		log: true
		}))
		.pipe(autoprefixer())
		.pipe(cleanCss())
		.pipe(gulp.dest('./header-top/package/css/'));
});
gulp.task('copy_header-top', function() {
	gulp.src('./header-top/package/**')
		// .pipe(gulp.dest('../plugins/lightning-skin-variety/inc/header-top/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-fort/inc/header-top/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-pale/inc/header-top/'))
		// .pipe(gulp.dest('../plugins/lightning-origin-pro/inc/header-top/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/header-top/'));
});
gulp.task('watch_header-top', function () {
	gulp.watch('./header-top/package/_scss/**', ['sass_header-top']);
  gulp.watch('./header-top/package/**', ['copy_header-top']);
});


/*-------------------------------------*/
/*	Font Awesome
/*-------------------------------------*/
gulp.task('copy_font-awesome', function () {
    gulp.src('./font-awesome/package/**')
        .pipe(gulp.dest('../themes/lightning/inc/font-awesome/'))
        .pipe(gulp.dest('../themes/lightning-pro/inc/font-awesome/'))
        .pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/plugins/font-awesome/package/'))
        .pipe(gulp.dest('../plugins/vk-blocks/inc/font-awesome/'))
        .pipe(gulp.dest('../plugins/vk-post-author-display/inc/font-awesome/'));
});
gulp.task('watch_fa', function () {
    gulp.watch('./font-awesome/package/**', ['copy_font-awesome']);
});

/*-------------------------------------*/
/*	vk-mobile-nav
/*-------------------------------------*/
gulp.task('sass_vk-mobile-nav', function () {
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
// js最小化
gulp.task('jsmin_vk-mobile-nav', function () {
    gulp.src(['./vk-mobile-nav/package/js/vk-mobile-nav.js'])
        .pipe(plumber()) // エラーでも監視を続行
        .pipe(jsmin())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./vk-mobile-nav/package/js/'));
});
gulp.task('copy_vk-mobile-nav', function () {
	gulp.src('./vk-mobile-nav/package/**')
		.pipe(gulp.dest('../themes/lightning/inc/vk-mobile-nav/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-mobile-nav/'))
		// .pipe(gulp.dest('../plugins/lightning-advanced-unit/inc/vk-mobile-nav/'))
		// .pipe(gulp.dest('../plugins/lightning-pro/inc/vk-mobile-nav/'))
		.pipe(gulp.dest('../plugins/vk-mobile-nav/inc/vk-mobile-nav/'))
		.pipe(gulp.dest('../themes/seizen-souzoku/inc/vk-mobile-nav/'))
});
gulp.task('watch_mobile', function () {
    gulp.watch('./vk-mobile-nav/package/_scss/**', ['sass_vk-mobile-nav']);
    gulp.watch('./vk-mobile-nav/package/js/**', ['jsmin_vk-mobile-nav']);
    gulp.watch('./vk-mobile-nav/package/**', ['copy_vk-mobile-nav']);
});

/*-------------------------------------*/
/*	vk-mobile-fix-nav
/*-------------------------------------*/
gulp.task('sass_vk-mobile-fix-nav', function() {
	gulp.src('vk-mobile-fix-nav/package/_scss/**/*.scss')
		.pipe(plumber())
		.pipe(sass())
		.pipe(cmq({
			log: true
		}))
		.pipe(autoprefixer())
		.pipe(cleanCss())
		.pipe(gulp.dest('./vk-mobile-fix-nav/package/css/'));
});
gulp.task('copy_vk-mobile-fix-nav', function() {
	gulp.src('./vk-mobile-fix-nav/package/**')
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-mobile-fix-nav/'))
		.pipe(gulp.dest('../themes/souzoku/inc/vk-mobile-fix-nav/'))
		.pipe(gulp.dest('../themes/seizen-souzoku/inc/vk-mobile-fix-nav/'))
});
gulp.task('watch_mobile-fix', function() {
	gulp.watch('./vk-mobile-fix-nav/package/_scss/**', ['sass_vk-mobile-fix-nav']);
	gulp.watch('./vk-mobile-fix-nav/package/**', ['copy_vk-mobile-fix-nav']);
});

/*-------------------------------------*/
/*	page-header
/*-------------------------------------*/
gulp.task('copy_page-header', function () {
	gulp.src('./vk-page-header/package/**')
		// .pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/inc/vk-page-header/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-fort/inc/vk-page-header/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-pale/inc/vk-page-header/'))
		// .pipe(gulp.dest('../plugins/lightning-origin-pro/inc/vk-page-header/'))
		// .pipe(gulp.dest('../plugins/lightning-pro/inc/vk-page-header/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-page-header/'));
});
gulp.task('watch_page-header', function () {
    gulp.watch('./vk-page-header/package/**', ['copy_page-header', 'copy_custom-field-builder']);
});

/*-------------------------------------*/
/*	widget pr content
/*-------------------------------------*/
gulp.task('sass_vk-widget-pr-content', function () {
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
gulp.task('copy_widget-pr-content', function () {
	gulp.src('./vk-widget-pr-content/package/**')
		// .pipe(gulp.dest('../plugins/lightning-origin-pro/inc/vk-widget-pr-content/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/inc/vk-widget-pr-content/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-fort/inc/vk-widget-pr-content/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-pale/inc/vk-widget-pr-content/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-variety/inc/vk-widget-pr-content/'))
		.pipe(gulp.dest('../plugins/lightning-pro/inc/vk-widget-pr-content/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-widget-pr-content/'));
	gulp.src('./vk-widget-pr-content/tests/**')
		// .pipe(gulp.dest('../plugins/lightning-skin-variety/tests/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-jpnstyle/tests/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-fort/tests/'))
		// .pipe(gulp.dest('../plugins/lightning-skin-pale/tests/'))
		// .pipe(gulp.dest('../plugins/lightning-origin-pro/tests/'))
		.pipe(gulp.dest('../plugins/lightning-pro/tests/'))
		.pipe(gulp.dest('../themes/lightning-pro/tests/'));
});
gulp.task('watch_pr-content', function () {
    gulp.watch('./vk-widget-pr-content/tests/**', ['copy_widget-pr-content']);
    gulp.watch('./vk-widget-pr-content/package/_scss/**', ['sass_vk-widget-pr-content']);
    // gulp.watch('./vk-widget-pr-content/package/**', ['copy_widget-pr-content', 'jsmin_jslibs', 'copy_jslibs']);
    gulp.watch('./vk-widget-pr-content/package/**', ['copy_widget-pr-content']);
    gulp.watch('./js-libraries/**', ['jsmin_jslibs', 'copy_jslibs']);
});
