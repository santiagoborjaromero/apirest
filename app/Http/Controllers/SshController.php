<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use phpseclib3\Net\SSH2;

class SshController extends Controller
{
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $ssh;

    public function __construct(
        string $host,
        int $port,
        string $username,
        string $password
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;

        $this->connect();
    }

    protected function connect()
    {   
        error_log($this->host);
        error_log($this->port);
        error_log($this->username);
        error_log($this->password);
        $this->ssh = new SSH2($this->host, $this->port);
        try{
            return $this->ssh->login($this->username, $this->password);
        }catch(Exception $err){
            error_log($err);
            return $err;
        }
    }

    /**
     * Ejecutar uno o varios comandos
     *
     * @param array|string $commands
     * @return string
     */
    public function run($commands): string
    {
        if (is_string($commands)) {
            $resp = $this->ssh->exec($commands); 
            return $resp;
        }

        if (is_array($commands)) {
            $output = '';
            foreach ($commands as $cmd) {
                $output .= "EJECUTANDO: $cmd\n";
                $output .= $this->ssh->exec($cmd) . "\n";
            }
            return $output;
        }

        return "No se proporcionaron comandos válidos.";
    }
}