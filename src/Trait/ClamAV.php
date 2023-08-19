<?php

namespace Ikechukwukalu\Clamavfileupload\Trait;

use Ikechukwukalu\Clamavfileupload\Events\ClamavIsNotRunning;

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
 *    GitHub account.
*/

trait ClamAV {

    private $message;

    /**
     * Private function to open a socket
     * to clamd based on the current options.
     */
    private function socket()
    {
        if(empty(config('clamavfileupload.clamd_ip')) && empty(config('clamavfileupload.clamd_ip'))) {
            // By default we just use the local socket
            $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);

            if(socket_connect($socket, config('clamavfileupload.clamd_sock'))) {
                $this->message = trans('clamavfileupload::clamav.socket_connected');
                return $socket;
            }
        }

        // Attempt to use a network based socket
        $socket = socket_create(AF_INET, SOCK_STREAM, 0);

        if(socket_connect($socket, config('clamavfileupload.clamd_ip'), config('clamavfileupload.clamd_ip'))) {
            $this->message = trans('clamavfileupload::clamav.socket_connected');
            return $socket;
        }

        $this->message = trans('clamavfileupload::clamav.unable_to_open_socket');
        return false;
    }

    /**
     * Get the last scan message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Function to ping Clamd to make sure its functioning.
     *
     * @return bool
     */
    public function ping(): bool
    {
        $ping = $this->send("PING");

        if($ping == "PONG") {
            return true;
        }

        $this->message = trans('clamavfileupload::clamav.not_running');
        ClamavIsNotRunning::dispatch();

        return false;
    }

    /**
     * Function to scan the passed in file.
     * Returns true if safe, false otherwise.
     *
     * @return bool
     */
    public function scan($file): bool
    {
        if(!file_exists($file)) {
            $this->message = trans('clamavfileupload::clamav.file_not_found',
                ['name' => $file]);
            return false;
        }

        $scan = $this->send("SCAN $file");
        if($scan === false) {
            $this->message = trans('clamavfileupload::clamav.not_running');
            ClamavIsNotRunning::dispatch();

            return false;
        }

        $scanMessage = trim(substr(strrchr($scan, ":"), 1));
        if($scanMessage == 'OK') {
            $this->message = $scanMessage;
            return true;
        }

        $this->message = trans('clamavfileupload::clamav.file_not_safe',
            ['name' => $file]);
        return false;
    }

    /**
     * Function to scan the passed in stream.
     * Returns true if safe, false otherwise.
     *
     * @param $file
     * @return bool
     */
    public function scanStream($file): bool
    {
        $socket = $this->socket();
        if(!$socket) {
            $this->message = trans('clamavfileupload::clamav.not_running');
            ClamavIsNotRunning::dispatch();

            return false;
        }

        if(!file_exists($file)) {
            $this->message = trans('clamavfileupload::clamav.file_not_found');
            return false;
        }

        if ($scan_fh = fopen($file, 'rb')) {
            return $this->scanStreamSend($scan_fh, $socket, $file);
        }

        $this->message = trans('clamavfileupload::clamav.file_not_safe',
            ['name' => $file]);
        return false;
    }


    /**
     * Function to scan the passed in stream.
     * Returns true if safe, false otherwise.
     *
     * @param $scan_fh
     * @param $socket
     * @param $file
     * @return bool
     */
    private function scanStreamSend($scan_fh, $socket, $file): bool
    {
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

        if($scan === false) {
            $this->message = trans('clamavfileupload::clamav.not_running');
            ClamavIsNotRunning::dispatch();

            return false;
        }

        $scanMessage = trim(substr(strrchr($scan, ":"), 1));
        if($scanMessage == 'OK') {
            $this->message = $scanMessage;
            return true;
        }

        $this->message = trans('clamavfileupload::clamav.file_not_safe',
            ['name' => $file]);
        return false;
    }

    /**
     * Function to send a command to the Clamd socket.
     * In case you need to send any other commands directly.
     *
     * @return bool
     */
    public function send($command)
    {
        if(empty($command)) {
            return false;
        }

        try {
            $socket = $this->socket();

            if($socket) {
                socket_send($socket, $command, strlen($command), 0);
                socket_recv($socket, $return, config('clamavfileupload.clamd_sock_len'), 0);
                socket_close($socket);

                return trim($return);
            }
        } catch (\ErrorException $e) {
            $this->message = $e->getMessage();
        }

        return false;
    }
}
