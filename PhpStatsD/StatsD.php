<?php

/*
 * A simple PHP class for interacting with a statsd server
 * @author John Crepezzi <john.crepezzi@gmail.com>
 */
namespace PhpStatsD;

class StatsD {

    private $host, $port, $debug;

    // Instantiate a new client
    public function __construct($host = 'localhost', $port = 8125, $debug=false) {
        $this->host = $host;
        $this->port = $port;
	$this->debug = $debug;
    }

    // Record timing
    public function timing($key, $time, $rate = 1) {
        $this->send("$key:$time|ms", $rate);
    }

    // Time something
    public function time_this($key, $callback, $rate = 1) {
        $begin = microtime(true);
        $callback();
        $time = floor((microtime(true) - $begin) * 1000);
        // And record
        $this->timing($key, $time, $rate);
    }

    // Record counting
    public function counting($key, $amount = 1, $rate = 1) {
        $this->send("$key:$amount|c", $rate);
    }


    // Record gauge
    public function gauge($key, $gauge) {
        $this->send_value("$key:$gauge|g");
    }


    // Send
    private function send($value, $rate) {
        if ($this->debug) echo "Sending $value|@$rate to ".$this->host.':'.$this->port."\n";
        $fp = fsockopen('udp://' . $this->host, $this->port, $errno, $errstr);
        // Will show warning if not opened, and return false
        if ($fp) {
            fwrite($fp, "$value|@$rate");
            fclose($fp);
        } else {
            echo "Error sending\n";
	}
    }

    private function send_value($value) {
	if ($this->debug) echo "Sending $value to ".$this->host.':'.$this->port."\n";
        $fp = fsockopen('udp://' . $this->host, $this->port, $errno, $errstr);
        // Will show warning if not opened, and return false
        if ($fp) {
            fwrite($fp, "$value");
            fclose($fp);
        } else {
	    echo "Error sending\n";
	}

    }

}
