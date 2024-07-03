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
$routes->post('configuration/update', 'Configuration::update');

$routes->get('configuration/drivers/datatable', 'Configuration::datatable_drivers');
$routes->get('configuration/get-driver/(:num)', 'Configuration::get_driver/$1');
$routes->post('configuration/add-driver', 'Configuration::add_driver');
$routes->post('configuration/edit-driver', 'Configuration::edit_driver');
$routes->post('configuration/delete-driver/(:num)', 'Configuration::delete_driver/$1');
// 
$routes->get('configuration/raw', 'ConfigurationRaw::index');
$routes->get('configuration/raw/datatable', 'ConfigurationRaw::datatable');
$routes->post('configuration/raw/add', 'ConfigurationRaw::add');

$routes->get('configuration/mainboard', 'Mainboard::index');
$routes->post('configuration/mainboard', 'Mainboard::store');
$routes->get('configuration/mainboard/json/(:num)', 'Mainboard::show/$1');
$routes->post('configuration/mainboard/(:num)', 'Mainboard::update/$1');
$routes->get('configuration/mainboard/delete/(:num)', 'Mainboard::delete/$1');


$routes->get('parameters', 'Parameter::index');
$routes->get('parameter/(:num)', 'Parameter::get/$1');
$routes->post('parameter/edit', 'Parameter::edit');
$routes->get('parameter/sensor-value', 'Parameter::sensor_value');

$routes->get('calibrations', 'Calibration::index');
$routes->post('calibrations', 'Calibration::store');
$routes->get('calibration/configuration', 'Calibration\CalibrationConfiguration::index');
$routes->post('calibration/configuration', 'Calibration\Calibrat/ionConfiguration::update');
$routes->get('calibration/logs', 'Calibration::logs');
$routes->get('calibration/log/(:num)', 'Calibration::detail_log/$1');
$routes->get('calibration/log/calibration-log/(:num)', 'Calibration::get_calibration_logs/$1');
$routes->get('calibration/datatable-logs', 'Calibration::datatable_logs');

$routes->get('calibration/span/(:num)', 'Calibration::span/$1');
$routes->post('calibration/span/(:num)', 'Calibration::stopSpan/$1');
// $routes->get('calibration/zero/{:num}', 'Calibration::zero');
// $routes->get('calibration/datatable', 'Calibration::datatable');


$routes->get('exports', 'Export::index');
$routes->get('export/csv', 'Export::export');

$routes->get('rht', 'Rht::index');
$routes->get('rht/realtime', 'Rht::get_all');

$routes->add('/switch/pump', 'Home::pump');
