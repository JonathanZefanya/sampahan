<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ── Public / Guest ───────────────────────────────────────────────────────────
$routes->get('/',              'Public\LandingController::index');
$routes->get('peta-sebaran',   'Public\LandingController::map');
$routes->get('api/reports/geojson', 'Public\LandingController::geojson');

// ── Authentication ────────────────────────────────────────────────────────────
$routes->group('auth', function (RouteCollection $routes) {
    $routes->get('login',             'Auth\AuthController::login');
    $routes->post('login',            'Auth\AuthController::attempt');
    $routes->get('logout',            'Auth\AuthController::logout');
    $routes->get('register',          'Auth\AuthController::register');
    $routes->post('register',         'Auth\AuthController::store');
    $routes->get('activate/(:segment)', 'Auth\AuthController::activate/$1');

    // Forgot / Reset Password
    $routes->get('forgot-password',             'Auth\ForgotPasswordController::index');
    $routes->post('forgot-password',            'Auth\ForgotPasswordController::send');
    $routes->get('reset-password/(:segment)',   'Auth\ForgotPasswordController::reset/$1');
    $routes->post('reset-password/(:segment)',  'Auth\ForgotPasswordController::update/$1');
});

// ── Admin ─────────────────────────────────────────────────────────────────────
$routes->group('admin', ['filter' => ['auth', 'role:admin']], function (RouteCollection $routes) {
    $routes->get('dashboard',               'Admin\DashboardController::index');

    // Settings
    $routes->get('settings',                'Admin\SettingsController::index');
    $routes->post('settings',               'Admin\SettingsController::save');
    $routes->post('settings/upload-logo',   'Admin\SettingsController::uploadLogo');
    $routes->post('settings/upload-favicon','Admin\SettingsController::uploadFavicon');
    $routes->post('settings/upload-geojson','Admin\SettingsController::uploadGeoJson');

    // Report management
    $routes->get('reports',                     'Admin\ReportController::index');
    $routes->get('reports/(:num)',              'Admin\ReportController::detail/$1');
    $routes->post('reports/(:num)/status',      'Admin\ReportController::updateStatus/$1');
    $routes->post('reports/bulk',               'Admin\ReportController::bulk');
    $routes->post('reports/(:num)/delete',      'Admin\ReportController::delete/$1');

    // User management
    $routes->get('users',                   'Admin\UserController::index');
    $routes->get('users/new',               'Admin\UserController::create');
    $routes->post('users',                  'Admin\UserController::store');
    $routes->get('users/(:num)/edit',       'Admin\UserController::edit/$1');
    $routes->post('users/(:num)',           'Admin\UserController::update/$1');
    $routes->post('users/(:num)/toggle',    'Admin\UserController::toggle/$1');

    // Profile
    $routes->get('profile',                 'Admin\ProfileController::index');
    $routes->post('profile',                'Admin\ProfileController::update');
});

// ── Dinas ─────────────────────────────────────────────────────────────────────
$routes->group('dinas', ['filter' => ['auth', 'role:dinas,admin']], function (RouteCollection $routes) {
    $routes->get('dashboard',                    'Dinas\DashboardController::index');
    $routes->get('map',                          'Dinas\MapController::index');
    $routes->post('reports/(:num)/advance',      'Dinas\MapController::advance/$1');
    $routes->post('reports/(:num)/reject',       'Dinas\MapController::reject/$1');
    $routes->get('reports/(:num)',               'Dinas\MapController::detail/$1');
    $routes->get('api/reports/geojson',          'Dinas\MapController::geojson');
    $routes->get('profile',                      'Dinas\ProfileController::index');
    $routes->post('profile',                     'Dinas\ProfileController::update');
});

// ── Masyarakat ────────────────────────────────────────────────────────────────
$routes->group('masyarakat', ['filter' => ['auth', 'role:masyarakat,admin']], function (RouteCollection $routes) {
    $routes->get('dashboard',           'Masyarakat\DashboardController::index');
    $routes->get('report',              'Masyarakat\ReportController::create');
    $routes->post('report',             'Masyarakat\ReportController::store');
    $routes->get('history',             'Masyarakat\ReportController::history');
    $routes->get('history/(:num)',      'Masyarakat\ReportController::detail/$1');
    $routes->get('profile',             'Masyarakat\ProfileController::index');
    $routes->post('profile',            'Masyarakat\ProfileController::update');
});

