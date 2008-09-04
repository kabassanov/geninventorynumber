<?php


/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2005 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of GLPI.

   GLPI is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

function plugin_item_add_generateInventoryNumber($parm) {
	global $INVENTORY_TYPES,$DB;
	if (isset($parm["type"]) && in_array($parm["type"], $INVENTORY_TYPES)) {
		$config = new plugin_GenerateInventoryNumberConfig;
		$config->getFromDB(1);
		if ($config->fields["active"]) {
			$template = $config->fields[plugin_generateInventoryNumber_getTemplateFieldByType($parm["type"])];
			
			$commonitem = new CommonItem;
			$commonitem->setType($parm["type"],true);
			$fields = $commonitem->obj->fields;
			
			//Cannot use update() because it'll launch pre_item_update and clean the inventory number...
			$sql ="UPDATE ".$commonitem->obj->table." SET otherserial='".plugin_generateInventoryNumber_autoName($template, $parm["type"], -1)."' WHERE ID=".$parm["ID"];
			$DB->query($sql);
			 
			plugin_generateInventoryNumber_incrementNumber(-1);
		}
	}

	return $parm;
}

function plugin_pre_item_update_generateInventoryNumber($parm) {
	global $INVENTORY_TYPES;

	if (isset($parm["_item_type_"]) && in_array($parm["_item_type_"], $INVENTORY_TYPES)) {
		$config = new plugin_GenerateInventoryNumberConfig;
		$config->getFromDB(1);
		if ($config->fields["active"])
		{
			if (isset ($parm["otherserial"]))
				unset ($parm["otherserial"]);
		}
	}

	return $parm;
}

function plugin_generateInventoryNumber_getTemplateFieldByType($type) {
	switch ($type) {
		case COMPUTER_TYPE :
			return "template_computer";
		case MONITOR_TYPE :
			return "template_monitor";
		case PRINTER_TYPE :
			return "template_printer";
		case PERIPHERAL_TYPE :
			return "template_peripheral";
		case NETWORKING_TYPE :
			return "template_networking";
		case SOFTWARE_TYPE :
			return "template_software";
		case PHONE_TYPE :
			return "template_software";
	}
}

function plugin_generateInventoryNumber_autoName($objectName, $type,$FK_entities=-1){
	global $DB;

	$len = strlen($objectName);
	if($len > 8 && substr($objectName,0,4) === '&lt;' && substr($objectName,$len - 4,4) === '&gt;') {
		$autoNum = substr($objectName, 4, $len - 8);
		$mask = '';
		if(preg_match( "/\\#{1,10}/", $autoNum, $mask)){
			$global = strpos($autoNum, '\\g') !== false && $type != INFOCOM_TYPE ? 1 : 0;
			$autoNum = str_replace(array('\\y','\\Y','\\m','\\d','_','%','\\g'), array(date('y'),date('Y'),date('m'),date('d'),'\\_','\\%',''), $autoNum);
			$mask = $mask[0];
			$pos = strpos($autoNum, $mask) + 1;
			$len = strlen($mask);
			$like = str_replace('#', '_', $autoNum);

			$sql = "SELECT next_number FROM glpi_plugin_generateinventorynumber_config WHERE FK_entities=$FK_entities";
			$result = $DB->query($sql);
			
			$objectName = str_replace(array($mask,'\\_','\\%'), array(str_pad($DB->result($result,0,"next_number"), $len, '0', STR_PAD_LEFT),'_','%'), $autoNum);
		}
	}
	return $objectName;
}

function plugin_generateInventoryNumber_incrementNumber($FK_entities=-1)
{
	global $DB;

	$sql = "UPDATE glpi_plugin_generateinventorynumber_config SET next_number=next_number+1 WHERE FK_entities=$FK_entities";
	$DB->query($sql);
}
?>