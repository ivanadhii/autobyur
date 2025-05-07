<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Dashboard::index');
$routes->post('update-control', 'Dashboard::updateControl');
$routes->get('get-sensor-data', 'Dashboard::getSensorData');
$routes->get('system-check', 'SystemCheck::index');
$routes->get('system-check/fix-time', 'SystemCheck::fixTime');
$routes->post('send-email', 'EmailController::send');
$routes->get('test-email', 'EmailController::test');
$routes->get('email-debug', 'EmailDebugController::test');
$routes->get('get-history-data', 'Dashboard::getHistoryData');
$routes->post('save-history-data', 'Dashboard::saveHistoryData');
$routes->get('export-history', 'Dashboard::exportHistoryCsv');