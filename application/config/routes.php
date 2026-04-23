<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -----------------------------------------------------------------------
| ROUTING SPK SAW
| -----------------------------------------------------------------------
*/

// Default langsung ke dashboard admin
$route['default_controller'] = 'admin/dashboard';

// Rute eksplisit admin
$route['admin']                = 'admin/dashboard';
$route['admin/dashboard']      = 'admin/dashboard';
$route['admin/alternatif']     = 'admin/alternatif';
$route['admin/kriteria']       = 'admin/kriteria';
$route['admin/nilai']          = 'admin/nilai';
$route['admin/hasil']          = 'admin/hasil';

// Rute user (untuk nanti)
$route['user']                 = 'user/dashboard';
$route['user/dashboard']       = 'user/dashboard';
$route['user/hasil']           = 'user/hasil';

// Auth
$route['auth/login']           = 'auth/login';
$route['auth/logout']          = 'auth/logout';
$route['auth/register']        = 'auth/register';

// 404
$route['404_override']         = '';
$route['translate_uri_dashes'] = FALSE;
