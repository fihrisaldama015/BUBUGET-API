<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */


$routes->get('/', 'Dashboard::index');
$routes->get('/login', 'Dashboard::login');

/*
* --------------------------------------------------------------------
* Resource Route
* --------------------------------------------------------------------
*/
$routes->get('api', 'Api\Home::index');
$routes->resource('api/transaction');
$routes->resource('api/category');
$routes->resource('api/budget');

/*
* --------------------------------------------------------------------
* Additional Custom Routing
* --------------------------------------------------------------------
*/
$routes->get('api/user/(:alphanum)/budget', 'Api\Budget::getUserBudget/$1');
$routes->get('api/user/(:alphanum)/budget/(:num)', 'Api\Budget::getUserBudgetByCategory/$1/$2');
$routes->get('api/user/(:alphanum)/transaction', 'Api\Transaction::getUserTransaction/$1');
$routes->get('api/user/(:alphanum)/transaction/(:num)', 'Api\Transaction::getUserTransactionByCategory/$1/$2');
$routes->get('api/user/(:alphanum)/expense', 'Api\User::getUserExpense/$1');
$routes->get('api/user/(:alphanum)/income', 'Api\User::getUserIncome/$1');
$routes->get('api/user/(:alphanum)/stats', 'Api\User::getUserStats/$1');
$routes->post('api/user/login', 'Api\User::login');
$routes->post('api/user/login_email', 'Api\User::loginWithEmail');
$routes->post('api/user/signup_email', 'Api\User::signupWithEmail');
$routes->resource('api/user');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
