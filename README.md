<img src="branding/screenshot.png" alt="The WoL Redirector website if the server is currently not reachable">

# php-wol-redirector

Sends a WOL packet to the specified host on website request and redirects to the hosted website after a timeout.

## Installation

1. Copy it to your hosting solution.
2. Copy the `config.sample.json` to `config.json`.
3. Edit the `config.json` to your liking.
4. Replace the links that point to the devices that are WoL capable.
   > Hint: Use the `./encode_url.php` script to generate the parameters.

## `config.json` definition

| Key        | Description                                                                                 |
|------------|---------------------------------------------------------------------------------------------|
| `host.mac` | The MAC address of the adapter that you want to wake (get it with `ip addr`)                |
| `host.ip`  | The IP address of the device you want to wake (only used for ICMP ping)                     |
| `timeout`  | The timeout between refreshes in the browser (`0` disables automatic refreshing)            |
| `details`  | Show details to the client (e.g. try count, destination url, etc.)                          |
| `log`      | When `true` write log messages via the `error_log` function for debugging output (optional) |
