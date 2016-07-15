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


gulp.task( 'copy', function() {
    gulp.src( './vk-admin/**'  )
    .pipe( gulp.dest( '../plugins/vk-all-in-one-expansion-unit/plugins_admin/vk-admin/' ) )
    .pipe( gulp.dest( '../plugins/vk-post-author-display/vk-admin/' ) );
} );

// Watch
gulp.task('watch', function() {
    gulp.watch('./vk-admin/css/**', ['copy']);
    gulp.watch('./vk-admin/class.vk-admin.php', ['copy']);
    // gulp.watch('js/*.js', ['scripts']);
    // gulp.watch('vk-admin', ['scripts','scripts_header_fixed']);
});

gulp.task('default', ['copy','watch']);
