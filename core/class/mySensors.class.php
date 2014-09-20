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
				'C_PRESENTATION'=> 0,
				'C_SET'=> 1,
				'C_REQ'=> 2,
				'C_INTERNAL'=> 3,
				'C_STREAM'=> 4,
				),
			'V' => array( 
				'V_TEMP'=> 0,
				'V_HUM'=> 1,
				'V_LIGHT'=> 2,
				'V_DIMMER'=> 3,
				'V_PRESSURE'=> 4,
				'V_FORECAST'=> 5,
				'V_RAIN'=> 6,
				'V_RAINRATE'=> 7,
				'V_WIND'=> 8,
				'V_GUST'=> 9,
				'V_DIRECTION'=> 10,
				'V_UV'=> 11,
				'V_WEIGHT'=> 12,
				'V_DISTANCE'=> 13,
				'V_IMPEDANCE'=> 14,
				'V_ARMED'=> 15,
				'V_TRIPPED'=> 16,
				'V_WATT'=> 17,
				'V_KWH'=> 18,
				'V_SCENE_ON'=> 19,
				'V_SCENE_OFF'=> 20,
				'V_HEATER'=> 21,
				'V_HEATER_SW'=> 22,
				'V_LIGHT_LEVEL'=> 23,
				'V_VAR1'=> 24,
				'V_VAR2'=> 25,
				'V_VAR3'=> 26,
				'V_VAR4'=> 27,
				'V_VAR5'=> 28,
				'V_UP'=> 29,
				'V_DOWN'=> 30,
				'V_STOP'=> 31,
				'V_IR_SEND'=> 32,
				'V_IR_RECEIVE'=> 33,
				'V_FLOW'=> 34,
				'V_VOLUME'=> 35,
				'V_LOCK_STATUS'=> 36,
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
			'S' => array( 
				'S_DOOR'=> 0,
				'S_MOTION'=> 1,
				'S_SMOKE'=> 2,
				'S_LIGHT'=> 3,
				'S_DIMMER'=> 4,
				'S_COVER'=> 5,
				'S_TEMP'=> 6,
				'S_HUM'=> 7,
				'S_BARO'=> 8,
				'S_WIND'=> 9,
				'S_RAIN'=> 10,
				'S_UV'=> 11,
				'S_WEIGHT'=> 12,
				'S_POWER'=> 13,
				'S_HEATER'=> 14,
				'S_DISTANCE'=> 15,
				'S_LIGHT_LEVEL'=> 16,
				'S_ARDUINO_NODE'=> 17,
				'S_ARDUINO_REPEATER_NODE'=> 18,
				'S_LOCK'=> 19,
				'S_IR'=> 20,
				'S_WATER'=> 21,
				'S_AIR_QUALITY'=> 22
				)
			);

    /************************Methode static*************************** */

	public static function cron() {
        
	$usbGateway = config::byKey('usbGateway', 'mySensors', '');
        if ($usbGateway != '' && file_exists( $usbGateway )) {
            if (!self::deamonRunning()) {
                self::runDeamon();
            }
            message::removeAll('mySensors', 'noMySensorsPort');
        } else {
            log::add('mySensors', 'error', __('Le port du mySensors est vide ou n\'éxiste pas', __FILE__), 'noMySensorsPort');
        }
    }
	
	public static function runDeamon() {
        log::add('mySensors', 'info', 'Lancement du démon mySensors');
        
		$usbGateway = config::byKey('usbGateway', 'mySensors', '');
		if ($usbGateway == '' ) {
			throw new Exception(__('Le port : ', __FILE__) . $port . __(' n\'éxiste pas', __FILE__));
		}

		$url = 'http://127.0.0.1/jeedom/core/api/jeeApi.php?api=' . config::byKey('api');
		
        $cmd = 'nice -n 19 node /usr/share/nginx/www/jeedom/plugins/mySensors/node/mysensors.js ' . $url . ' ' . $usbGateway;
		
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

		$msg = $destination . ";" . $sensor . ";" . $command . ";" . $acknowledge . ";" .$type . ";" . $payload;
		$fp = fsockopen("unix:///tmp/mysensor.sock", -1, $errno, $errstr);
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
			
			$name = array_search($value, self::$_dico['S']);
			if ($name == false )
				$name = 'UNKNOWN';

				
			$mysCmd->setName( $name . " " . $sensor );
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

