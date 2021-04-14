
/*-------------------------------------*/
/*  Copyright
/*-------------------------------------*/
/*  Font
/*-------------------------------------*/
/*  media posts
/*-------------------------------------*/
/*  term color
/*-------------------------------------*/
/*  Font Awesome
/*-------------------------------------*/
/*  vk-mobile-nav
/*-------------------------------------*/
/*  vk-mobile-fix-nav
/*-------------------------------------*/
/*  page-header
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
var uglify = require('gulp-uglify');
// エラーでも監視を続行させる
var plumber = require('gulp-plumber');
var replace = require("gulp-replace");

var babel = require('gulp-babel');

/*-------------------------------------*/
/*  sass
/*-------------------------------------*/

// js最小化
gulp.task('jsmin_jslibs', function () {
  return gulp.src(['./js-libraries/vk-prlx.js', './js-libraries/admin-widget-color.js'])
    .pipe(plumber())
    .pipe(jsmin())
    .pipe(rename(
      {
        suffix: '.min'
      }
    ))
    .pipe(gulp.dest('./js-libraries/'))
});
gulp.task('copy_jslibs', function (done) {
  gulp.src('./js-libraries/vk-prlx.min.js')
    .pipe(gulp.dest('./vk-widget-pr-content/package/js/'))
  gulp.src('./js-libraries/admin-widget-color.min.js')
    .pipe(gulp.dest('./vk-widget-pr-content/package/js/'))
  done()
});

// parallax
gulp.task('parallax', function () {
  return gulp.src(['./js-libraries/vk-prlx.js'])
    .pipe(plumber())
    .pipe(jsmin())
    .pipe(rename(
      {
        suffix: '.min'
      }
    ))
    .pipe(gulp.dest('./js-libraries/'))
    .pipe(concat('_vk-prlx.min.js'))
    .pipe(gulp.dest('../themes/lightning/assets/js/'))
    .pipe(gulp.dest('../themes/lightning-pro/assets/js/'));
});

/*-------------------------------------*/
/*  copy
/*-------------------------------------*/
gulp.task('copy_post-type-manager', function(done) {
  gulp.src('./post-type-manager/package/**')
    .pipe(gulp.dest('../themes/biz-vektor/plugins/post-type-manager/'))
    .pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/inc/post-type-manager/package/'));
    done();
});

// gulp.task('copy_call-to-action', function() {
//  gulp.src('./call-to-action/package/**')
//    .pipe(gulp.dest('../plugins/vk-call-to-action/inc/vk-call-to-action-package/'))
//    .pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/plugins/call-to-action/package/'));
// });


/*-------------------------------------*/
/*  Watch
/*-------------------------------------*/
gulp.task('copy_custom-field-builder', function (done) {
  gulp.src('./custom-field-builder/package/**')
	.pipe(gulp.dest('../plugins/vk-google-job-posting-manager/inc/custom-field-builder/package/'))
	.pipe(gulp.dest('../plugins/vk-version-table/inc/custom-field-builder/package/'))
	.pipe(gulp.dest('../themes/bill-vektor/inc/custom-field-builder/'))
	.pipe(gulp.dest('../themes/katawara/inc/custom-field-builder/package/'))
	.pipe(gulp.dest('../themes/lightning-pro/inc/custom-field-builder/package/'))
	.pipe(gulp.dest('../plugins/lightning-g3-pro-unit/inc/custom-field-builder/package/'));
  done();
});
gulp.task('watch_cf', function (done) {
    gulp.watch('./custom-field-builder/package/**', gulp.task('copy_custom-field-builder'));
    done();
});
gulp.task('watch_ptm', function () {
    gulp.watch('./post-type-manager/package/**', gulp.task('copy_post-type-manager'));
});

gulp.task('watch_full-title', function () {
    gulp.watch('./vk-widget-full-wide-title/**', gulp.task('copy_full-title'));
});

// gulp.task('default', gulp.task('watch'));


