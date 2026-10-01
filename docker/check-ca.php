<?php
$caPath = getenv('MYSQL_ATTR_SSL_CA');
if ($caPath && !file_exists($caPath)) {
    fwrite(STDERR, "CA file does not exist at: {$caPath}\n");
    exit(1);
}