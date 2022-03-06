<?php

/// config section

const CONFIG_PATH = "config.json";
const GET_PARAM_REDIRECT_URL = "dest";
const SESSION_PARAM_TRY_COUNT = "count";

if (!file_exists(CONFIG_PATH)) {
    die("Configuration file doesn't exist. Please create JSON file at " . CONFIG_PATH . " and try again!");
}

$config = json_decode(file_get_contents(CONFIG_PATH));

/// command section

/**
 * Checks if a given command (UNIX binary) is in the PATH environment.
 *
 * @return bool <code>true</code> when the command/binary exists
 */
function command_exists(string $command): bool
{
    $output = [];
    $result_code = 0;
    $cmd = "command -v " . $command;
    exec($cmd, $output, $result_code);
    return $result_code == 0;
}

/**
 * Checks if the given command/binary name exist and returns the path to the executable.
 *
 * @param string $command the command to check
 * @return string the path to the executable
 */
function get_command_path(string $command): string
{
    $output = [];
    $result_code = 0;
    $cmd = "command -v " . $command;
    exec($cmd, $output, $result_code);

    if ($result_code > 0) {
        die("Requested command " . $command . " doesn't exist. Please install the required package and try again.");
    }

    return $output[0];
}

/// function section

/**
 * Checks if the configured host is up and running.
 *
 * @return bool <code>true</code> when the host is up
 */
function is_up(): bool
{
    global $config;

    // send one ICMP ping packet to host
    // when answered -> host is up
    // when timeout -> host is down
    $output = [];
    $result_code = 0;
    $cmd = get_command_path("ping") . " -q -c 1 -w 1 " . $config->{"host"}->{"ip"};
    exec($cmd, $output, $result_code);

    return $result_code == 0;
}

/**
 * Wakes the configured host with a Wake-On-Lan packet.
 *
 * @return bool <code>true</code> when the WoL packet was sent successfully
 */
function wake(): bool
{
    global $config;

    // send one WOL packet to the host
    if (command_exists("wol")) {
        $wol_command = sprintf(get_command_path("wol") . " -i \"%s\" \"%s\"",
            $config->{"host"}->{"ip"},
            $config->{"host"}->{"mac"}
        );
    } else if (command_exists("wakeonlan")) {
        $wol_command = sprintf(get_command_path("wakeonlan") . " -i \"%s\" \"%s\"",
            $config->{"host"}->{"ip"},
            $config->{"host"}->{"mac"}
        );
    } else {
        die("No wakeonlan binaries found. Please install \"wakeonlan\" or \"wol\" package.");
    }

    $output = [];
    $result_code = 0;
    exec($wol_command, $output, $result_code);
    return $result_code == 0;
}
