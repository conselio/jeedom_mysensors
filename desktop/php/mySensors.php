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
sendVarToJS('mySensorDico', mySensors::$_dico);

?>

<div class="row row-overflow">
    <div class="col-md-2">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un équipement}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
                foreach (eqLogic::byType('mySensors') as $eqLogic) {
                    echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName() . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <div class="row">
            <div class="col-sm-6">
                <form class="form-horizontal">
            <fieldset>
                <legend>{{Général}}</legend>
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

   
		<div id="followNode" class="form-group">
                    <label class="col-md-2 control-label" >{{Suivi du Node}}</label>
                    <div class="col-md-3">
                        </span><input type="checkbox" class="eqLogicAttr" data-l1key="configuration"  data-l2key="followActivity"  checked/>
                    </div>
                    <label class="col-md-2 control-label" >{{Durée d'Inactivité}}</label>
                    <div class="col-md-3">
                        <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="AlertLimit" placeholder="AlertLimit"/>
                    </div>
                </div>
            </fieldset> 
        </form>
        </div>      
        
                <div class="col-sm-6">
                <form class="form-horizontal">
                    <fieldset>
                        <legend>{{Informations}}</legend>

                        <div class="form-group">
                    		<label class="col-md-2 control-label">{{Node ID}}</label>
                    		<div class="col-md-3">
                        	<input id="selectNode" type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="nodeid" placeholder="NODE ID"/ readonly=true>
                    		</div>
                    		
                    <label class="col-md-2 control-label">{{mySensors}}</label>
                    <div class="col-md-3">
                        <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="LibVersion" placeholder="LibVersion"/ readonly=true>
                    </div>                    		
                	</div>                    		
                        <div class="form-group">
                    		<label class="col-md-2 control-label">{{Sketch}}</label>
                		 <div class="col-md-3">
                		  <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="SketchName" placeholder="SketchName"/ readonly=true>
                    		</div>
                    		                    <label class="col-md-2 control-label">{{Version}}</label>
                    <div class="col-md-3">
                        <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="SketchVersion" placeholder="SketchVersion"/ readonly=true>
                    </div>
                    		
                	</div>
                	              	<div class="form-group">
                    <label class="col-md-2 control-label">{{Dernière Activité}}</label>
                    <div class="col-md-3">
                        <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="LastActivity" placeholder="LastActivity"/ readonly=true>
                    </div>
                </div>	 
                	

                    </fieldset> 
                </form>
            </div>
        </div>

	<legend>{{mySensors}}</legend>

        <a class="btn btn-default btn-sm" id="bt_addmySensorsInfo"><i class="fa fa-plus-circle"></i> {{Ajouter une info}}</a>
        <a class="btn btn-default btn-sm" id="bt_addmySensorsAction"><i class="fa fa-plus-circle"></i> {{Ajouter une commande}}</a><br/><br/>
        		<script>
				$('#bt_restartEq').on('click', function () {
					nodeId = document.getElementById('selectNode');
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
						$('#ul_plugin .li_plugin[data-plugin_id=mySensors]').click();
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

<script>
	$( "#ul_eqLogic" ).change(function() {
		if($( "#selectNode" ).val() == "gateway"){
			$("#selectNode").hide();
			$("#followNode").hide();
		}
		else {
			$("#selectNode").show();
			$("#followNode").show();
		}
	});
</script>

<?php include_file('desktop', 'mySensors', 'js', 'mySensors'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
