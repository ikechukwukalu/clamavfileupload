<?php

namespace Ikechukwukalu\Clamavfileupload\Trait;

/*
 * ClamAV.php
 *
 * A simple PHP class for scanning files using ClamAV.
 *
 * Copyright (C) 2017 KISS IT Consulting <http://www.kissitconsulting.com/>
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above
 *    copyright notice, this list of conditions and the following
 *    disclaimer in the documentation and/or other materials
 *    provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL ANY
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/**
 * 1. Go to <https://github.com/kissit/php-clamav-scan> to view the original
 *    PHP file.
 * 2. Go to <https://github.com/ikechukwukalu> to view my personal
 *    GitHub account 😊.
*/

trait ClamAV {

    const OK = "OK";

    private static $message;

    /**
     * Private function to open a socket
     * to clamd based on the current options.
     */
    private static function socket()
    {
        if(!empty(config('clamavfileupload.clamd_ip')) && !empty(config('clamavfileupload.clamd_ip'))) {
            // Attempt to use a network based socket
            $socket = socket_create(AF_INET, SOCK_STREAM, 0);
            if(socket_connect($socket, config('clamavfileupload.clamd_ip'), config('clamavfileupload.clamd_ip'))) {
                self::$message = trans('clamavfileupload::clamav.connected');

                return $socket;
            }
        } else {
            // By default we just use the local socket
            $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
            if(socket_connect($socket, config('clamavfileupload.clamd_sock'))) {
                self::$message = trans('clamavfileupload::clamav.socket_connected');

                return $socket;
            }
        }

        return false;
    }

    /**
     * Get the last scan message.
     */
    public static function getMessage(): string
    {
        return self::$message;
    }

    /**
     * Function to ping Clamd to make sure its functioning.
     */
    public static function ping(): bool
    {
        $ping = self::send("PING");

        if($ping == "PONG") {
            return true;
        }

        self::$message = trans('clamavfileupload::clamav.not_running');

        return false;
    }

    /**
     * Function to scan the passed in file.
     * Returns true if safe, false otherwise.
     */
    public static function scan($file): bool
    {
        if(file_exists($file)) {
            $scan = self::send("SCAN $file");
            $scan = substr(strrchr($scan, ":"), 1);

            if($scan !== false) {
                self::$message = trim($scan);
                if(self::$message == self::OK) {
                    return true;
                }

            } else {
                self::$message = trans('clamavfileupload::clamav.file_not_safe', ['name' => $file]);
            }

        } else {
            self::$message = trans('clamavfileupload::clamav.file_not_found', ['name' => $file]);
        }

        return false;
    }

    /**
     * Function to scan the passed in stream.
     * Returns true if safe, false otherwise.
     */
    public static function scanstream($file): bool
    {
        $socket = self::socket();
        if(!$socket) {
            self::$message = trans('clamavfileupload::clamav.unable_to_open_socket');

            return false;
        }

        if(file_exists($file)) {
            if ($scan_fh = fopen($file, 'rb')) {
                $chunksize = filesize($file) < 8192 ? filesize($file) : 8192;
                $command = "zINSTREAM\0";
                socket_send($socket, $command, strlen($command), 0);

                while (!feof($scan_fh)) {
                    $data = fread($scan_fh, $chunksize);
                    $packet = pack(sprintf("Na%d", strlen($data)), strlen($data), $data);
                    socket_send($socket, $packet, strlen($packet), 0);
                }

                $packet = pack("Nx",0);
                socket_send($socket, $packet, strlen($packet), 0);
                socket_recv($socket, $scan, config('clamavfileupload.clamd_sock_len'), 0);
                socket_close($socket);
                trim($scan);
                $scan = substr(strrchr($scan, ":"), 1);

                if($scan !== false) {
                    self::$message = trim($scan);
                    if(self::$message == self::OK) {

                        return true;
                    }

                } else {
                    self::$message = trans('clamavfileupload::clamav.scan_failed');
                }
            }

        } else {
            self::$message = trans('clamavfileupload::clamav.file_not_found');
        }

        return false;
    }

    /**
     * Function to send a command to the Clamd socket.
     * In case you need to send any other commands directly.
     */
    public static function send($command) {
        if(!empty($command)) {
            $socket = self::socket();
            if($socket) {
                socket_send($socket, $command, strlen($command), 0);
                socket_recv($socket, $return, config('clamavfileupload.clamd_sock_len'), 0);
                socket_close($socket);

                return trim($return);
            }
        }

        return false;
    }
}
