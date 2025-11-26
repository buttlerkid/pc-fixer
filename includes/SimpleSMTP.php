<?php
/**
 * SimpleSMTP Class
 * A lightweight SMTP client for sending emails without external dependencies.
 */
class SimpleSMTP {
    private $host;
    private $port;
    private $username;
    private $password;
    private $timeout = 30;
    private $socket;
    private $debug = false;
    private $logs = [];

    public function __construct($host, $port, $username, $password) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function send($to, $subject, $body, $fromEmail, $fromName) {
        try {
            $this->connect();
            $this->auth();
            
            $this->sendCommand('MAIL FROM: <' . $fromEmail . '>');
            $this->sendCommand('RCPT TO: <' . $to . '>');
            $this->sendCommand('DATA');
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $fromName . " <" . $fromEmail . ">\r\n";
            $headers .= "To: " . $to . "\r\n";
            $headers .= "Subject: " . $subject . "\r\n";
            
            $this->sendCommand($headers . "\r\n" . $body . "\r\n.");
            $this->sendCommand('QUIT');
            
            fclose($this->socket);
            return true;
        } catch (Exception $e) {
            $this->log("Error: " . $e->getMessage());
            if ($this->socket) fclose($this->socket);
            return false;
        }
    }

    private function connect() {
        $protocol = '';
        if ($this->port == 465) $protocol = 'ssl://';
        // if ($this->port == 587) $protocol = 'tls://'; // TLS is handled via STARTTLS

        $this->socket = fsockopen($protocol . $this->host, $this->port, $errno, $errstr, $this->timeout);
        
        if (!$this->socket) {
            throw new Exception("Could not connect to SMTP host: $errstr ($errno)");
        }
        
        $this->readResponse();
        
        if ($this->port == 587) {
            $this->sendCommand('EHLO ' . $_SERVER['SERVER_NAME']);
            $this->sendCommand('STARTTLS');
            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->sendCommand('EHLO ' . $_SERVER['SERVER_NAME']);
        } else {
            $this->sendCommand('EHLO ' . $_SERVER['SERVER_NAME']);
        }
    }

    private function auth() {
        if (!empty($this->username) && !empty($this->password)) {
            $this->sendCommand('AUTH LOGIN');
            $this->sendCommand(base64_encode($this->username));
            $this->sendCommand(base64_encode($this->password));
        }
    }

    private function sendCommand($command) {
        $this->log("Client: " . $command);
        fputs($this->socket, $command . "\r\n");
        return $this->readResponse();
    }

    private function readResponse() {
        $response = '';
        while ($str = fgets($this->socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == ' ') break;
        }
        $this->log("Server: " . $response);
        
        // Check for error codes (4xx or 5xx)
        $code = substr($response, 0, 3);
        if ($code >= 400) {
            throw new Exception("SMTP Error: $response");
        }
        
        return $response;
    }

    private function log($message) {
        if ($this->debug) {
            error_log($message);
        }
        $this->logs[] = $message;
    }
    
    public function getLogs() {
        return $this->logs;
    }
}
