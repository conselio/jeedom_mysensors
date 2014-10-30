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

	public static $_dico = 
			array(
			'C' => array( 
				'PRESENTATION'=> 0,
				'PARAMETRAGE'=> 1,
				'INTERNE'=> 3,
				'OTA'=> 4,
				),
			'U' => array( // Unité
				'C°'=> 0, //Temperature
				'%'=> 1, //Humidité
				'Relais'=> 2,
				'Variateur'=> 3,
				'hPA'=> 4, //Pression admospherique
				'V_FORECAST'=> 5,
				'mm'=> 6, //Niveau d'eau en milli-metre
				'%'=> 7, //Taux de pluie
				'KMh'=> 8, //Vitesse du vent
				'GUST'=> 9, //Raffale
				'DIRECTION'=> 10, //Direction du vent
				'UV'=> 11,
				'Kg'=> 12,
				'cm'=> 13,
				'V_IMPEDANCE'=> 14,
				'V_ARMED'=> 15,
				'Entrée'=> 16,
				'WATT'=> 17,
				'KWH'=> 18,
				'V_SCENE_ON'=> 19,
				'V_SCENE_OFF'=> 20,
				'Radiateur'=> 21,
				'Radiateur ON/OFF'=> 22,
				'%'=> 23, // Niveau lumiere
				'VAR1'=> 24,
				'VAR2'=> 25,
				'VAR3'=> 26,
				'VAR4'=> 27,
				'VAR5'=> 28,
				'V_UP'=> 29,
				'V_DOWN'=> 30,
				'V_STOP'=> 31,
				'IR_SEND'=> 32,
				'IR_RECEIVE'=> 33,
				'V_FLOW'=> 34,
				'M²'=> 35, // volume
				'V_LOCK_STATUS'=> 36,
				'V_DUST_LEVEL'=> 37,
				'V'=> 38, //Volt (tension)
				'A'=> 39, //Ampere (intensité)
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
				'Fumé'=> 2,
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
		if ($usbGateway == '' ) {
			throw new Exception(__('Le port : ', __FILE__) . $port . __(' n\'éxiste pas', __FILE__));
		}
		
		if (config::byKey('jeeNetwork::mode') == 'slave') { //Je suis l'esclave
			$url  = 'http://' . config::byKey('jeeNetwork::master::ip') . '/core/api/jeeApi.php?api=' . config::byKey('jeeNetwork::master::apikey');
		} else {
			$url = 'http://127.0.0.1/jeedom/core/api/jeeApi.php?api=' . config::byKey('api');
		}
	
	$sensor_path = realpath(dirname(__FILE__) . '/../../node');	
        $cmd = 'nice -n 19 node ' . $sensor_path . '/mysensors.js ' . $url . ' ' . $usbGateway;
		
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
	//	$fp = fsockopen("unix:///tmp/mysensor.sock", -1, $errno, $errstr);
	//	   if (!$fp) {
	//	   echo "ERROR: $errno - $errstr<br />\n";
	//	} else {
	
	//	   fwrite($fp, $msg);
	//	   fclose($fp);
	//	}

	}
	
	public static function saveValue() {
	
		$nodeid = init('id');
		$sensor = init('sensor');
		$value = init('value');
		
		
		//recherche dans tous les eqlogic 
		foreach( self::byType( 'mySensors' ) as $elogic) {
		
			//si le nodeid est le meme
			if ( $elogic->getConfiguration('nodeid') == $nodeid ) {

				foreach( mySensorsCmd::byEqLogicId($elogic->getId()) as $cmd ) {
					//on cherche la commande par son sensor
					if ( $cmd->getType() =='info' && $cmd->getConfiguration( 'sensor' ) == $sensor ) {
						$cmd->setConfiguration('value', $value);
						$cmd->save();
						$cmd->event($value);
						
					}
				}
			}
		}
	
	}
	
	public static function saveBatteryLevel() {

		$nodeid = init('id');
		$value = init('value');
		
		//recherche dans tous les eqlogic 
		foreach( self::byType( 'mySensors' ) as $elogic) {
		
			//si le nodeid est le meme
			if ( $elogic->getConfiguration('nodeid') == $nodeid ) {
				if ( $elogic->getConfiguration('BatteryLevel', '') != $value ) {
					$elogic->setConfiguration('BatteryLevel',$value);
					$elogic->save();
				}
			}
		}
	
	}
	
	public static function saveSketchNameEvent() {
	
		$nodeid = init('id');
		$value = init('value');
		
		//recherche dans tous les eqlogic 
		foreach( self::byType( 'mySensors' ) as $elogic) {
		
			//si le nodeid est le meme
			if ( $elogic->getConfiguration('nodeid') == $nodeid ) {
				if ( $elogic->getConfiguration('SketchName', '') != $value ) {
					$elogic->setConfiguration('SketchName',$value);
					$elogic->save();
				}
			}
		}
	}

	public static function saveSketchVersion() {
	
		$nodeid = init('id');
		$value = init('value');
		
		//recherche dans tous les eqlogic 
		foreach( self::byType( 'mySensors' ) as $elogic) {
		
			//si le nodeid est le meme
			if ( $elogic->getConfiguration('nodeid') == $nodeid ) {
				$elogic->setConfiguration('SketchVersion',$value);
				$elogic->save();
			}
		}
	}
	
	public static function saveSensor() {
	
	
		$nodeid = init('id');
		$value = init('value');
		$sensor = init('sensor');
		
		$first_eqlogic = -1;
		$allreadyexist = false;
		//recherche dans tous les eqlogic 
		foreach( self::byType( 'mySensors' ) as $elogic) {
		
			//si le nodeid est le meme
			if ( $elogic->getConfiguration('nodeid') == $nodeid ) {
				//si on trouve un node déjà existant
				if ( $first_eqlogic == -1 )
					$first_eqlogic = $elogic->getId();
					
				foreach( mySensorsCmd::byEqLogicId($elogic->getId()) as $cmd ) {
					//on cherche la commande par son sensor
					if ( $cmd->getConfiguration( 'sensor' ) == $sensor ) {
						$cmd->setConfiguration('sensorType', $value);
						$cmd->save();
						$allreadyexist = true;
					}
				}
			}
		}
		
		if ( !$allreadyexist ) {
		
			if ( $first_eqlogic == '-1') {
				
				$mys = new mySensors();
				$mys->setEqType_name('mySensors');
				$mys->setConfiguration('nodeid', $nodeid);
				$mys->setName('new Node '.$nodeid);
				$mys->setIsEnable(true);
				$mys->save();
				$first_eqlogic = $mys->getId();
			}
			
			$mysCmd = new mySensorsCmd();
			$mysCmd->setCache('enable', 0);
			$mysCmd->setEventOnly(0);
			$mysCmd->setConfiguration('sensorType', $value);
			$mysCmd->setConfiguration('sensor', $sensor);
			$mysCmd->setEqLogic_id($first_eqlogic);
			$mysCmd->setEqType('mySensors');
			$mysCmd->setType('info');
			$mysCmd->setSubType('numeric');
			
			$name = array_search($value, self::$_dico['N']);
			if ($name == false )
				$name = 'UNKNOWN';

				
			$mysCmd->setName( $name . " " . $sensor );
			$unite = array_search($value, self::$_dico['U']);
			if ($unite == false )
				$unite = 'Unite';
			//log::add('mySensors', 'info', $value);
			//log::add('mySensors', 'info', $unite);
			$mysCmd->setUnite( $unite );
			$mysCmd->save();
		}
		
	
	}

	
    public static function event() {

		$messageType = init('messagetype');
		switch ($messageType) {
		
			case 'saveValue' : self::saveValue(); break;
			case 'saveSketchName' : self::saveSketchNameEvent(); break;
			case 'saveSketchVersion' : self::saveSketchVersion(); break;
			case 'saveSensor' : self::saveSensor(); break;
			case 'saveBatteryLevel' : self::saveBatteryLevel(); break;
		
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

