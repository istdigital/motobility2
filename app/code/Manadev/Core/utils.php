<?php

if (!function_exists('_me')) {
    function _me() {
        $ip = [];

        if (count($ip) && !in_array($_SERVER['REMOTE_ADDR'], $ip)) {
            return false;
        }

        return true;
    }
}

/**
 * MANAdev logging function
 */
if (!function_exists('_log')) {
    function _log($message, $filename = 'mana.log') {
        if (!_me()) return;

        $filename = BP . '/var/log/' . $filename;
        $s = file_exists($filename) ? @file_get_contents($filename) : '';
        file_put_contents($filename, $s . $message . "\n");
    }
}

if (!function_exists('_logStackTrace')) {
    function _logStackTrace($filename = 'mana.log') {
        if (!_me()) return;

        try {
            throw new \Exception();
        }
        catch (\Exception $e) {
            _log($e->getTraceAsString(), $filename);
        }
    }
}

$_timers = [];
$_startedTimers = [];

if (!function_exists('_start')) {
    function _start($timer, $tag = 'default') {
        global $_startedTimers;

        if (!_me()) return;

        $micro_time = microtime(true);
        if (!isset($_startedTimers[$timer])) $_startedTimers[$timer] = [];
        $_startedTimers[$timer][] = compact('tag', 'micro_time');
    }
}

if (!function_exists('_stop')) {
    function _stop($timer) {
        global $_timers, $_startedTimers;

        if (!_me()) return;

        $record = array_pop($_startedTimers[$timer]);
        $elapsed = microtime(true) - $record['micro_time'];

        foreach ($_startedTimers as &$startedTimer) {
            foreach ($startedTimer as &$startedRecord) {
                $startedRecord['micro_time'] += $elapsed;
            }
        }

        if (!isset($_timers[$timer])) $_timers[$timer] = ['count' => 0, 'elapsed' => 0];
        $_timers[$timer]['count']++;
        $_timers[$timer]['elapsed'] += $elapsed;

//        if (!isset($_timers[$record['tag']])) $_timers[$record['tag']] = ['count' => 0, 'elapsed' => 0];
//        $_timers[$record['tag']]['count']++;
//        $_timers[$record['tag']]['elapsed'] += $elapsed;
    }
}

if (!function_exists('_logTimers')) {
    function _logTimers() {
        global $_timers;

        if (!_me()) return;

        uasort($_timers, function($a, $b) {
            if ($a['elapsed'] < $b['elapsed']) return 1;
            if ($a['elapsed'] > $b['elapsed']) return -1;

            return 0;
        });

        $specialAndReservedCharacters = ";/?:@=&$+!*'(),";

        $url = str_replace(str_split($specialAndReservedCharacters), '_', $_SERVER['REQUEST_URI']);
        if (strlen($url) > 128) {
            $url = substr($url, 0, 128);
        }

        $filename = sprintf(BP . '/var/log/mana/timers/%s-%s.log',
            PHP_SAPI !== 'cli' ? $_SERVER['REMOTE_ADDR'] . '-' . $url : 'cli',
            date("Y-m-d-H-i-s")
        );

        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        file_put_contents($filename, json_encode($_timers, JSON_PRETTY_PRINT));
    }
}
