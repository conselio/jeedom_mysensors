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

    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

    global $jsonrpc;
    if (!is_object($jsonrpc)) {
       throw new Exception(__('JSONRPC object not defined', __FILE__), -32699);
    }
    $params = $jsonrpc->getParams();
    
    if ($jsonrpc->getMethod() == 'deamonRunning') {
       log::add('mySensors','info','Vérification du statut du service');
       if (mySensors::deamonRunning()) {
		   $jsonrpc->makeSuccess('ok');
       } else {
		   $jsonrpc->makeSuccess('ko');
		}
    }
    
    if ($jsonrpc->getMethod() == 'saveConfig') {
		$nodeRun = $params['nodeRun'];
       log::add('mySensors','info','Sauvegarde de la configuration' . $nodeRun);
       mySensors::saveConfig($nodeRun);
       mySensors::stopDeamon();
       $jsonrpc->makeSuccess('ok');
    }   
    
    if ($jsonrpc->getMethod() == 'getConfig') {
       log::add('mySensors','info','Récupération de la configuration');
       $jsonrpc->makeSuccess(array('nodeHost' => config::byKey('nodeHost', 'mySensors', 0), 'nodeGateway' => config::byKey('nodeGateway', 'mySensors', 0), 'nodeSerial' => config::byKey('nodeSerial', 'mySensors', 0), 'nodeNetwork' => config::byKey('nodeNetwork', 'mySensors', 0), 'include_mode' => config::byKey('include_mode', 'mySensors', 0)));
    }      

    throw new Exception(__('Aucune methode correspondante pour le plugin mySensors : ' . $jsonrpc->getMethod(), __FILE__));
    ?>
