<?php
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

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'mySensors');
$eqLogics = eqLogic::byType('mySensors');
sendVarToJS('mySensorDico', mySensors::$_dico);

?>

<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un équipement}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
    
    <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
        <legend>{{Mes mySensors}}
        </legend>
        <?php
        if (count($eqLogics) == 0) {
            echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Aucun mySensors détecté, démarrer un node pour ajout}}</span></center>";
        } else {
            ?>
            <div class="eqLogicThumbnailContainer">
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
                    echo "<center>";
                    echo '<img src="plugins/mySensors/doc/images/mySensors_icon.png" height="105" width="95" />';
                    echo "</center>";
                    echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
                    echo '</div>';
                }
                ?>
            </div>
        <?php } ?>
    </div>    
    
    
    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <div class="row">
            <div class="col-sm-6">
                <form class="form-horizontal">
            <fieldset>
                <legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i>  {{Général}}
                <i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i>
                </legend>
                <div class="form-group">
                    <label class="col-md-2 control-label">{{Nom du Node}}</label>
                    <div class="col-md-3">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                        <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement mySensors}}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label" >{{Objet parent}}</label>
                    <div class="col-md-3">
                        <select class="form-control eqLogicAttr" data-l1key="object_id">
                            <option value="">{{Aucun}}</option>
                            <?php
                            foreach (object::all() as $object) {
                                echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">{{Catégorie}}</label>
                    <div class="col-md-8">
                        <?php
                        foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                            echo '<label class="checkbox-inline">';
                            echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                            echo '</label>';
                        }
                        ?>

                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">{{Activer}}</label>
                    <div class="col-md-1">
                        <input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>
                    </div>
                    <label class="col-md-2 control-label" >{{Visible}}</label>
                    <div class="col-md-1">
                        <input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>
                    </div>
                </div>

   
                            <div class="form-group expertModeVisible">
                                <label class="col-md-2 control-label">{{Délai max entre 2 messages}}</label>
                                <div class="col-md-8">
                                    <input class="eqLogicAttr form-control" data-l1key="timeout" placeholder="Délai maximum autorisé entre 2 messages (en mn)"/>
                                </div>
                            </div>
                            <div class="form-group">
                    <label class="col-sm-2 control-label">{{Commentaire}}</label>
                    <div class="col-md-8">
                        <textarea class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="commentaire" ></textarea>
                    </div>
                </div>  
              
            </fieldset> 
            	
        </form>
        </div>      
        
                <div id="infoNode" class="col-sm-6">
                <form class="form-horizontal">
                    <fieldset>
                        <legend>{{Informations}}</legend>

                        <div class="form-group">
                    		<label class="col-md-2 control-label">{{ID du Node}}</label>
                    		<div class="col-md-3">
                    		 <span class="mySensorsInfo tooltips label label-default" id="nodeId" data-l1key="nodeId"></span>
                    		</div>
                    		
                    		<label class="col-md-2 control-label">{{Version mySensors}}</label>
                    		<div class="col-md-3">
                        	<span class="mySensorsInfo tooltips label label-default" data-l1key="libVersion"></span>
                    		</div>                    		
                		
                	</div>                    		
                        
                        <div id="infoSketch" class="form-group">
                    		<label class="col-md-2 control-label">{{Nom du Sketch}}</label>
                		 <div class="col-md-3">
                		  <span class="mySensorsInfo tooltips label label-default" data-l1key="sketchNom"></span>
                    		</div>
                    	
                    	        <label class="col-md-2 control-label">{{Version du Sketch}}</label>
                    		<div class="col-md-3">
                        	<span class="mySensorsInfo tooltips label label-default" data-l1key="sketchVersion"></span>
                    		</div>
                    		
                	</div>
                	
                	<div class="form-group">
                    		<label class="col-md-2 control-label">{{Dernière Activité}}</label>
                    		<div class="col-md-3">
                        	<span class="mySensorsInfo tooltips label label-default" data-l1key="lastActivity"></span>
                    		</div>    
                    		
                    		<label class="col-md-2 control-label">{{Batterie}}</label>
                    		<div class="col-md-3">
                    		 <span class="mySensorsInfo tooltips label label-default" data-l1key="perBatterie"></span>
                    		</div>
                	</div>  
	 
                	<div class="form-group">
                    		<label class="col-md-2 control-label">{{Documentation}}</label>
                    		<div class="col-md-3">
                        	<a href="http://doc.jeedom.fr/fr_FR/doc_mySensors_modules.html" class="btn btn-default"><i class="fa fa-book"></i> Documentation</a>
                    		</div>    
                    		
                    		<label class="col-md-2 control-label">{{Redémarrer le Node}}</label>
                    		<div class="col-md-3">
				<a class="btn btn-default" id="bt_restartEq"><i class="fa fa-power-off"></i> Redémarrer</a>
				</div>
                	</div>                 

                	<legend>{{Paramètres du Sketch}}</legend>
                	
                	

                    </fieldset> 
                </form>
            </div>
        </div>

	<legend>{{mySensors}}</legend>

        <a class="btn btn-default btn-sm" id="bt_addmySensorsInfo"><i class="fa fa-plus-circle"></i> {{Ajouter une info}}</a>
        <a class="btn btn-default btn-sm" id="bt_addmySensorsAction"><i class="fa fa-plus-circle"></i> {{Ajouter une commande}}</a><br/><br/>
        		<script>
				$('#bt_restartEq').on('click', function () {
					//nodeId = document.getElementById('nodeId');
					$.ajax({// fonction permettant de faire de l'ajax
						type: "POST", // methode de transmission des données au fichier php
						url: "plugins/mySensors/core/ajax/mySensors.ajax.php", // url du fichier php
						data: {
							action: "restartEq",
							node: nodeId,
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
						$('#div_alert').showAlert({message: 'Le node a été relancé', level: 'success'});
						}
					});
				});
			</script>        
        <table id="table_cmd" class="table table-bordered table-condensed">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 150px;">{{Nom}}</th>
                    <th style="width: 110px;">{{Sous-Type}}</th>
                    <th>{{N° Sensor}}</th>
                    <th style="width: 100px;">{{Unité}}</th>
                    <th style="width: 200px;">{{Paramètres}}</th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        <form class="form-horizontal">
            <fieldset>
                <div class="form-actions">
                    <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
                    <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
                </div>
            </fieldset>
        </form>

    </div>
</div>

<?php include_file('desktop', 'mySensors', 'js', 'mySensors'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
