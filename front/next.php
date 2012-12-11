<?php

/* ---------------------------------------------------------------------------*
 *                                                                            *
 *                     This file is part of Beaneditor                        * 
 *                                                                            *
 *                                   _/                                       *
 *                                  /o|                                       *
 *                                 |o||                                       *
 *                                 |o||                                       *
 *                                  v\|                                       *
 *                                                                            *
 *                                                                            *
 *  Beaneditor is free software:  you can redistribute it and / or modify it  *
 *  under the terms of the GNU Affero General Public License as published by  *
 *  the Free Software Foundation, either version three of the License or (at  *
 *  your option) any later version.                                           *
 *                                                                            *
 *  Beaneditor is distributed in hope that it will be useful but WITHOUT ANY  *
 *  WARRANTY; without even the implied warranty of MERCHANTABILTY or FITNESS  *
 *  FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for  *
 *  more details.                                                             *
 *                                                                            *
 *  You should have received a copy of the GNU Affero General Public License  *
 *  along with Beaneditor. If not, see <http://www.gnu.org/licenses/>.        *
 *                                                                            * 
 *----------------------------------------------------------------------------*/   


include "../settings.php";
include "../inc/inc.php";

$last = (int)$_GET['last'];
render_bean(false, false, $last, true);

?>