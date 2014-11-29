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
			log::add('mySensors', 'info', 'Vérification du node' . $elogic->getName());
			if ($elogic->getConfiguration('followActivity') == '1'){
				log::add('mySensors', 'info', $elogic->getName() . ' en surveillance');
				$actDate = $elogic->getConfiguration('LastActivity');
				log::add('mySensors', 'info', 'Derniere Activite ' . $actDate);
				$activity = strtotime($actDate);
				$duration = $elogic->getConfiguration('AlertLimit');
				log::add('mySensors', 'info', 'Interval paramétré ' . $duration);
				$interval = round(abs($date - $activity) / 60,2);
				log::add('mySensors', 'info', 'Durée d inactivité ' . $interval);
				if ($interval > $duration) {
					log::add('mySensors', 'info', 'Délai dépassé pour ' . $elogic->getName());
					$gate = self::byLogicalId('gateway', 'mySensors');
					$value = $elogic->getName();
					$cmdlogic = mySensorsCmd::byEqLogicIdAndLogicalId($gate->getId(),'Inactif');
					$cmdlogic->setConfiguration('value',$value);
					$cmdlogic->save();
					$cmdlogic->event($value);
					}
				}
			}
		}

	public static $_dico = 
			array(
			'C' => array( 
				'Présentation'=> 0,
				'Paramétrage'=> 1,
				'Interne'=> 3,
				'OTA'=> 4,
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
				'Température'=> 0,
				'Humidité'=> 1,
				'Relais'=> 2,
				'Dimmer'=> 3,
				'Pression'=> 4,
				'Prévision'=> 5,
				'Niveau de pluie'=> 6,
				'Débit de pluie'=> 7,
				'Vitesse de vent'=> 8,
				'Rafale de vent'=> 9,
				'Direction du vent'=> 10,
				'UV'=> 11,
				'Poids'=> 12,
				'Distance'=> 13,
				'Impédance'=> 14,
				'Sécurité activée'=> 15,
				'Activation'=> 16,
				'Puissance'=> 17,
				'KWh'=> 18,
				'Activation Scène'=> 19,
				'Désactivation Scène'=> 20,
				'Mode de chauffage'=> 21,
				'Radiateur'=> 22,
				'Niveau de Lumière'=>23,
				'Variable1'=>24,
				'Variable2'=>25,
				'Variable3'=>26,
				'Variable4'=>27,
				'Variable5'=>28,
				'Lever'=>29,
				'Descente'=>30,
				'Arrêt'=>31,
				'Envoi IR'=>32,
				'Réception IR'=>33,
				'Débit Eau'=>34,
				'Volume Eau'=>35,
				'Verrou'=>36,
				'Poussière'=>37,
				'Voltage'=>38,
				'Courant'=>39,
				'Connexion'=>97,
				'Inactivité'=>98,
				'Batterie'=>99
			 ),
			 'S' => array( // 'S_TYPE', 'Nom', 'widget', 'variable, 'unité', 'historique', 'affichage'
				0 => array('S_DOOR','Ouverture','door','binary','','','1',),
				1 => array('S_MOTION','Mouvement','presence','binary','','','1',),
				2 => array('S_SMOKE','Fumée','presence','binary','','','1',),
				3 => array('S_LIGHT','Relais','light','binary','','','',),
				4 => array('S_DIMMER','Variateur','light','numeric','%','','',),
				5 => array('S_COVER','Store','store','string','','','1',),
				6 => array('S_TEMP','Température','thermometre','numeric','°C','1','1',),
				7 => array('S_HUM','Humidité','humidite','numeric','%','1','1',),
				8 => array('S_BARO','Baromètre','tile','string','Pa','1','1',),
				9 => array('S_WIND','Vent','tile','string','','','1',),
				10 => array('S_RAIN','Pluie','tile','numeric','cm','1','1',),
				11 => array('S_UV','UV','tile','numeric','uvi','1','1',),
				12 => array('S_WEIGHT','Poids','tile','numeric','kg','1','1',),
				13 => array('S_POWER','Energie','tile','numeric','','1','1',),
				14 => array('S_HEATER','Radiateur','tile','string','','','1',),
				15 => array('S_DISTANCE','Distance','tile','numeric','cm','','1',),
				16 => array('S_LIGHT_LEVEL','Luminosité','tile','numeric','','','1',),
				17 => array('S_ARDUINO_NODE','Noeud Arduino','tile','string','','','1',),
				18 => array('S_ARDUINO_RELAY','Noeud Répéteur','tile','string','','','1',),
				19 => array('S_LOCK','Verrou','presence','binary','','','1',),
				20 => array('S_IR','Infrarouge','tile','string','','','1',),
				21 => array('S_WATER','Eau','tile','numeric','','1','1',),
				22 => array('S_AIR_QUALITY','Qualité d Air','tile','numeric','','1','1',),
				23 => array('S_CUSTOM','Custom','tile','string','','','1',),
				24 => array('S_DUST','Poussière','tile','numeric','mm','1','1',),
				25 => array('S_SCENE_CONTROLLER','Controlleur de Scène','tile','binary','','','1',),
				97 => array('GATEWAY','Connexion avec Gateway','tile','string','','','',),
				98 => array('INNA_NODE','Inactivité des Nodes','tile','string','','','',),
				99 => array('BATTERIE','Etat de la batterie','tile','numeric','%','','1',)
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
		$type = init('donnees');
		$daType = self::$_dico['N'][$type];
		$cmdId = 'Sensor'.$sensor;
		$elogic = self::byLogicalId($nodeid, 'mySensors');
		if (is_object($elogic)) { 
			$date = date('d-m-Y H:i');
			$elogic->setConfiguration('LastActivity', $date);
			$elogic->save();
			$cmdlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$cmdId);
			if (is_object($cmdlogic)) {
				$cmdlogic->setConfiguration('value', $value);
				$cmdlogic->setConfiguration('sensorType', $dataType);
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
				$mysCmd->setConfiguration('sensorCategory', 'Batterie');
				$mysCmd->setConfiguration('sensorType', 'Batterie');
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
				$mys->setName($value.' - '.$nodeid);
				$mys->setIsEnable(true);
				$mys->save();
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
				$mysCmd->setConfiguration('sensorCategory', 'Statut Gateway');
				$mysCmd->setConfiguration('sensorType', 'Connexion');
				$mysCmd->save();
				$mysCmd->event($value);
				$mysCmd = new mySensorsCmd();
				$mysCmd->setEqLogic_id($elogic->getId());
				$mysCmd->setEqType('mySensors');
				$mysCmd->setLogicalId('Inactif');
				$mysCmd->setType('info');
				$mysCmd->setSubType('string');
				$mysCmd->setName( 'Inactif' );
				$mysCmd->setConfiguration('sensorCategory', 'Noeuds Inactifs');
				$mysCmd->setConfiguration('sensorType', 'Inactivité');
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
		//exemple : 0 => array('S_DOOR','Ouverture','door','binary','','','1',),
		$name = self::$_dico['S'][$value][1];
		if ($name == false ) {
			$name = 'UNKNOWN';
		}
		$unite = self::$_dico['S'][$value][4];
		$sType = self::$_dico['S'][$value][1];
		$info = self::$_dico['S'][$value][3];
		$widget = self::$_dico['S'][$value][2];
		$history = self::$_dico['S'][$value][5];
		$visible = self::$_dico['S'][$value][6];
		$cmdId = 'Sensor'.$sensor;
		$elogic = self::byLogicalId($nodeid, 'mySensors');
		if (is_object($elogic)) {
			$cmdlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$cmdId);
			if (is_object($cmdlogic)) {
				if ( $cmdlogic->getConfiguration('sensorCategory', '') != $sType ) {
					$cmdlogic->setConfiguration('sensorCategory', $sType);
					$cmdlogic->save();
				}
			}
			else {
				$mysCmd = new mySensorsCmd();
				$mysCmd->setCache('enable', 0);
				$mysCmd->setEventOnly(0);
				$mysCmd->setConfiguration('sensorCategory', $sType);
				$mysCmd->setConfiguration('sensor', $sensor);
				$mysCmd->setEqLogic_id($elogic->getId());
				$mysCmd->setEqType('mySensors');
				$mysCmd->setLogicalId($cmdId);
				$mysCmd->setType('info');
				$mysCmd->setSubType($info);
				$mysCmd->setName( $name . " " . $sensor );
				$mysCmd->setUnite( $unite );
				$mysCmd->setIsVisible($visible);
				if ($info != 'string') {
					$mysCmd->setIsHistorized($history);
				}
				$mysCmd->setTemplate("mobile",$widget );
				$mysCmd->setTemplate("dashboard",$widget );
				$mysCmd->save();
			}
			if ($name == 'Relais') {
				$relonId = 'Relais'.$sensor.'On';
				$reloffId = 'Relais'.$sensor.'Off';
				$onlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$relonId);
				$offlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$reloffId);
				$cmdlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$cmdId);
				$cmId = $cmdlogic->getId();
				if (!is_object($offlogic)) {
					$mysCmd = new mySensorsCmd();
					$mysCmd->setEventOnly(0);
					$mysCmd->setConfiguration('cmdCommande', '1');
					$mysCmd->setConfiguration('request', '0');
					$mysCmd->setConfiguration('cmdtype', '2');
					$mysCmd->setConfiguration('sensor', $sensor);
					$mysCmd->setEqLogic_id($elogic->getId());
					$mysCmd->setEqType('mySensors');
					$mysCmd->setLogicalId($reloffId);
					$mysCmd->setType('action');
					$mysCmd->setSubType('other');
					$mysCmd->setValue($cmId);
					$mysCmd->setTemplate("dashboard","light" );
					$mysCmd->setTemplate("mobile","light" );
					$mysCmd->setDisplay('parameters',array('displayName' => 1));
					$mysCmd->setName( "Off ". $sensor );
					$mysCmd->save();
				}
				if (!is_object($onlogic)) {
					$mysCmd = new mySensorsCmd();
					$mysCmd->setEventOnly(0);
					$mysCmd->setConfiguration('cmdCommande', '1');
					$mysCmd->setConfiguration('request', '1');
					$mysCmd->setConfiguration('cmdtype', '2');
					$mysCmd->setConfiguration('sensor', $sensor);
					$mysCmd->setEqLogic_id($elogic->getId());
					$mysCmd->setEqType('mySensors');
					$mysCmd->setLogicalId($relonId);
					$mysCmd->setType('action');
					$mysCmd->setSubType('other');
					$mysCmd->setValue($cmId);
					$mysCmd->setTemplate("dashboard","light" );
					$mysCmd->setTemplate("mobile","light" );
					$mysCmd->setDisplay('parameters',array('displayName' => 1));
					$mysCmd->setName( "On " . $sensor );
					$mysCmd->save();
				}

			}
			if ($name == 'Variateur') {
				$dimmerId = 'Dimmer'.$sensor;
				$dimlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$dimmerId);
				$cmdlogic = mySensorsCmd::byEqLogicIdAndLogicalId($elogic->getId(),$cmdId);
				$cmId = $cmdlogic->getId();
				if (!is_object($dimlogic)) {
					$mysCmd = new mySensorsCmd();
					$mysCmd->setEventOnly(0);
					$mysCmd->setConfiguration('cmdCommande', '1');
					$mysCmd->setConfiguration('request', '');
					$mysCmd->setConfiguration('cmdtype', '3');
					$mysCmd->setConfiguration('sensor', $sensor);
					$mysCmd->setEqLogic_id($elogic->getId());
					$mysCmd->setEqType('mySensors');
					$mysCmd->setLogicalId($dimmerId);
					$mysCmd->setType('action');
					$mysCmd->setSubType('slider');
					$mysCmd->setValue($cmId);
					$mysCmd->setTemplate("dashboard","light" );
					$mysCmd->setTemplate("mobile","light" );
					$mysCmd->setName( "Set " . $sensor );
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

