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
    .pipe( gulp.dest( '../plugins/vk-all-in-one-expansion-unit/plugins_admin/vk-admin/' ) ); // _scss ディレクトリに保存
} );

// Watch
gulp.task('watch', function() {
    // gulp.watch('css/*.css', ['cssmin'])
    // gulp.watch('js/*.js', ['scripts']);
    gulp.watch('vk-admin', ['scripts','scripts_header_fixed']);
});

gulp.task('default', ['copy','watch']);
