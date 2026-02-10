<?php

test('php and laravel version', function () {
    var_dump(app()->version());
    var_dump(phpversion());
});