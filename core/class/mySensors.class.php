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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';


//ATTENTION A BIEN VERIFIER QUE WWW-DATA est bien dans le group tty
//un redemarrage complet est nécessaire


class mySensors extends eqLogic {
    /*     * *************************Attributs****************************** */
     	public static function pull($_options) {
     		$date = time();
     		log::add('mySensors', 'info', 'Cron de vérification des nodes');
		foreach (eqLogic::byType('mySensors') as $elogic) {
			log::add('mySensors', 'info', 'Vérification du node');
			//if ($elogic->getInformations('followActivity') == $elogic->getInformations('followActivity')){
				$actDate = $elogic->getInformations('LastActivity');
				log::add('mySensors', 'info', $actDate);
				$activity = strtotime($actDate);
				log::add('mySensors', 'info', $activity);
				$duration = $elogic->getInformations('AlertLimit');
				log::add('mySensors', 'info', $duration);
				$interval = round(abs($to_time - $from_time) / 60,2);
				log::add('mySensors', 'info', $interval);
				if ($interval > $duration) {
					$gate = self::byLogicalId('gateway', 'mySensors');
					$value = $elogic->getName();
					$cmdlogic = mySensorsCmd::byEqLogicIdAndLogicalId($gate->getId(),'Inactif');
					$cmdlogic->setConfiguration('value',$value);
					$cmdlogic->save();
					$cmdlogic->event($value);
					}
			//	}
			}
		}

	public static $_dico = 
			array(
			'C' => array( 
				'PRESENTATION'=> 0,
				'PARAMETRAGE'=> 1,
				'INTERNE'=> 3,
				'OTA'=> 4,
				),
			'U' => array( // Unité
				'Entrée'=> 0,
				'Mouvement'=> 1,
				'Fumée'=> 2,
				'Relais'=> 3,
				'%'=> 4,
				'S_COVER'=> 5,
				'°C'=> 6,
				'%'=> 7,
				'hPa'=> 8,
				'Anémomètre'=> 9,
				'Pluie'=> 10,
				'UV'=> 11,
				'Kg'=> 12,
				'W'=> 13,
				'Chauffage'=> 14,
				'cm'=> 15,
				'Lux'=> 16,
				'S_ARDUINO_NODE'=> 17,
				'Repeteur'=> 18,
				'S_LOCK'=> 19,
				'S_IR'=> 20,
				'L'=> 21,
				'Qualité Air'=> 22
			 ),
			'I' => array( 
				'I_BATTERY_LEVEL'=> 0,
				'I_TIME'=> 1,
				'I_VERSION'=> 2,
				'I_ID_REQUEST'=> 3,
				'I_ID_RESPONSE'=> 4,
				'I_INCLUSION_MODE'=> 5,
				'I_CONFIG'=> 6,
				'I_PING'=> 7,
				'I_PING_ACK'=> 8,
				'I_LOG_MESSAGE'=> 9,
				'I_CHILDREN'=> 10,
				'I_SKETCH_NAME'=> 11,
				'I_SKETCH_VERSION'=> 12,
				'I_REBOOT'=> 13,
			 ),
			'N' => array( // Type de Capteur / Actionneur
				'Entrée'=> 0,
				'Mouvement'=> 1,
				'Fumée'=> 2,
				'Relais'=> 3,
				'Variateur'=> 4,
				'S_COVER'=> 5,
				'Temperature'=> 6,
				'Humidité'=> 7,
				'Pression'=> 8,
				'Anémomètre'=> 9,
				'Pluie'=> 10,
				'UV'=> 11,
				'Poids'=> 12,
				'Energie'=> 13,
				'Chauffage'=> 14,
				'Distance'=> 15,
				'Niveau Lumiere'=> 16,
				'S_ARDUINO_NODE'=> 17,
				'Repeteur'=> 18,
				'S_LOCK'=> 19,
				'S_IR'=> 20,
				'Eau'=> 21,
				'Qualité Air'=> 22
			 ),
			'A' => array( // Actionneur
				'Relais'=> 2,
				'Variateur'=> 3,
				'V_FORECAST'=> 5,
				'DIRECTION'=> 10, //Direction du vent
				'UV'=> 11,
				'V_IMPEDANCE'=> 14,
				'V_ARMED'=> 15,
				'Entrée'=> 16,
				'SCENE_ON'=> 19,
				'SCENE_OFF'=> 20,
				'Radiateur'=> 21,
				'Radiateur ON/OFF'=> 22,
				'VAR1'=> 24,
				'VAR2'=> 25,
				'VAR3'=> 26,
				'VAR4'=> 27,
				'VAR5'=> 28,
				'V_UP'=> 29,
				'V_DOWN'=> 30,
				'V_STOP'=> 31,
				'IR_SEND'=> 32,
				'V_FLOW'=> 34,
				'V_LOCK_STATUS'=> 36,
				'V_DUST_LEVEL'=> 37,
				)
			);

