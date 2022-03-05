<?php
    require_once "lib.php";
    global $config;

    session_start();

    $dest = $_GET[GET_PARAM_REDIRECT_URL];
    $is_up = is_up();
    $timeout = $config->{"timeout"};
    $try_count = $_SESSION[SESSION_PARAM_TRY_COUNT] ?? 0;

    if ($dest != null && $is_up) {
        header("Location: " . urldecode($dest));
        exit(0);
    }

    // well, host is down -> try to wake up
    $wol_state = wake();
    $try_count++;
    $_SESSION[SESSION_PARAM_TRY_COUNT] = $try_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check Homeserver Response</title>
    <meta http-equiv="refresh" content="<?= $timeout ?>" />
</head>
<body>

<p>Destination host: <?= $config->{"host"}->{"ip"} ?></p>
<p>Host State: <?= $is_up ? "Up" : "Down" ?></p>
<p>Destination URL: <?= htmlspecialchars(urldecode($dest)) ?></p>
<p>Timeout: <?= $timeout ?></p>
<p>WoL packet sent: <?= $wol_state ? "successfully" : "failed" ?></p>
<p>Try count: <?= $try_count ?></p>
<p>Please wait for the host to come up...</p>
</body>
</html>