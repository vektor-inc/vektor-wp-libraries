
/*-------------------------------------*/
/*	Copyright
/*-------------------------------------*/
/*	Font
/*-------------------------------------*/
/*	media posts
/*-------------------------------------*/
/*	term color
/*-------------------------------------*/
/*	Font Awesome
/*-------------------------------------*/
/*	vk-mobile-nav
/*-------------------------------------*/
/*	vk-mobile-fix-nav
/*-------------------------------------*/
/*	page-header
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


/*-------------------------------------*/
/*	Watch
/*-------------------------------------*/
gulp.task('copy_custom-field-builder', function () {
  gulp.src('./custom-field-builder/package/**')
    .pipe(gulp.dest('../plugins/vk-google-job-posting-manager/inc/custom-field-builder/package/'))
    .pipe(gulp.dest('../plugins/vk-version-table/inc/custom-field-builder/package/'))
    .pipe(gulp.dest('../themes/bill-vektor/inc/custom-field-builder/'))
    .pipe(gulp.dest('../themes/lightning-pro/inc/custom-field-builder/package/'));
});
gulp.task('watch_cf', function () {
    gulp.watch('./custom-field-builder/package/**', gulp.task('copy_custom-field-builder'));
});
gulp.task('watch_ptm', function () {
    gulp.watch('./post-type-manager/package/**', gulp.task('copy_post-type-manager'));
});

gulp.task('watch_full-title', function () {
    gulp.watch('./vk-widget-full-wide-title/**', gulp.task('copy_full-title'));
});

// gulp.task('default', gulp.task('watch'));


/*-------------------------------------*/
/*	Template Tags
/*-------------------------------------*/
gulp.task('c_tags', function(done) {
    gulp.src('./template-tags/package/**')
        .pipe(gulp.dest('../themes/katawara/inc/template-tags/'))
		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/inc/template-tags/package/'))
		.pipe(gulp.dest('../plugins/vk-post-author-display/inc/template-tags/package/'));
    gulp.src('./template-tags/tests/**')
        .pipe(gulp.dest('../themes/katawara/inc/tests/'))
		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/tests/'))
        .pipe(gulp.dest('../plugins/vk-post-author-display/tests/'));
    done();
});
gulp.task('w_tags', function (done) {
    gulp.watch('./template-tags/package/**', gulp.task('c_tags'));
    done();
});

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
		.pipe(gulp.dest('../plugins/vk-plugin-beta-tester/inc/vk-admin/package/'))
		.pipe(gulp.dest('../plugins/wp-easy-responsive-tabs-to-accordion/vk-admin/'));
});
gulp.task('watch_admin', function () {
    gulp.watch('./vk-admin/package/_scss/**', gulp.task('sass_vk-admin'));
    gulp.watch('./vk-admin/package/**', gulp.task('copy_vk-admin'));
});

/*-------------------------------------*/
/*	Copyright
/*-------------------------------------*/
gulp.task('copy_copyright-custom', function () {
	gulp.src('./copyright-customizer/package/**')
		.pipe(gulp.dest('../plugins/lightning_copyright_customizer/inc/copyright-customizer/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/copyright-customizer/package/'));
});
gulp.task('watch_copyright', function () {
    gulp.watch('./copyright-customizer/package/**', gulp.task('copy_copyright-custom'));
});

/*-------------------------------------*/
/*	components
/*-------------------------------------*/
gulp.task('sass_compo', function (done) {
    // gulp.src( '**/_scss/**/*.scss' )
    gulp.src('vk-components/package/_scss/*.scss')
        .pipe(plumber())
        .pipe(sass())
        .pipe(cmq({
            log: true
        }))
        .pipe(autoprefixer())
        .pipe(cleanCss())
        .pipe(gulp.dest('./vk-components/package/css/'));
        done();
});
gulp.task('copy_compo', function (done) {
    gulp.src('./vk-components/package/**')
        .pipe(gulp.dest('../themes/katawara/inc/vk-components/package/'))
		.pipe(gulp.dest('../themes/lightning/inc/vk-components/package/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-components/package/'))
        .pipe(gulp.dest('../plugins/vk-blocks-pro/inc/vk-components/package/'));
        done();
});
gulp.task('watch_compo', function () {
    gulp.watch('vk-components/package/**', gulp.series('copy_compo'));
    gulp.watch('vk-components/package/_scss', gulp.series('sass_compo'));
});
/*-------------------------------------*/
/*	media posts
/*-------------------------------------*/
gulp.task('c_mp', function (done) {
    gulp.src('./vk-media-posts/package/**')
        .pipe(gulp.dest('../themes/katawara/inc/vk-media-posts/package/'));
        done();
});
gulp.task('w_mp', function (done) {
    gulp.watch('vk-media-posts/package/**', gulp.task('c_mp'));
    done();
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
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-font-selector/package/'));
});
gulp.task('watch_font', function () {
		gulp.watch('./vk-font-selector/package/**', gulp.task('copy_font-selector'));
    gulp.watch('./vk-font-selector/package/_scss/**', gulp.task('sass_vk-font-selector'));
});

