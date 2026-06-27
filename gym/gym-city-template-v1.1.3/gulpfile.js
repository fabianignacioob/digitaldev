(function() {
  'use strict';

  var gulp = require('gulp');
  var browserSync = require('browser-sync');
  var clean = require('gulp-clean');

  var requireDir = require('require-dir');
  requireDir('./gulp');

  function initBrowserSync(done) {
    browserSync.init({
      ui: false,
      port: 8000,
      ghostMode: false,
      notify: false,
      proxy: {
        target: 'http://localhost:8000/'
      }
    });
    done();
  }

  gulp.task(
    'serve',
    gulp.series(
      gulp.parallel('build-css', 'build-js', 'build-html', 'build-img'),
      gulp.parallel('watch-css', 'watch-js', 'watch-html', initBrowserSync)
    )
  );

  function cleanPublicFolder() {
    return gulp
      .src('./public', { read: false, allowEmpty: true })
      .pipe(clean());
  }

  gulp.task(
    'release',
    gulp.series(
      cleanPublicFolder,
      gulp.parallel('min-js', 'min-css', 'min-html', 'min-img')
    )
  );
})();
