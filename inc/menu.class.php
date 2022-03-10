<?php
/*
 -------------------------------------------------------------------------
 Statecheck plugin for GLPI
 Copyright (C) 2009-2018 by Eric Feron.
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Statecheck.

 Statecheck is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 at your option any later version.

 Statecheck is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Statecheck. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
 
class PluginStatecheckMenu extends CommonGLPI {
   static $rightname = 'plugin_statecheck';

   static function getMenuName() {
      return _n('Statecheck Rule', 'Statecheck Rules', 2, 'statecheck');
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu                                           = [];
      $menu['title']                                  = self::getMenuName();
      $menu['page']                                   = "/".Plugin::getWebDir('statecheck', false)."/front/rule.php";
      $menu['links']['search']                        = PluginStatecheckRule::getSearchURL(false);
      if (PluginStatecheckRule::canCreate()) {
         $menu['links']['add']                        = PluginStatecheckRule::getFormURL(false);
      }
      $menu['icon'] = self::getIcon();

      return $menu;
   }

   static function getIcon() {
      return "fas fa-check-square";
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['admin']['types']['PluginStatecheckMenu'])) {
         unset($_SESSION['glpimenu']['admin']['types']['PluginStatecheckMenu']); 
      }
      if (isset($_SESSION['glpimenu']['admin']['content']['pluginstatecheckmenu'])) {
         unset($_SESSION['glpimenu']['admin']['content']['pluginstatecheckmenu']); 
      }
   }
}
