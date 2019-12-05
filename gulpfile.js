let gulp = require("gulp");

let paths = {
    js: [
        "gulpfile.js",
        "index.js",
        "api/*.js"
    ]
};

let eslint = require("gulp-eslint");

gulp.task("eslint", function() {
    return gulp.src(paths.js)
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(eslint.failAfterError());
});

let nodemon = require("gulp-nodemon");
gulp.task("nodemon", function(cb) {
    let started = false;

    nodemon({
        script: "index.js"
    }).on("start", function() {
        if (!started) {
            cb();
            started = true;
        }
    });
});

gulp.task("serve", gulp.series(["eslint", "nodemon"]));

gulp.task("default", gulp.series("nodemon"));
