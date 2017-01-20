var gulp = require('gulp');

var cssmin = require('gulp-cssmin');
// ファイルリネーム（.min作成用）
var rename = require('gulp-rename');
// ファイル結合
var concat = require('gulp-concat');
// js最小化
var jsmin = require('gulp-jsmin');
// エラーでも監視を続行させる
var plumber = require('gulp-plumber');

gulp.task( 'copy_vk-admin', function() {
    gulp.src( './vk-admin/package/**' )
    .pipe( gulp.dest( '../plugins/vk-all-in-one-expansion-unit/plugins_admin/vk-admin/' ) )
    .pipe( gulp.dest( '../plugins/vk-post-author-display/inc/vk-admin/' ) )
    .pipe( gulp.dest( '../plugins/lightning-skin-variety/inc/vk-admin/' ) )
    .pipe( gulp.dest( '../plugins/lightning-origin-pro/inc/vk-admin/' ) )
    .pipe( gulp.dest( '../plugins/wp-easy-responsive-tabs-to-accordion/vk-admin/' ) );
} );
gulp.task( 'copy_term-color', function() {
    gulp.src( './term-color/package/**' )
    .pipe( gulp.dest( '../plugins/vk-post-author-display/inc/term-color/' ) )
    .pipe( gulp.dest( '../plugins/lightning-skin-variety/inc/term-color/' ) )
    .pipe( gulp.dest( '../plugins/lightning-origin-pro/inc/term-color/' ) );
} );
gulp.task( 'copy_header-top', function() {
    gulp.src( './header-top/package/**' )
    .pipe( gulp.dest( '../plugins/lightning-skin-variety/inc/header-top/' ) )
    .pipe( gulp.dest( '../plugins/lightning-origin-pro/inc/header-top/' ) );
} );
gulp.task( 'copy_media-posts', function() {
    gulp.src( './media-posts/package/**' )
    .pipe( gulp.dest( '../plugins/lightning-skin-variety/inc/media-posts/' ) )
    .pipe( gulp.dest( '../plugins/lightning-origin-pro/inc/media-posts/' ) );
} );
// gulp.task( 'copy_font-awesome-selector', function() {
//     gulp.src( './font-awesome-selector/package/**' )
//     .pipe( gulp.dest( '../themes/lightning/library/font-awesome-selector/' ) );
// } );
gulp.task( 'copy_post-type-manager', function() {
    gulp.src( './post-type-manager/package/**' )
    .pipe( gulp.dest( '../themes/biz-vektor/plugins/post-type-manager/' ) )
    .pipe( gulp.dest( '../plugins/vk-all-in-one-expansion-unit/plugins/post-type-manager/' ) );
} );
gulp.task( 'copy_custom-field-builder', function() {
    gulp.src( './custom-field-builder/package/**' )
    .pipe( gulp.dest( '../themes/bill-vektor/inc/custom-field-builder/' ) );
} );
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
    // gulp.watch('./font-awesome-selector/package/**', ['copy_font-awesome-selector']);
    // gulp.watch('js/*.js', ['scripts']);
    // gulp.watch('vk-admin', ['scripts','scripts_header_fixed']);
});

gulp.task('default', ['watch']);
