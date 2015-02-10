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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    
    if (init('action') == 'postSave') {
        mySensors::stopDeamon();
        ajax::success();
    }

    if (init('action') == 'getNodeInfo') {
        ajax::success(mySensors::deamonRunning());
    }
    
     if (init('action') == 'getModuleInfo') {
        $eqLogic = mySensors::byId(init('id'));
        if (!is_object($eqLogic)) {
        throw new Exception(__('mySensors eqLogic non trouvé : ', __FILE__) . init('id'));
            }
        ajax::success($eqLogic->getInfo());
    }
    
    if (init('action') == 'getUSB') {
		$return = "";
        if (init('id') == 'master' || init('id') == 'network') {
			foreach (jeedom::getUsbMapping() as $name => $value) {
                        $return = $return . '<option value="' . $name . '">' . $name . ' (' . $value . ')</option>';
                    }
        } else {
			$jeeNetwork = jeeNetwork::byId(init('id'));
			foreach ($jeeNetwork->sendRawRequest('jeedom::getUsbMapping') as $name => $value) {
                        $return = $return . '<option value="' . $name . '">' . $name . ' (' . $value . ')</option>';
                    }
		}
		$return = $return . '<option value="serie">Port série non listé (port manuel)</option>';
		ajax::success($return);
    }

    if (init('action') == 'restartEq') {
        ajax::success(mySensors::sendToController( '32', '0', '13', '0', '3', '0' ));
    }    

    throw new Exception(__('Aucune methode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
?>
