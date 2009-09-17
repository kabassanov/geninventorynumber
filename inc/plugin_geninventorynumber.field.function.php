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

function plugin_geninventorynumber_getFieldInfos($field) {
	global $DB;
   $query = "SELECT fields.* FROM `glpi_plugin_geninventorynumber_fields` as fields,
               `glpi_plugin_geninventorynumber_config` as config
                  WHERE config.field='$field' AND config.ID=fields.config_id
                     ORDER BY fields.device_type";
   $result = $DB->query($query);
   
   $fields = array();
   while ($datas = $DB->fetch_array($result)) {
   	$fields[$datas['device_type']] = $datas;
   }        
   return $fields;
}

function plugin_geninventorynumber_saveField($fields) {
	$field = new PluginGenInventoryNumberFieldDetail;
    $field->update($fields);
}
?>