/*-------------------------------------*/
/*  Template Tags
/*-------------------------------------*/
gulp.task('c_tags', function(done) {
	gulp.src('./template-tags/package/**')
		.pipe(gulp.dest('../themes/katawara/inc/template-tags/'))
		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/inc/template-tags/package/'))
		.pipe(gulp.dest('../plugins/vk-blocks-pro/inc/template-tags/package/'))
		.pipe(gulp.dest('../plugins/vk-google-job-posting-manager/inc/template-tags/package/'))
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
/*  vk admin
/*-------------------------------------*/
gulp.task('sass_vk-admin', function(done) {
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
        done();
});
gulp.task('copy_vk-admin', function(done) {
  gulp.src('./vk-admin/package/**')
    .pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/admin/vk-admin/package/'))
    .pipe(gulp.dest('../plugins/vk-post-author-display/inc/vk-admin/package/'))
    .pipe(gulp.dest('../plugins/vk-legacy-notice/inc/vk-admin/package/'))
    .pipe(gulp.dest('../plugins/vk-blocks-pro/inc/vk-admin/package/'))
    // .pipe(gulp.dest('../plugins/vk-call-to-action/inc/vk-admin/'))
    .pipe(gulp.dest('../plugins/vk-link-target-controller/inc/vk-admin/package/'))
    .pipe(gulp.dest('../plugins/vk-plugin-beta-tester/inc/vk-admin/package/'))
    //.pipe(gulp.dest('../plugins/wp-easy-responsive-tabs-to-accordion/vk-admin/'))
	.pipe(gulp.dest('../plugins/lightning-g3-pro-unit/inc/vk-admin/package/'));
done();
});
gulp.task('watch_admin', function (done) {
    gulp.watch('./vk-admin/package/_scss/**', gulp.task('sass_vk-admin'));
    gulp.watch('./vk-admin/package/**', gulp.task('copy_vk-admin'));
    done();
});

/*-------------------------------------*/
/*  Copyright
/*-------------------------------------*/
gulp.task('copy_copyright-custom', function () {
    gulp.src('./copyright-customizer/package/**')
        .pipe(gulp.dest('../themes/katawara/inc/copyright-customizer/package/'))
        .pipe(gulp.dest('../plugins/lightning_copyright_customizer/inc/copyright-customizer/'))
    	.pipe(gulp.dest('../themes/lightning-pro/inc/copyright-customizer/package/'))
		.pipe(gulp.dest('../plugins/lightning-g3-pro-unit/inc/copyright-customizer/package/'));
});
gulp.task('watch_copyright', function () {
    gulp.watch('./copyright-customizer/package/**', gulp.task('copy_copyright-custom'));
});

/*-------------------------------------*/
/*  css optimize
/*-------------------------------------*/
gulp.task('copy_css-optimize', function (done) {
  gulp.src('./vk-css-optimize/package/**')
      .pipe(gulp.dest('../themes/lightning/inc/vk-css-optimize/package/'))
      // .pipe(gulp.dest('../themes/lightning-pro/inc/vk-css-optimize/package/'))
      .pipe(gulp.dest('../themes/katawara/inc/vk-css-optimize/package/'))
      // .pipe(gulp.dest('../plugins/vk-blocks-pro/inc/vk-css-optimize/package/'))
      .pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/inc/vk-css-optimize/package/'));
      done();
});
gulp.task('watch_css', function () {
  gulp.watch('./vk-css-optimize/package/**', gulp.task('copy_css-optimize'));
});

/*-------------------------------------*/
/*  components
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
        .pipe(gulp.dest('../themes/lightning/_g3/inc/vk-components/package/'))
        .pipe(gulp.dest('../themes/lightning-pro/inc/vk-components/package/'))
		.pipe(gulp.dest('../plugins/_vk-blocks-pro-v0/inc/vk-components/package/'))
        .pipe(gulp.dest('../plugins/vk-blocks-pro/inc/vk-components/package/'));
    done();
});
gulp.task('watch_compo', function () {
    gulp.watch('vk-components/package/**', gulp.series('copy_compo'));
    gulp.watch('vk-components/package/_scss', gulp.series('sass_compo'));
});

/*-------------------------------------*/
/*  page-header
/*-------------------------------------*/
gulp.task('copy_page-header', function (done) {
	gulp.src('./vk-page-header/package/**')
		.pipe(gulp.dest('../themes/katawara/inc/vk-page-header/package/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-page-header/package/'))
	done();
});
gulp.task('watch_page-header', function () {
  // gulp.watch('./vk-page-header/package/**', gulp.series('copy_page-header', 'copy_custom-field-builder'));
  gulp.watch('./vk-page-header/package/**', gulp.series('copy_page-header'));
});

/*-------------------------------------*/
/*  media posts
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

/**
 * Media Posts BS4
 */
gulp.task('w_mp4', function (done) {
  gulp.watch('media-posts-bs4/package/**', gulp.task('copy_media-posts-bs4'));
  done();
});
gulp.task('copy_media-posts-bs4', function (done) {
    gulp.src('./media-posts-bs4/package/**')
		.pipe(gulp.dest('../themes/katawara/inc/media-posts-bs4/package/'))
		.pipe(gulp.dest('../themes/lightning-pro/inc/media-posts-bs4/package/'))
		.pipe(gulp.dest('../plugins/lightning-g3-pro-unit/inc/media-posts-bs4/package/'))
    done();
});

/**
 * Headding Design
 */
 gulp.task('copy_heading-design', function (done) {
	// Lightning Pro
	gulp.src('./inc/vk-heading-design/package/**')
		.pipe(replace("'heading_design_textdomain'", "'lightning-pro'"))
		.pipe(gulp.dest('../themes/lightning-pro/inc/vk-heading-design/package/'))
	// Katawara
	gulp.src('./inc/vk-heading-design/package/**')
		.pipe(replace("'heading_design_textdomain'", "'katawara'"))
		.pipe(gulp.dest('../themes/katawara/inc/vk-heading-design/package/'))
	// Lightning G3 Pro Unit
	gulp.src('./inc/vk-heading-design/package/**')
		.pipe(replace("'heading_design_textdomain'", "'lightning-g3-pro-unit'"))
		.pipe(gulp.dest('../plugins/lightning-g3-pro-unit/inc/vk-heading-design/package/'))
    done();
});

/*-------------------------------------*/
/*  Font
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
        .pipe(gulp.dest('../themes/katawara/inc/vk-font-selector/package/'))
    	.pipe(gulp.dest('../themes/lightning-pro/inc/vk-font-selector/package/'))
		.pipe(gulp.dest('../plugins/lightning-g3-pro-unit/inc/vk-font-selector/package/'));
});
gulp.task('watch_font', function () {
    gulp.watch('./vk-font-selector/package/**', gulp.task('copy_font-selector'));
    gulp.watch('./vk-font-selector/package/_scss/**', gulp.task('sass_vk-font-selector'));
});

/*-------------------------------------*/
/*  term color
/*-------------------------------------*/
gulp.task('copy_term-color', function(done) {
  gulp.src('./term-color/package/**')
    .pipe(gulp.dest('../plugins/vk-post-author-display/inc/term-color/'))
    .pipe(gulp.dest('../plugins/vk-blocks/inc/term-color/package/'))
	.pipe(gulp.dest('../plugins/vk-blocks-pro/inc/term-color/package/'))
	.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/inc/term-color/package/'))
	.pipe(gulp.dest('../themes/katawara/inc/term-color/package/'))
	.pipe(gulp.dest('../themes/lightning-child-rerise/inc/term-color/'))
    .pipe(gulp.dest('../themes/lightning-pro/inc/term-color/package/'))
    .pipe(gulp.dest('../themes/lightning/inc/term-color/package/'));
    done();
});
gulp.task('watch_term', function () {
    gulp.watch('./term-color/package/**', gulp.task('copy_term-color'));
});

/*-------------------------------------*/
/*  Font Awesome
/*-------------------------------------*/
gulp.task('copy_fa', function (done) {
    gulp.src('./font-awesome/package/**')
        .pipe(gulp.dest('../themes/lightning/inc/font-awesome/package'))
        .pipe(gulp.dest('../themes/lightning-pro/inc/font-awesome/package'))
        .pipe(gulp.dest('../themes/katawara/inc/font-awesome/package'))


        .pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/inc/font-awesome/package/'))
        .pipe(gulp.dest('../plugins/vk-blocks/inc/font-awesome/package'))
        .pipe(gulp.dest('../plugins/vk-blocks-pro/inc/font-awesome/package'))
        .pipe(gulp.dest('../plugins/vk-post-author-display/inc/font-awesome/package'));
        done();
});
gulp.task('watch_fa', function () {
    gulp.watch('./font-awesome/package/**', gulp.task('copy_fa'));
});

/**
 * Widget Area Setting.
 */
gulp.task('copy_vk-footer-customize', function (done) {
    gulp.src('./vk-footer-customize/package/**')
        .pipe(gulp.dest('../themes/lightning/inc/vk-footer-customize/package'))
        .pipe(gulp.dest('../themes/lightning-pro/inc/vk-footer-customize/package'))
        .pipe(gulp.dest('../themes/katawara/inc/vk-footer-customize/package'));
        done();
});
gulp.task('watch_vk-footer-customize', function () {
    gulp.watch('./vk-footer-customize/package/**', gulp.task('copy_vk-footer-customize'));
});

/**
 * VK Campaign Text
 */
gulp.task('copy_vk-campaign-text', function (done) {
    gulp.src('./vk-campaign-text/package/**')
        .pipe(gulp.dest('../themes/katawara/inc/vk-campaign-text/package'))
        .pipe(gulp.dest('../themes/lightning-pro/inc/vk-campaign-text/package'))
        .pipe(gulp.dest('../plugins/lightning-g3-pro-unit/inc/vk-campaign-text/package'));
        done();
});
gulp.task('watch_vk-campaign-text', function () {
    gulp.watch('./vk-campaign-text/package/**', gulp.task('copy_vk-campaign-text'));
});

/*-------------------------------------*/
/*  vk-mobile-nav
/*-------------------------------------*/
gulp.task('sass_vk-mobile-nav', function (done) {
  gulp.src('vk-mobile-nav/package/_scss/*.scss')
    .pipe(sass())
    .pipe(cmq({
        log: true
    }))
    .pipe(autoprefixer())
    .pipe(cleanCss())
    .pipe(gulp.dest('./vk-mobile-nav/package/css/'))
  done();
});

gulp.task('copy_vk-mobile-nav', function (done) {
  gulp.src('./vk-mobile-nav/package/**')
    .pipe(gulp.dest('../themes/lightning/inc/vk-mobile-nav/package/'))
    // .pipe(gulp.dest('../themes/lightning/_g3/inc/vk-mobile-nav/package/'))
    .pipe(gulp.dest('../themes/lightning-pro/inc/vk-mobile-nav/package/'))
    .pipe(gulp.dest('../themes/katawara/inc/vk-mobile-nav/package/'))
    // .pipe(gulp.dest('../themes/seizen-souzoku/inc/vk-mobile-nav/'))
    // .pipe(gulp.dest('../themes/souzoku/inc/vk-mobile-nav/package/'))
  done()
});

gulp.task('watch_mobile', function () {
  gulp.watch('./vk-mobile-nav/package/_scss/**', gulp.series('sass_vk-mobile-nav'));
  gulp.watch('./vk-mobile-nav/package/_scss/**', gulp.series('copy_vk-mobile-nav'));
  gulp.watch('./vk-mobile-nav/package/**.php', gulp.series('copy_vk-mobile-nav'));
  gulp.watch('./vk-mobile-nav/package/images/**', gulp.series('copy_vk-mobile-nav'));
  gulp.watch('./vk-mobile-nav/package/js/**', gulp.series('copy_vk-mobile-nav'));
});

/*-------------------------------------*/
/*  vk-mobile-fix-nav
/*-------------------------------------*/
gulp.task('sass_vk-mobile-fix-nav', function(done) {
  gulp.src('vk-mobile-fix-nav/package/_scss/**/*.scss')
    .pipe(plumber())
    .pipe(sass())
    .pipe(cmq({
      log: true
    }))
    .pipe(autoprefixer())
    .pipe(cleanCss())
        .pipe(gulp.dest('./vk-mobile-fix-nav/package/css/'));
        done();
});
gulp.task('copy_vk-mobile-fix-nav', function(done) {
  gulp.src('./vk-mobile-fix-nav/package/**')
    // .pipe(gulp.dest('../themes/souzoku/inc/vk-mobile-fix-nav/'))
    // .pipe(gulp.dest('../themes/seizen-souzoku/inc/vk-mobile-fix-nav/'))
    .pipe(gulp.dest('../themes/katawara/inc/vk-mobile-fix-nav/package/'))
    .pipe(gulp.dest('../themes/lightning-pro/inc/vk-mobile-fix-nav/package/'))
    .pipe(gulp.dest('../plugins/lightning-g3-pro-unit/inc/vk-mobile-fix-nav/package/'));
    done();
});
gulp.task('watch_mf', function() {
  gulp.watch('./vk-mobile-fix-nav/package/_scss/**.scss', gulp.series('sass_vk-mobile-fix-nav','copy_vk-mobile-fix-nav'));
  gulp.watch('./vk-mobile-fix-nav/package/**', gulp.task('copy_vk-mobile-fix-nav'));
});

gulp.task('watch_mobile-fix', function(done) {
  gulp.watch('./vk-mobile-fix-nav/package/_scss/**', gulp.task('sass_vk-mobile-fix-nav'));
  gulp.watch('./vk-mobile-fix-nav/package/**', gulp.task('copy_vk-mobile-fix-nav'));
  done();
});

/**
 * Filter Search
 */
gulp.task('copy_filter_search', function (done) {
    gulp.src('./filter-search/package/**')
		.pipe(replace("'filter-search-textdomain'","'vk-filter-search'"))
        .pipe(gulp.dest('../plugins/vk-filter-search/inc/filter-search/package/'))
	gulp.src('./filter-search/package/**')
		.pipe(replace("'filter-search-textdomain'","'vk-filter-search-pro'"))
		.pipe(gulp.dest('../plugins/vk-filter-search-pro/inc/filter-search/package/'));
    done();
});

/**
 * VK Swiper
 */
 gulp.task('copy_vk-swiper', function(done) {
	gulp.src('./vk-swiper/package/**')
		.pipe(gulp.dest('../themes/lightning/_g3/inc/vk-swiper/package/'))
		.pipe(gulp.dest('../themes/katawara/inc/vk-swiper/package/'))
		.pipe(gulp.dest('../plugins/vk-blocks-pro/inc/vk-swiper/package/'))
		.pipe(gulp.dest('../plugins/lightning-advanced-slider/inc/vk-swiper/package/'))
	done();
});

/**
 * VK Google Tag Manager
 */
 gulp.task('copy_tag_manager', function (done) {
    gulp.src('./vk-google-tag-manager/package/**')
		.pipe(replace("'tag-manager-textdomain'","'lightning-pro'"))
        .pipe(gulp.dest('../themes/lightning-pro/inc/vk-google-tag-manager/package/'))
	gulp.src('./vk-google-tag-manager/package/**')
		.pipe(replace("'tag-manager-textdomain'","'katawara'"))
		.pipe(gulp.dest('../themes/katawara/inc/vk-google-tag-manager/package/'))
	gulp.src('./vk-google-tag-manager/package/**')
		.pipe(replace("'tag-manager-textdomain'","'vk-all-in-one-expansion-unit'"))
		.pipe(gulp.dest('../plugins/vk-all-in-one-expansion-unit/inc/vk-google-tag-manager/package/'))
    done();
});
