Beaneditor
==========================================

* Author:    Hannes Mannerheim (<h@nnesmannerhe.im>)
* Last mod.: February, 2016
* Version:   3
* Website:   <http://beaneditor.hannesmannerhe.im>
* GitHub:    <https://github.com/hannesmannerheim/beaneditor>

Beaneditor is free software:  you can redistribute it and / or modify it
under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version three of the License or (at
your option) any later version.

Beaneditor is distributed in hope that it will be useful but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILTY or FITNESS
FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for
more details.

Setup
-----

1. Create the MySQL tables. Beaneditor needs only two tables:
```
	CREATE TABLE 'content' (  
	  'id' int(10) unsigned NOT NULL auto_increment,  
	  'content' longtext collate utf8_unicode_ci NOT NULL,  
	  PRIMARY KEY  ('id')  
	) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;  
	  
	CREATE TABLE 'objects' (  
	  'i' int(10) unsigned NOT NULL auto_increment,  
	  'id' int(10) unsigned NOT NULL,  
	  'time' decimal(12,2) unsigned NOT NULL,  
	  'type' varchar(2) collate utf8_unicode_ci NOT NULL,  
	  'parent' int(10) unsigned NOT NULL,  
	  'content' int(10) unsigned NOT NULL,  
	  'width' decimal(4,2) NOT NULL,  
	  'sort_order' int(10) unsigned NOT NULL,  
	  'published' tinyint(1) NOT NULL,  
	  'deleted' tinyint(1) NOT NULL,  
	  KEY 'i' ('i'),  
	  KEY 'id' ('id'),  
	  KEY 'time' ('time')  
	) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;  
```
2. Create a MySQL user. Note, the user only needs SELECT and INSERT priviliges! 
Beaneditor never use UPDATE or DELETE, so don't allow those actions. 

3. Edit settings.php_template and .htaccess_template accordingly to your db, and
rename the files to settings.php and .htaccess

4. Make sure STRICT_ALL_TABLES is _not_ set in mysql

5. If you want to password protect the administration pages, use .htpasswd and 
put .htaccess files in the folders you want to protect, probably in:
   
	./admin/  
	./operations/
   

TODO
----

1. WYSIWYG-editor

2. Simple removal tool for stuff/images/div:s/p:s in articles

3. Drag-n-drop media upload

4. Much more...