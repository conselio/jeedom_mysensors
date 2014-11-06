<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/*
#
#  ______                             _  _                                
# / _____)                           | |(_)                               
#| /        ___   ____    ___   ____ | | _   ___       ____   ___   ____  
#| |       / _ \ |  _ \  /___) / _  )| || | / _ \     / ___) / _ \ |    \ 
#| \_____ | |_| || | | ||___ |( (/ / | || || |_| | _ ( (___ | |_| || | | |
# \______) \___/ |_| |_|(___/  \____)|_||_| \___/ (_) \____) \___/ |_|_|_|
#
*/

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

/*
function install() {
    if (mySensors::deamonRunning()) {
        mySensors::stopDeamon();
    }
}

function update() {
    if (mySensors::deamonRunning()) {
        mySensors::stopDeamon();
    }
}

function remove() {
    if (mySensors::deamonRunning()) {
        mySensors::stopDeamon();
    }
}

*/

function mySensors_install() {
    $cron = cron::byClassAndFunction('mySensors', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
}

function mySensors_update() {
    $cron = cron::byClassAndFunction('mySensors', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
    if (method_exists('mySensors', 'stopDeamon')) {
        mySensors::stopDeamon();
    }
}

function mySensors_remove() {
    $cron = cron::byClassAndFunction('mySensors', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
    if (method_exists('mySensors', 'stopDeamon')) {
        mySensors::stopDeamon();
    }
}

?>
