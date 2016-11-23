<?php
/*
Plugin Name: Periodic Downloadable Issue Manager
Description: Publish your journal, newspaper or any other periodic publication in PDF format.
Version: 0.9
Text Domain: rq-issue-manager
Author: Rimas Kudelis
Author URI: https://rimas.kudelis.lt/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2016 Rimas Kudelis <rq@akl.lt>.
*/

// Abort immediately if this file is called directly.
if (!defined('WPINC')) {
    die;
}

require_once 'classes/IssueManager.php';
require_once 'classes/IssueArticlesMetaBox.php';
require_once 'classes/IssueSpecificsMetaBox.php';

new \RQ\IssueManager;