<?php
/* Copyright (C) 2020 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Script cr�ant et v�rifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require '../config.php';
} else {
	global $db;
}


/* uncomment
dol_include_once('/dolifleet/class/dolifleet.class.php');
$o=new doliFleet($db);
$o->init_db_by_vars();
*/

/* Dictionnaries */

dol_include_once('/affaire/class/affaire.class.php');
$o=new GammeDictType($db);
$o->init_db_by_vars();

dol_include_once('/affaire/class/affaire.class.php');
$o=new PdvDictType($db);
$o->init_db_by_vars();



/* Objects */

dol_include_once('/affaire/class/affaire.class.php');
$o=new Affaire($db);
$o->init_db_by_vars();