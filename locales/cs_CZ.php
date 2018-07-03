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


$title = "StateCheck Rules";

$LANG['plugin_statecheck'][0] = "Název";
$LANG['plugin_statecheck'][1] = "Tabulka";
$LANG['plugin_statecheck'][2] = "Cílový stav („----“ = vše)";
$LANG['plugin_statecheck'][3] = "Popis";
$LANG['plugin_statecheck'][4] = "".$title."";
$LANG['plugin_statecheck'][5] = "Aktivní";
$LANG['plugin_statecheck'][6] = "Počáteční datum stavu";
$LANG['plugin_statecheck'][7] = "Zpráva při úspěchu";
$LANG['plugin_statecheck'][8] = "Zpráva při nezdaru";
$LANG['plugin_statecheck'][9] = "Podlední uložená hodnota"
$LANG['plugin_statecheck'][10] = "Zkontrolovat hodnotu";
$LANG['plugin_statecheck'][11] = "Zkontrolovat typ";
$LANG['plugin_statecheck'][12] = "je";
$LANG['plugin_statecheck'][13] = "není";
$LANG['plugin_statecheck'][14] = "je prádzné";
$LANG['plugin_statecheck'][15] = "není prázdné";
$LANG['plugin_statecheck'][16] = "je regulární výraz";
$LANG['plugin_statecheck'][17] = "Metoda načítání";
$LANG['plugin_statecheck'][18] = "E-mail na skupinu";
$LANG['plugin_statecheck'][19] = "E-mail na uživatele";
$LANG['plugin_statecheck'][20] = "Priorita";
$LANG['plugin_statecheck'][21] = "Přidělený „.$title.“";
$LANG['plugin_statecheck'][22] = "Typ";
$LANG['plugin_statecheck'][23] = "Indikátor";
$LANG['plugin_statecheck'][24] = "Přihlášený uživatel";
$LANG['plugin_statecheck'][25] = "Skupina přihlášeného uživatele";
$LANG['plugin_statecheck'][29] = "Pravidlo";
$LANG['plugin_statecheck'][30] = "Kolonka";
$LANG['plugin_statecheck'][31] = "není v souladu s pravidlem";
$LANG['plugin_statecheck'][32] = "Záznam nevložen";
$LANG['plugin_statecheck'][33] = "Záznam neaktualizován";
$LANG['plugin_statecheck'][34] = "!! Zvýrazněné kolonky jsou řízené !!";
$LANG['plugin_statecheck'][40] = "je uživatel";
$LANG['plugin_statecheck'][41] = "je členem skupiny";

$LANG['plugin_statecheck']['mailing'][0] = "S vytvořením, úpravou, smazáním projektu";
$LANG['plugin_statecheck']['mailing'][1] = "S vytvořením, úpravou, smazáním úkolu";
$LANG['plugin_statecheck']['mailing'][2] = "úspěšné";
$LANG['plugin_statecheck']['mailing'][3] = "nezdařilo se";
$LANG['plugin_statecheck']['mailing'][4] = "Kontrola stavu úspěšná pro ";
$LANG['plugin_statecheck']['mailing'][5] = "Kontrola stavu nezdařená pro ";
$LANG['plugin_statecheck']['mailing'][6] = "Projekt byl smazán";
$LANG['plugin_statecheck']['mailing'][7] = "Úkol byl přidán";
$LANG['plugin_statecheck']['mailing'][8] = "Úkol byl upraven nebo otevřen";
$LANG['plugin_statecheck']['mailing'][9] = "Úkol byl smazán";
$LANG['plugin_statecheck']['mailing'][10] = "Úkol je neaktuální";
$LANG['plugin_statecheck']['mailing'][11] = "Skupina zodpovědná za ";
$LANG['plugin_statecheck']['mailing'][12] = "Uživatel zodpovědný za ";
$LANG['plugin_statecheck']['mailing'][13] = "Skupina zodpovědná za položku";
$LANG['plugin_statecheck']['mailing'][14] = "Úpravy";
$LANG['plugin_statecheck']['mailing'][15] = "Zastaralé úkoly";
$LANG['plugin_statecheck']['mailing'][16] = "Přiřazené úkoly";

$LANG['plugin_statecheck']['profile'][0] = "Správa oprávnění";
$LANG['plugin_statecheck']['profile'][1] = "$title";

$LANG['plugin_statecheck']['setup'][1] = "Flow skupina";
$LANG['plugin_statecheck']['setup'][11] = "Server";
$LANG['plugin_statecheck']['setup'][12] = "Jazyk";
$LANG['plugin_statecheck']['setup'][14] = "Dodavatel";
$LANG['plugin_statecheck']['setup'][15] = "Přiřazené položky";
$LANG['plugin_statecheck']['setup'][23] = "Přiřadit";
$LANG['plugin_statecheck']['setup'][24] = "Zrušit přiřazení";
$LANG['plugin_statecheck']['setup'][25] = "Přiřadit k dataflow";
$LANG['plugin_statecheck']['setup'][28] = "Editor";
$LANG['plugin_statecheck']['setup'][29] = "Duplikát";
?>