/*-------------------------------------*/
/*	term color
/*-------------------------------------*/
gulp.task('copy_term-color', function() {
	gulp.src('./term-color/package/**')
		.pipe(gulp.dest('../plugins/vk-post-author-display/inc/term-color/'))
		.pipe(gulp.dest('../plugins/vk-blocks/inc/term-color/package/'))
		.pipe(gulp.dest('../themes/lightning-child-rerise/inc/term-color/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/term-color/package/'))
		.pipe(gulp.dest('../themes/lightning/inc/term-color/package/'))
});
gulp.task('watch_term', function () {
    gulp.watch('./term-color/package/**', gulp.task('copy_term-color'));
});

/*-------------------------------------*/
/*	Font Awesome
/*-------------------------------------*/
gulp.task('copy_font-awesome', function (done) {
    gulp.src('./font-awesome/package/**')
        .pipe(gulp.dest('../themes/lightning/inc/font-awesome/package'))
        .pipe(gulp.dest('../themes/lightning-pro/inc/font-awesome/package'))
        .pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/inc/font-awesome/package/'))
        .pipe(gulp.dest('../plugins/vk-blocks/inc/font-awesome/package'))
        .pipe(gulp.dest('../plugins/vk-blocks-pro/inc/font-awesome/package'))
        .pipe(gulp.dest('../plugins/vk-post-author-display/inc/font-awesome/'));
        done();
});
gulp.task('watch_fa', function () {
    gulp.watch('./font-awesome/package/**', gulp.task('copy_font-awesome'));
});

/*-------------------------------------*/
/*	vk-mobile-nav
/*-------------------------------------*/
gulp.task('sass_vk-mobile-nav', function (done) {
    // gulp.src( '**/_scss/**/*.scss' )
    gulp.src('vk-mobile-nav/package/_scss/*.scss')
        .pipe(plumber())
        .pipe(sass())
        .pipe(cmq({
            log: true
        }))
        .pipe(autoprefixer())
        .pipe(cleanCss())
        .pipe(gulp.dest('./vk-mobile-nav/package/css/'));
        done();
});
// js最小化
gulp.task('jsmin_vk-mobile-nav', function (done) {
    gulp.src(['./vk-mobile-nav/package/js/vk-mobile-nav.js'])
        .pipe(plumber()) // エラーでも監視を続行
        .pipe(jsmin())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./vk-mobile-nav/package/js/'));
        done();
});
gulp.task('copy_vk-mobile-nav', function (done) {
	gulp.src('./vk-mobile-nav/package/**')
		.pipe(gulp.dest('../themes/lightning/inc/vk-mobile-nav/package/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-mobile-nav/package/'))
		// .pipe(gulp.dest('../plugins/lightning-advanced-unit/inc/vk-mobile-nav/'))
		// .pipe(gulp.dest('../plugins/lightning-pro/inc/vk-mobile-nav/'))
		.pipe(gulp.dest('../plugins/vk-mobile-nav/inc/vk-mobile-nav/'))
        .pipe(gulp.dest('../themes/seizen-souzoku/inc/vk-mobile-nav/'));
        done();
});
gulp.task('watch_mobile', function () {
    gulp.watch('./vk-mobile-nav/package/_scss/**', gulp.series('sass_vk-mobile-nav','copy_vk-mobile-nav'));
    gulp.watch('./vk-mobile-nav/package/js/vk-mobile-nav.js', gulp.series('jsmin_vk-mobile-nav','copy_vk-mobile-nav'));
    // gulp.watch('./vk-mobile-nav/package/**', gulp.series('sass_vk-mobile-nav','jsmin_vk-mobile-nav','copy_vk-mobile-nav'));
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
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-mobile-fix-nav/package/'))
		.pipe(gulp.dest('../themes/souzoku/inc/vk-mobile-fix-nav/'))
		.pipe(gulp.dest('../themes/seizen-souzoku/inc/vk-mobile-fix-nav/'))
});
gulp.task('watch_mobile-fix', function() {
	gulp.watch('./vk-mobile-fix-nav/package/_scss/**', gulp.task('sass_vk-mobile-fix-nav'));
	gulp.watch('./vk-mobile-fix-nav/package/**', gulp.task('copy_vk-mobile-fix-nav'));
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
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-page-header/package/'));
});
gulp.task('watch_page-header', function () {
    gulp.watch('./vk-page-header/package/**', gulp.task('copy_page-header', 'copy_custom-field-builder'));
});
