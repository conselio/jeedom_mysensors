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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>


<form class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label">Mode : </label>
            <div class="col-lg-4">
				<select class="configKey form-control" id="select_mode" data-l1key="externalDeamon">
                    <option value="0">Local</option>
                    <option value="2">Gateway sur un Jeedom Esclave</option>
					<option value="1">Jeedom Esclave (Envoyer les données sur le jeedom Master)</option>
                </select>
            </div>
        </div>
        
        <div id="div_slave" class="form-group">
            <label class="col-lg-4 control-label">Esclave Jeedom :</label>
            <div class="col-lg-4">
                        <select id="select_slave" class="configKey form-control" data-l1key="jeeSlave">
                    <option value="">Aucun</option>
                    <?php
                    foreach (jeeNetwork::byPlugin('mySensors') as $jeeNetwork) {
echo '<option value="' . $jeeNetwork->getId(). '">' . $jeeNetwork->getName() . ' (' . $jeeNetwork->getId(). ')</option>';
}
                    ?>
                </select>
                    </div>
        </div>
        
<div id="div_local" class="form-group">
            <label class="col-lg-4 control-label">Port Gateway :</label>
            <div class="col-lg-4">
                <select id="select_port" class="configKey form-control" data-l1key="usbGateway">
                    <option value="">Aucun</option>
                    <?php
                    foreach (jeedom::getUsbMapping() as $name => $value) {
                        echo '<option value="' . $name . '">' . $name . ' (' . $value . ')</option>';
                    }
					echo '<option value="serie">Modem Série</option><option value="network">Network Gateway</option>';
                    ?>
                </select>
				
				<input id="port_serie" class="configKey form-control" data-l1key="modem_serie_addr" style="margin-top:5px;display:none" placeholder="Renseigner le port série (ex : /dev/ttyS0)"/>
				<input id="network_address" class="configKey form-control" data-l1key="gateway_addr" style="margin-top:5px;display:none" placeholder="Renseigner l'e port série'adresse de la gateway (ex : 192.168.1.1:5003"/>
				            </div>
        </div>
		
		<div id="div_inclusion" class="form-group">		
		<label class="col-lg-4 control-label" >Inclusion</label>
			<div class="col-lg-2">
			<input type="checkbox" data-l1key="include_mode" checked/>
			</div>
		</div>
		
		<div id="div_status" class="form-group">
		<label class="col-lg-4 control-label">Redémarrage</label>
			<div class="col-lg-2">
			<a class="btn btn-warning" id="bt_restartmySensors"><i class='fa fa-stop'></i> Arrêter/Redemarrer le service NodeJS</a>
			</div>
	</div>		
 <?php
 if (!mySensors::deamonRunning()) {
echo '<div id="div_node" class="alert alert-danger">Le service NodeJS ne tourne pas</div>';
} else {
echo '<div id="div_node" class="alert alert-success">Le service NodeJS est en marche</div>';
}
?>				
				<script>
				$( "#select_port" ).change(function() {
					$( "#select_port option:selected" ).each(function() {
						if($( this ).val() == "serie"){
						 $("#port_serie").show();
						 
						 $("#network_address").hide();
						}
						else if($( this ).val() == "network"){
							$("#port_serie").hide();
							
							$("#network_address").show();
							}
						else {
							$("#port_serie").hide();
							
							$("#network_address").hide();
						}
						});
					
				});
				$( "#select_mode" ).change(function() {
					$( "#select_mode option:selected" ).each(function() {
						if($( this ).val() == "0" || $( this ).val() == "1"){
						 $("#div_local").show();
						 $("#div_status").show();
						 $("#div_node").show();
						 $("#div_inclusion").show();
						 $("#div_slave").hide();
						}
						else{
							$("#div_local").hide();
							$("#div_status").hide();
							$("#div_node").hide();
							$("#div_inclusion").hide();
							$("#div_slave").show();
							}
						});
				});
				$('#bt_restartmySensors').on('click', function () {
					$.ajax({// fonction permettant de faire de l'ajax
						type: "POST", // methode de transmission des données au fichier php
						url: "plugins/mySensors/core/ajax/mySensors.ajax.php", // url du fichier php
						data: {
							action: "restartNode",
						},
						dataType: 'json',
						error: function (request, status, error) {
							handleAjaxError(request, status, error);
						},
						success: function (data) { // si l'appel a bien fonctionné
							if (data.state != 'ok') {
								$('#div_alert').showAlert({message: data.result, level: 'danger'});
								return;
							}
						$('#div_alert').showAlert({message: 'Le nodejs a été correctement arrêté : il se relancera automatiquement dans 1 minute', level: 'success'});
						$('#ul_plugin .li_plugin[data-plugin_id=mySensors]').click();
						}
					});
				});
			</script>

    </fieldset>
</form>
