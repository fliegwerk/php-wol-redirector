<?php

/// config section

const CONFIG_PATH = "config.json";
const GET_PARAM_REDIRECT_URL = "dest";
const SESSION_PARAM_TRY_COUNT = "count";

if (!file_exists(CONFIG_PATH)) {
    die("Configuration file doesn't exist. Please create JSON file at \"" . CONFIG_PATH . "\" and try again.");
}

$config = json_decode(file_get_contents(CONFIG_PATH));
$log_enabled = $config->{"log"} ?? false;

/// logging section

function notice(string $message, string $scope = null): void {
    global $log_enabled;
    if (!$log_enabled) return;
    $scope = isset($scope) ? " [" . $scope . "]" : "";
    error_log("notice" . $scope . ": " . $message . "\n");
}

function warn(string $message, string $scope = null): void {
    global $log_enabled;
    if (!$log_enabled) return;
    $scope = isset($scope) ? " [" . $scope . "]" : "";
    error_log("warn" . $scope . ": " . $message . "\n");
}

function error(string $message, string $scope = null): void {
    global $log_enabled;
    if (!$log_enabled) return;
    $scope = isset($scope) ? " [" . $scope . "]" : "";
    error_log("error" . $scope . ": " . $message . "\n");
}

/// command section

/**
 * Same as {@link exec()} but additionally logs everything to the error log.
 */
function execute_command(string $command, array &$output, int &$result_code, string $scope = null) {
    $result = exec($command, $output, $result_code);
    notice("executed: " . $command . ", got: " . $output[0] . ", rc: " . $result_code, $scope);
    return $result;
}

/**
 * Checks if a given command (UNIX binary) is in the PATH environment.
 *
 * @return bool <code>true</code> when the command/binary exists
 */
function command_exists(string $command): bool
{
    $output = [];
    $result_code = 0;
    execute_command("command -v " . escapeshellarg($command), $output, $result_code, "command_exists");
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
    execute_command("command -v " . escapeshellarg($command), $output, $result_code, "get_command_path");

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
    execute_command($cmd, $output, $result_code, "is_up");
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

    // send one WoL packet to the host
    if (command_exists("wol")) {
        notice("Found \"wol\" as WoL binary", "wake");
        $wol_command = get_command_path("wol") . " " . escapeshellarg($config->{"host"}->{"mac"});
    } else if (command_exists("wakeonlan")) {
        notice("Found \"wakeonlan\" as WoL binary", "wake");
        $wol_command = get_command_path("wakeonlan") . " " . escapeshellarg($config->{"host"}->{"mac"});
    } else {
        die("No WoL binaries found. Please install \"wakeonlan\" or \"wol\" package.");
    }

    $output = [];
    $result_code = 0;
    execute_command($wol_command, $output, $result_code, "wake");
    return $result_code == 0;
}
