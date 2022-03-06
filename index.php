<?php
require_once "lib.php";
global $config;

session_start();

$dest = $_GET[GET_PARAM_REDIRECT_URL] ?? null;
$is_up = is_up();
$timeout = $config->{"timeout"};
$try_count = $_SESSION[SESSION_PARAM_TRY_COUNT] ?? 0;

if (isset($dest)) {
    // direct redirect
    if ($is_up) {
        header("Location: " . urldecode($dest));
        exit(0);
    }

    // well, host is down -> try to wake up
    $wake_state = wake();
    $try_count++;
    $_SESSION[SESSION_PARAM_TRY_COUNT] = $try_count;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="theme-color" content="#ffffff">
    <meta name="msapplication-TileColor" content="#ffc40d">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">
    <?php if (isset($dest) && $timeout > 0): ?>
        <meta http-equiv="refresh" content="<?= $timeout ?>"/>
    <?php endif; ?>

    <title><?= isset($dest) ? "Server is starting" : "No destination" ?></title>

    <link href="index.css" type="text/css" rel="stylesheet">

    <!-- favicon support -->
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png?v=3jkrji349">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png?v=3jkrji349">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png?v=3jkrji349">
    <link rel="manifest" href="site.webmanifest?v=3jkrji349">
    <link rel="mask-icon" href="images/safari-pinned-tab.svg?v=3jkrji349" color="#0092bb">
    <link rel="shortcut icon" href="favicon.ico?v=3jkrji349">

    <script>
        'use strict';
        window.onload = function () {
            // const refreshTime = 5 * 1000;
            const refreshTime = <?= $timeout ?> * 1000;
            const container = document.getElementById("animated-container");
            const elements = container !== null ? container.children : [];
            // we actually count the steps between the hides, therefore +1
            const cycleTime = refreshTime / (elements.length + 1);

            // console.log(`Refresh time: ${refreshTime}ms`);
            // console.log(`Cycle time: ${cycleTime}ms`);
            // console.log("Elements:", elements);

            let current = elements.length - 1;

            function cycle() {
                if (current >= 0) {
                    // console.log("Next element:", current);
                    // next call -> hide current element
                    elements[current].classList.add("hide");
                    // decrement for next call
                    current--;
                }
            }

            // JS...
            cycle = cycle.bind(this);

            // console.log("Start cycle");
            if (elements.length > 0 && cycleTime > 0) setInterval(cycle, cycleTime);
        }
    </script>
</head>
<body>

<?php if (isset($dest)): ?>
    <div>
        <div>
            <h1>Server is starting</h1>

            <img src="images/booting-server.svg" alt="Booting server icon"/>

            <div id="animated-container" class="progress-container">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>

            <p class="subtitle">Please wait for the host to come up</p>

            <?php if ($config->{"details"}): ?>
                <table class="subtitle details">
                    <tr>
                        <td>Destination host:</td>
                        <td><?= $config->{"host"}->{"ip"} ?></td>
                    </tr>

                    <tr>
                        <td>Host State:</td>
                        <td><?= $is_up ? "Up" : "Down" ?></td>
                    </tr>

                    <tr>
                        <td>Destination URL:</td>
                        <td><?= htmlspecialchars(urldecode($dest)) ?></td>
                    </tr>

                    <tr>
                        <td>Timeout:</td>
                        <td><?= $timeout ?></td>
                    </tr>

                    <tr>
                        <td>WoL packet sent:</td>
                        <td><?= isset($wake_state) ? $wake_state ? "successfully" : "failed" : "undefined" ?></td>
                    </tr>

                    <tr>
                        <td>Try count:</td>
                        <td><?= $try_count ?></td>
                    </tr>
                </table>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>

    <div>
        <div>
            <h1>No Destination</h1>

            <p>Please add a destination to the parameter list.</p>
            <p>Use the <code>encode_url.php</code> script to generate one.</p>
        </div>
    </div>

<?php endif; ?>

</body>
</html>