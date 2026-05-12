<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

$routes->group('api', function($routes) {
    // Public routes
    $routes->get('health', 'Api::health');
    $routes->get('lof-data', 'Api::getLofData');
    $routes->get('stats', 'Api::getStats');
    $routes->get('settings', 'Api::getSettings');
    $routes->post('login', 'Api::login');
    $routes->get('fund-lookup/(:segment)', 'Api::fundLookup/$1');
    $routes->get('cron-update', 'Api::cronUpdate');
    
    // Protected routes (Requires AuthFilter)
    $routes->group('', ['filter' => 'auth'], function($routes) {
        $routes->post('lof-data', 'Api::addFund');
        $routes->post('lof-data/batch', 'Api::batchAddFunds');
        $routes->put('lof-data/(:segment)', 'Api::updateFund/$1');
        $routes->delete('lof-data/(:segment)', 'Api::deleteFund/$1');
        $routes->post('settings', 'Api::saveSettings');
        $routes->post('lof-data/sync-names', 'Api::syncNames');
    });
});
