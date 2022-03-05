#!/usr/bin/env php7
<?php

require_once "lib.php";

if ($argc < 2) {
    echo "Usage: encode_url.php <url>\n";
    exit(1);
}

echo "Append the following line to your link to the PHP WoL redirector:\n\n";
echo "   ?" . GET_PARAM_REDIRECT_URL . "=" . urlencode($argv[1]) . "\n\n";
exit(0);
