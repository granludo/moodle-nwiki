<?php // $Id: version.php,v 1.30 2008/08/04 08:37:04 pigui Exp $

// Moodle mod-DFWiki Is an alternative wiki module for moodle versions 1.5.x
// Nwiki has been developed by David Castro, Ferran Recio, Marc Alier, Jordi Piguillem, 
// at the Universitat Politecnica de Catalunya http://www.upc.edu  
// contact info marc.alier@upc.edu
//
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
* assignment_base is the base class for assignment types
*
* This class provides all the functionality for an assignment
*
* @package mod-nwiki 
* @copyrigth 2009 David Castro, Ferran Recio, Marc Alier, Jordi Piguillem marc.alier@upc.edu 
* @copyrigth 2009 Universitat Politecnica de Catalunya http://www.upc.edu
*
* @autor Marc Alier
* @autor Jordi Piguillem
* @autor David Castro
* @autor Ferran Recio
*
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
$module->version  = 2010060000;
$module->requires = 2007020200;  // Requires this Moodle version. 1.8 or newer
$module->cron     = 0; // How often should cron check this module (seconds)?

?>
