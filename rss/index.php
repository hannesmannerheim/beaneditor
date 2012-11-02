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

header('Content-Type: application/rss+xml; charset=UTF-8');

include "../settings.php";
include "../inc/inc.php";

print '<?xml version="1.0" encoding="UTF-8" ?>'."\n";

?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
		<atom:link href="<?php print "http://".$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]; ?>" rel="self" type="application/rss+xml" />		
        <title><?php print $name; ?></title>
        <description>RSS feed for <?php print $name; ?></description>
        <link><?php print "http://".$_SERVER['SERVER_NAME'].substr($_SERVER["REQUEST_URI"],0,(strpos($_SERVER["REQUEST_URI"],'rss'))) ?></link>
        <lastBuildDate><?php print date('r', last_change_timestamp()) ?></lastBuildDate>
        <ttl>1800</ttl>
        <?php 
		
		
		// we haz RSS bean
		render_bean(false, true);
		
		
		?>
</channel>
</rss>