    /************************Methode static*************************** */

	public static function cron() {
        
        if (config::byKey('externalDeamon', 'mySensors', 0) != 2) {
		$modem_serie_addr = config::byKey('usbGateway', 'mySensors');
		if($modem_serie_addr == "serie") {
			$usbGateway = config::byKey('modem_serie_addr', 'mySensors');
		} else if ($modem_serie_addr == "network") {
			$usbGateway = config::byKey('gateway_addr', 'mySensors');
		} else {
			$usbGateway = jeedom::getUsbMapping(config::byKey('usbGateway', 'mySensors'));
		}

		if ($usbGateway != '' && file_exists( $usbGateway )) {
            		if (!self::deamonRunning()) {
                		self::runDeamon();
            		}
            	message::removeAll('mySensors', 'noMySensorsPort');
        	} else {
            		log::add('mySensors', 'error', __('Le port du mySensors est vide ou n\'éxiste pas', __FILE__), 'noMySensorsPort');
        	}
        }
    }
	
	public static function runDeamon() {
        log::add('mySensors', 'info', 'Lancement du démon mySensors');
        
		$modem_serie_addr = config::byKey('usbGateway', 'mySensors');
		if($modem_serie_addr == "serie") {
			$usbGateway = config::byKey('modem_serie_addr', 'mySensors');
		} else {
			$usbGateway = jeedom::getUsbMapping(config::byKey('usbGateway', 'mySensors'));
		}
		
		if($modem_serie_addr == "network") {
			$gateMode = "Network";
			$netAd = explode(":",config::byKey('gateway_addr', 'mySensors'));
			$usbGateway = $netAd[0];
			$gatePort = $netAd[1];	
		} else {
			$gateMode = "Serial";
			$gatePort = "";	
		}
		
		if ($usbGateway == '' ) {
			throw new Exception(__('Le port : ', __FILE__) . $port . __(' n\'éxiste pas', __FILE__));
		}
		
		if (config::byKey('jeeNetwork::mode') == 'slave') { //Je suis l'esclave
			$url  = 'http://' . config::byKey('jeeNetwork::master::ip') . '/core/api/jeeApi.php?api=' . config::byKey('jeeNetwork::master::apikey');
		} else {
			if (stripos(config::byKey('internalAddr'), 'jeedom') !== FALSE) {
				//on est pas sur une Mini
				$jeeurl = "http://127.0.0.1/jeedom";
			} else {
				$jeeurl = "http://127.0.0.1";
			}
			$url = $jeeurl . '/core/api/jeeApi.php?api=' . config::byKey('api');
		}
	
	$sensor_path = realpath(dirname(__FILE__) . '/../../node');	
        $cmd = 'nice -n 19 node ' . $sensor_path . '/mysensors.js ' . $url . ' ' . $usbGateway . ' ' . $gateMode . ' ' . $gatePort;
		
        log::add('mySensors', 'info', 'Lancement démon mySensors : ' . $cmd);
        $result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('mySensors') . ' 2>&1 &');
        if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
            log::add('mySensors', 'error', $result);
            return false;
        }
        sleep(2);
        if (!self::deamonRunning()) {
            sleep(10);
            if (!self::deamonRunning()) {
                log::add('mySensors', 'error', 'Impossible de lancer le démon mySensors, vérifiez le port', 'unableStartDeamon');
                return false;
            }
        }
        message::removeAll('mySensors', 'unableStartDeamon');
        log::add('mySensors', 'info', 'Démon mySensors lancé');
    }
	
	
	public static function deamonRunning() {
   
		$pid = trim( shell_exec ('ps ax | grep "mySensors/node/mysensors.js" | grep -v "grep" | wc -l') );
		
		if ($pid != '' && $pid != '0') {
                return true;
        }

        return false;
    }

    public static function stopDeamon() {
        if (!self::deamonRunning())
			return true;
			
		$pid = trim(shell_exec('ps ax | grep "mySensors/node/mysensors.js" | grep -v "grep" | awk \'{print $1}\''));
		if ( $pid == '' ){
			return true;
		}
		
        exec('kill ' . $pid);
        $check = self::deamonRunning();
        $retry = 0;
        while ($check) {
            $check = self::deamonRunning();
            $retry++;
            if ($retry > 10) {
                $check = false;
            } else {
                sleep(1);
            }
        }
        exec('kill -9 ' . $pid);
        $check = self::deamonRunning();
        $retry = 0;
        while ($check) {
            $check = self::deamonRunning();
            $retry++;
            if ($retry > 10) {
                $check = false;
            } else {
                sleep(1);
            }
        }

        return self::deamonRunning();
    }
	
	/**
	* retourne le numéro du prochain mysensorid dispo
	*/
	protected static function getNextSensorId() {
	
		$max = 0;

		//recherche dans tous les eqlogic 
		foreach( self::byType( 'mySensors' ) as $elogic) {
		
			if ($max <  $elogic->getConfiguration('nodeid') ) {
				$max = $elogic->getConfiguration('nodeid');
			}
		}
		return $max + 1;
	}
	
	
	public static function sendToController( $destination, $sensor, $command, $acknowledge, $type, $payload ) {
		if (config::byKey('externalDeamon', 'mySensors', 0) == 2) {
			$jeeSlave = jeeNetwork::byId(config::byKey('jeeSlave', 'mySensors'));
			$urlNode = getIpFromString($jeeSlave->getIp());
		} else {
			$urlNode = "127.0.0.1";
		}
		log::add('mySensors', 'info', $urlNode);
		$msg = $destination . ";" . $sensor . ";" . $command . ";" . $acknowledge . ";" .$type . ";" . $payload;
		log::add('mySensors', 'info', $msg);
		$fp = fsockopen($urlNode, 8019, $errno, $errstr);
		   if (!$fp) {
		   echo "ERROR: $errno - $errstr<br />\n";
		} else {
	
		   fwrite($fp, $msg);
		   fclose($fp);
		}

	}
	
	public static function saveValue() {
		$nodeid = init('id');
		$sensor = init('sensor');
		$value = init('value');
		$typu = init('typu');
		$cmdId = 'Sensor'.$sensor;
		$elogic = self::byLogicalId($nodeid, 'mySensors');
		if (is_object($elogic)) { 
			$date = date('d-m-Y H:i');
			$elogic->setConfiguration('LastActivity', $date);
			$elogic->save();
			$cmdlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$cmdId);
			if (is_object($cmdlogic)) {
				$cmdlogic->setConfiguration('value', $value);
				$cmdlogic->save();
				$cmdlogic->event($value);
			}
		}
	}
	
	public static function saveBatteryLevel() {
		$nodeid = init('id');
		$value = init('value');
		$elogic = self::byLogicalId($nodeid, 'mySensors');
		if (is_object($elogic)) { 
			$date = date('d-m-Y H:i');
			$elogic->setConfiguration('LastActivity', $date);
			$elogic->save();			
			$cmdlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),'BatteryLevel');
			if (is_object($cmdlogic)) {
				if ( $cmdlogic->getConfiguration('value') != $value ) {
					$cmdlogic->setConfiguration('value',$value);
					$cmdlogic->save();
					$cmdlogic->event($value);
				}
			}
			else {
				$mysCmd = new mySensorsCmd();
				$mysCmd->setCache('enable', 0);
				$mysCmd->setEventOnly(0);
				$mysCmd->setConfiguration('sensorType', '0');
				$mysCmd->setConfiguration('sensor', '0');
				$mysCmd->setEqLogic_id($elogic->getId());
				$mysCmd->setEqType('mySensors');
				$mysCmd->setLogicalId('BatteryLevel');
				$mysCmd->setType('info');
				$mysCmd->setSubType('numeric');
				$mysCmd->setName( 'Batterie' );
				$mysCmd->setUnite( '%' );
				$mysCmd->setConfiguration('value',$value);
				$mysCmd->setTemplate("dashboard","batterie" );
				$mysCmd->save();
				$mysCmd->event($value);
			}				
		}
	
	}
	
	public static function saveSketchNameEvent() {
		$nodeid = init('id');
		$value = init('value');
		$elogic = self::byLogicalId($nodeid, 'mySensors');
		if (is_object($elogic)) {
				if ( $elogic->getConfiguration('SketchName', '') != $value ) {
					$elogic->setConfiguration('SketchName',$value);
					//si le sketch a changé sur le node, alors on set le nom avec le sketch
					$elogic->setName($value.''.$nodeid);					
					$elogic->save();
				}
		}
		else {
				$mys = new mySensors();
				$mys->setEqType_name('mySensors');
				$mys->setLogicalId($nodeid);
				$mys->setConfiguration('nodeid', $nodeid);
				$mys->setConfiguration('SketchName',$value);
				$mys->setName($value.'-'.$nodeid);
				$mys->setIsEnable(true);
				$mys->save();
					$mysCmd = new mySensorsCmd();
					$mysCmd->setEventOnly(0);
					$mysCmd->setConfiguration('cmdCommande', '3');
					$mysCmd->setConfiguration('request', '0');
					$mysCmd->setConfiguration('cmdtype', '3');
					$mysCmd->setConfiguration('sensorType', '13');
					$mysCmd->setConfiguration('sensor', '0');
					$mysCmd->setEqLogic_id($mys->getId());
					$mysCmd->setEqType('mySensors');
					$mysCmd->setLogicalId('reboot');
					$mysCmd->setType('action');
					$mysCmd->setSubType('other');
					$mysCmd->setName( "Reboot Node" );
					$mysCmd->save();				
		}
	}
	
	public static function saveGateway() {
		$status = init('status');
		$elogic = self::byLogicalId('gateway', 'mySensors');
		if (is_object($elogic)) {
			$cmdlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),'Connexion');
			if (is_object($cmdlogic)) {
				$cmdlogic->setConfiguration('value',$value);
				$cmdlogic->save();
				$cmdlogic->event($value);
			}
			else {
				$mysCmd = new mySensorsCmd();
				$mysCmd->setEqLogic_id($elogic->getId());
				$mysCmd->setEqType('mySensors');
				$mysCmd->setLogicalId('Connexion');
				$mysCmd->setType('info');
				$mysCmd->setSubType('numeric');
				$mysCmd->setName( 'Connexion' );
				$mysCmd->setConfiguration('value',$status);
				$mysCmd->save();
				$mysCmd->event($value);
				$mysCmd = new mySensorsCmd();
				$mysCmd->setEqLogic_id($elogic->getId());
				$mysCmd->setEqType('mySensors');
				$mysCmd->setLogicalId('Inactif');
				$mysCmd->setType('info');
				$mysCmd->setSubType('other');
				$mysCmd->setName( 'Inactif' );
				$mysCmd->save();
			}	
		}
		else {
				$mys = new mySensors();
				$mys->setEqType_name('mySensors');
				$mys->setLogicalId('gateway');
				$mys->setConfiguration('nodeid', 'gateway');
				$mys->setName('Gateway');
				$mys->setIsEnable(true);
				$mys->save();
				$mysCmd = new mySensorsCmd();
				$mysCmd->setEqLogic_id($mys->getId());
				$mysCmd->setEqType('mySensors');
				$mysCmd->setLogicalId('Connexion');
				$mysCmd->setType('info');
				$mysCmd->setSubType('numeric');
				$mysCmd->setName( 'Connexion' );
				$mysCmd->setConfiguration('value',$status);
				$mysCmd->save();
				$mysCmd->event($value);
		}
	}	

	public static function saveSketchVersion() {
		$nodeid = init('id');
		$value = init('value');
		$elogic = self::byLogicalId($nodeid, 'mySensors');
		sleep(1);
		if (is_object($elogic)) { 
			if ( $elogic->getConfiguration('SketchVersion', '') != $value ) {
				$elogic->setConfiguration('SketchVersion',$value);
				$elogic->save();
			}
		}
	}
	
	public static function saveLibVersion() {
		sleep(1);
		$nodeid = init('id');
		$value = init('value');
		$elogic = self::byLogicalId($nodeid, 'mySensors');
		if (is_object($elogic)) { 
			if ( $elogic->getConfiguration('LibVersion', '') != $value ) {
				$elogic->setConfiguration('LibVersion',$value);
				$elogic->save();
			}
		}
	}	
	
	public static function saveSensor() {
		sleep(1);
		$nodeid = init('id');
		$value = init('value');
		$sensor = init('sensor');
		$name = array_search($value, self::$_dico['N']);
		if ($name == false ) {
			$name = 'UNKNOWN';
		}
		$unite = array_search($value, self::$_dico['U']);
		if ($unite == false ) {
			$unite = 'UNKNOWN';
		}		
		$cmdId = 'Sensor'.$sensor;
		$elogic = self::byLogicalId($nodeid, 'mySensors');
		if (is_object($elogic)) {
			$cmdlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$cmdId);
			if (is_object($cmdlogic)) {
				if ( $cmdlogic->getConfiguration('sensorType', '') != $value ) {
					$cmdlogic->setConfiguration('sensorType', $value);
					$cmdlogic->save();
				}
			}
			else {
				$mysCmd = new mySensorsCmd();
				$mysCmd->setCache('enable', 0);
				$mysCmd->setEventOnly(0);
				$mysCmd->setConfiguration('sensorType', $value);
				$mysCmd->setConfiguration('sensor', $sensor);
				$mysCmd->setEqLogic_id($elogic->getId());
				$mysCmd->setEqType('mySensors');
				$mysCmd->setLogicalId($cmdId);
				$mysCmd->setType('info');
				$mysCmd->setSubType('numeric');
				$mysCmd->setName( $name . " " . $sensor );
				$mysCmd->setUnite( $unite );
				if ($name == 'Relais') {
					$mysCmd->setTemplate("dashboard","light" );
				} else if ($name == 'Variateur') {
					$mysCmd->setTemplate("dashboard","progressBar" );
				} else if ($name == 'Temperature') {
					$mysCmd->setTemplate("dashboard","gauge" );
				} else if ($name == 'Humidité') {
					$mysCmd->setTemplate("dashboard","vuMeter" );
				} else if ($name == 'Mouvement') {
					$mysCmd->setTemplate("dashboard","vibration" );
				} else {
					$mysCmd->setTemplate("dashboard","badge" );
				}
				$mysCmd->save();
			}
			if ($name == 'Relais') {
				$relonId = 'Relais'.$sensor.'On';
				$reloffId = 'Relais'.$sensor.'Off';
				$onlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$relonId);
				$offlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$reloffId);
				if (!is_object($onlogic)) {
					$mysCmd = new mySensorsCmd();
					$mysCmd->setEventOnly(0);
					$mysCmd->setConfiguration('cmdCommande', '1');
					$mysCmd->setConfiguration('request', '1');
					$mysCmd->setConfiguration('cmdtype', '2');
					$mysCmd->setConfiguration('sensorType', $value);
					$mysCmd->setConfiguration('sensor', $sensor);
					$mysCmd->setEqLogic_id($elogic->getId());
					$mysCmd->setEqType('mySensors');
					$mysCmd->setLogicalId($relonId);
					$mysCmd->setType('action');
					$mysCmd->setSubType('other');
					$mysCmd->setName( $name . " " . $sensor . " On" );
					$mysCmd->save();
				}
				if (!is_object($offlogic)) {
					$mysCmd = new mySensorsCmd();
					$mysCmd->setEventOnly(0);
					$mysCmd->setConfiguration('cmdCommande', '1');
					$mysCmd->setConfiguration('request', '0');
					$mysCmd->setConfiguration('cmdtype', '2');
					$mysCmd->setConfiguration('sensorType', $value);
					$mysCmd->setConfiguration('sensor', $sensor);
					$mysCmd->setEqLogic_id($elogic->getId());
					$mysCmd->setEqType('mySensors');
					$mysCmd->setLogicalId($reloffId);
					$mysCmd->setType('action');
					$mysCmd->setSubType('other');
					$mysCmd->setName( $name . " " . $sensor . " Off" );
					$mysCmd->save();
				}
			}
			if ($name == 'Variateur') {
				$dimmerId = 'Dimmer'.$sensor;
				$dimlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$dimmerId);
				if (!is_object($dimlogic)) {
					$mysCmd = new mySensorsCmd();
					$mysCmd->setEventOnly(0);
					$mysCmd->setConfiguration('cmdCommande', '1');
					$mysCmd->setConfiguration('request', '');
					$mysCmd->setConfiguration('cmdtype', '2');
					$mysCmd->setConfiguration('sensorType', $value);
					$mysCmd->setConfiguration('sensor', $sensor);
					$mysCmd->setEqLogic_id($elogic->getId());
					$mysCmd->setEqType('mySensors');
					$mysCmd->setLogicalId($dimmerId);
					$mysCmd->setType('action');
					$mysCmd->setSubType('other');
					$mysCmd->setName( $name . " " . $sensor . " Set" );
					$mysCmd->save();
				}				
			}
			
		}

	
		
	
	}

	
    public static function event() {

		$messageType = init('messagetype');
		switch ($messageType) {
		
			case 'saveValue' : self::saveValue(); break;
			case 'saveSketchName' : self::saveSketchNameEvent(); break;
			case 'saveSketchVersion' : self::saveSketchVersion(); break;
			case 'saveLibVersion' : self::saveLibVersion(); break;
			case 'saveSensor' : self::saveSensor(); break;
			case 'saveBatteryLevel' : self::saveBatteryLevel(); break;
			case 'saveGateway' : self::saveGateway(); break;
		
		}
		
	
	/*
        $cmd = mySensorsCmd::byId(init('id'));
        if (!is_object($cmd)) {
            throw new Exception('Commande ID virtuel inconnu : ' . init('id'));
        }
        $value = init('value');
        $virtualCmd = virtualCmd::byId($cmd->getConfiguration('infoId'));
        if (is_object($virtualCmd)) {
            if ($virtualCmd->getEqLogic()->getEqType_name() != 'virtual') {
                throw new Exception(__('La cible de la commande virtuel n\'est pas un équipement de type virtuel', __FILE__));
            }
            if ($this->getSubType() != 'slider' && $this->getSubType() != 'color') {
                $value = $this->getConfiguration('value');
            }
            $virtualCmd->setConfiguration('value', $value);
            $virtualCmd->save();
        } else {
            $cmd->setConfiguration('value', $value);
            $cmd->save();
        }
        $cmd->event($value);
		
    }*/

    /*     * *********************Methode d'instance************************* */


    /*     * **********************Getteur Setteur*************************** */
	}
	
}

class mySensorsCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

	
	public function execute($_options = null) {


            switch ($this->getType()) {
			
				case 'info' : 
					return $this->getConfiguration('value');
					break;
					
                case 'action' :
					$request = $this->getConfiguration('request');
					
                    switch ($this->getSubType()) {
                        case 'slider':
                            $request = str_replace('#slider#', $_options['slider'], $request);
                            break;
                        case 'color':
                            $request = str_replace('#color#', $_options['color'], $request);
                            break;
                        case 'message':
							if ($_options != null)  {
								
								$replace = array('#title#', '#message#');
								$replaceBy = array($_options['title'], $_options['message']);
								if ( $_options['title'] == '') {
									throw new Exception(__('Le sujet ne peuvent être vide', __FILE__));
								}
								$request = str_replace($replace, $replaceBy, $request);
							
							}
							else	
							 $request = 1;
						
                            break;
						default : $request == null ?  1 : $request;
						
					}
						
					$eqLogic = $this->getEqLogic();
					
					mySensors::sendToController( 
						$eqLogic->getConfiguration('nodeid') ,
						$this->getConfiguration('sensor'),
						$this->getConfiguration('cmdCommande'),
						1,
						$this->getConfiguration('cmdtype'),
						$request ); 
					
					$result = $request;

					
					return $result;
			}
			
			return true;
		
    }
	
     

    /*     * **********************Getteur Setteur*************************** */
}

