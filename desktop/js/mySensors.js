
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


$("#bt_addmySensorsInfo").on('click', function(event) {
    var _cmd = {type: 'info'};
    addCmdToTable(_cmd);
});

$("#bt_addmySensorsAction").on('click', function(event) {
    var _cmd = {type: 'action'};
    addCmdToTable(_cmd);
});


/*
$('#table_cmd tbody').delegate('tr .remove', 'click', function(event) {
    $(this).closest('tr').remove();
});*/

$("#table_cmd").delegate(".listEquipementInfo", 'click', function() {
    var el = $(this);
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function(result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=calcul]');
        calcul.atCaret('insert', result.human);
    });
});

$("#table_cmd").delegate(".listEquipementAction", 'click', function() {
    var el = $(this);
    jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function(result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.attr('data-input') + ']');
        calcul.value(result.human);
    });
});



$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});


function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }

    if (init(_cmd.type) == 'info') {
        var disabled = (init(_cmd.configuration.virtualAction) == '1') ? 'disabled' : '';
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td>';
			tr += '<span class="cmdAttr" data-l1key="id"></span>';
        tr += '</td>';
        tr += '<td>';
			tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom du capteur}}"></td>';
        tr += '<td>';
			tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style="margin-bottom : 5px;" />';
			tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
        tr += '</td>';
        tr += '<td>';
			tr += '<textarea class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="sensor" style="height : 33px;" ' + disabled + ' placeholder="{{Capteur}}"></textarea>';
        tr += '</td>';
        tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="unite" style="width : 90px;" placeholder="{{Unite}}"></td>';
        tr += '<td>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isHistorized"/> {{Historiser}}<br/></span>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/> {{Afficher}}<br/></span>';
		tr += '<span class="expertModeVisible"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary" /> {{Inverser}}<br/></span>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="eventOnly"' + disabled + ' /> {{Evénement seulement}}<br/></span>';
        tr += '<input style="width : 81%;margin-bottom : 2px;" class="tooltips cmdAttr form-control input-sm" data-l1key="cache" data-l2key="lifetime" placeholder="{{Lifetime cache}}" title="{{Lifetime cache}}">';
        tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width : 40%;display : inline-block;"> ';
        tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width : 40%;display : inline-block;">';
        tr += '</td>';
        tr += '<td>';
        if (is_numeric(_cmd.id)) {
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
        }
        tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
        tr += '</tr>';
        $('#table_cmd tbody').append(tr);
        $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
        /*if (isset(_cmd.type)) {
            $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
        }
        jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));*/
    }

    if (init(_cmd.type) == 'action') {
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td>';
        tr += '<span class="cmdAttr" data-l1key="id"></span>';
        tr += '</td>';
        tr += '<td>';
        tr += '<div class="row">';
        tr += '<div class="col-lg-6">';
        tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fa fa-flag"></i> Icone</a>';
        tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
        tr += '</div>';
        tr += '<div class="col-lg-6">';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="name">';
        tr += '</div>';
        tr += '</div>';
        tr += '<select class="cmdAttr form-control tooltips input-sm" data-l1key="value" style="display : none;margin-top : 5px;" title="{{La valeur de la commande vaut par défaut la commande}}">';
	tr += '<option value="">Aucune</option>';
	tr += '</select>';
        tr += '</td>';
        tr += '<td>';
        tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="action" disabled style="margin-bottom : 5px;" />';
		tr += '<span>{{Message}}:<select class="cmdAttr" data-l1key="configuration" data-l2key="cmdCommande">';
			$.each(mySensorDico['C'],function(item, index){ 
				tr += '<option value="' + index + '">' + item + '</option>';
			})
		tr +='</select></span>';
        tr += '<span class="subType" subType="' + init(_cmd.subType) + '" style=""></span>';
        //tr += '<input class="cmdAttr" data-l1key="configuration" data-l2key="virtualAction" value="1" style="display:none;" >';
        tr += '</td>';
         tr += '<td>';
			tr += '<textarea class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="sensor" style="height : 33px;" ' + disabled + ' placeholder="{{N° Actionneur}}"></textarea><br/>';
        
			tr += '<textarea class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="request" style="height : 33px;" ' + disabled + ' placeholder="{{Valeur}}"></textarea>';
        tr += '</td>';
        tr += '<td></td>';
        tr += '<td>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/> {{Afficher}}<br/></span>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="displayName" checked/> {{Afficher le Nom}}<br/></span>';
        tr += '<span>{{Type de donnée}}:<select class="cmdAttr" data-l1key="configuration" data-l2key="cmdtype">';
			$.each(mySensorDico['A'],function(item, index){ 
				tr += '<option value="' + index + '">' + item + '</option>';
			})
		tr +='</select></span>';
        tr += '</td>';
        tr += '<td>';
        if (is_numeric(_cmd.id)) {
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
        }
        tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
        tr += '</tr>';

        $('#table_cmd tbody').append(tr);
        //$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
        var tr = $('#table_cmd tbody tr:last');
 	jeedom.eqLogic.builSelectCmd({
	id: $(".li_eqLogic.active").attr('data-eqLogic_id'),
	filter: {type: 'info'},
	error: function (error) {
		$('#div_alert').showAlert({message: error.message, level: 'danger'});
	},
	success: function (result) {
		tr.find('.cmdAttr[data-l1key=value]').append(result);
		tr.setValues(_cmd, '.cmdAttr');
		jeedom.cmd.changeType(tr, init(_cmd.subType));
	}
	});
	
    }
}
