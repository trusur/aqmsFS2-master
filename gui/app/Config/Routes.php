<?php

use CodeIgniter\Router\RouteCollection;

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/**
 * @var RouteCollection $routes
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');
$routes->get('lang/(:any)', 'Language::index');
// Configuration Route
$routes->get('configurations', 'Configuration::index');
$routes->get('configuration/raw', 'ConfigurationRaw::index');
$routes->get('configuration/raw/datatable', 'ConfigurationRaw::datatable');
$routes->post('configuration/raw/add', 'ConfigurationRaw::add');


$routes->get('parameters', 'Parameter::index');
$routes->get('calibrations', 'Calibration::index');
$routes->get('calibration/zero/{:num}', 'Calibration::zero');
$routes->get('calibration/span/{:num}', 'Calibration::span');
$routes->get('calibration/datatable', 'Calibration::datatable');
$routes->get('exports', 'Export::index');

$routes->add('/switch/pump', 'Home::pump');
