<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * SIMPOSYANDU CI4 - dua koneksi: default (aplikasi) + opensid (read-only penduduk).
 * Kredensial dibaca dari .env, bukan dari kode.
 */
class Database extends Config
{
    public array $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    public string $defaultGroup = 'default';

    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => 'simposyandu',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'dateFormat'   => ['date' => 'Y-m-d', 'datetime' => 'Y-m-d H:i:s', 'time' => 'H:i:s'],
    ];

    public array $opensid = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => 'opensid',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => false,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
    ];

    public function __construct()
    {
        parent::__construct();

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }

        // Override dari .env bila ada
        $this->default['hostname'] = env('database.default.hostname', $this->default['hostname']);
        $this->default['database'] = env('database.default.database', $this->default['database']);
        $this->default['username'] = env('database.default.username', $this->default['username']);
        $this->default['password'] = env('database.default.password', $this->default['password']);

        $this->opensid['hostname'] = env('database.opensid.hostname', $this->opensid['hostname']);
        $this->opensid['database'] = env('database.opensid.database', $this->opensid['database']);
        $this->opensid['username'] = env('database.opensid.username', $this->opensid['username']);
        $this->opensid['password'] = env('database.opensid.password', $this->opensid['password']);
    }
}
