<?php
xcache_clear_cache(XC_TYPE_PHP);
$date = date('Y-m-d H:i:s');
$host = gethostname();
echo "[$date] Cache clean on $host";
