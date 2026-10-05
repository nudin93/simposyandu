<?php

use CodeIgniter\Router\RouteCollection;

/**
 * SIMPOSYANDU CI4 routes.
 * Lama: dashboard.php, router.php?c=balita&m=index, ajax/*.php
 * Baru: /dashboard, /balita, /api/balita dst.
 *
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::attempt');
$routes->get('/logout', 'Auth::logout');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('/kader-home', 'Dashboard::kader');

    // Balita (pola untuk semua modul siklus hidup)
    $routes->get('/balita', 'Balita::index');
    $routes->get('/balita/tambah', 'Balita::tambah');
    $routes->post('/balita/simpan', 'Balita::simpan');
    $routes->get('/balita/detail/(:num)', 'Balita::detail/$1');

    // API JSON pengganti ajax/*.php (bertahap)
    $routes->group('api', static function ($routes) {
        $routes->post('balita/cari', 'Api\\BalitaApi::cari');
        $routes->get('balita/opensid', 'Api\\BalitaApi::opensid');
        $routes->get('penduduk/cari', 'Api\\PendudukApi::cari');
        $routes->get('whatsnew', 'Api\\MetaApi::whatsnew');
    });
});
