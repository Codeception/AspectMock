<?php
$version = file_get_contents(__DIR__.'/../VERSION');
system("git tag $version");
system("git push origin master --tags");