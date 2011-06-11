<?php

global $CFG;

require_once($CFG->dirroot.'/mod/wiki/db/dfwikiupgradelib.php');
require_once($CFG->dirroot.'/mod/wiki/db/nwikiupgradelib.php');
require_once($CFG->dirroot.'/backup/lib.php');

function xmldb_wiki_upgrade($oldversion=0) {

    global $CFG, $db;
    
    if ($oldversion < 2007010102) { //Checking special downgraded version for migration
        $message = "Migration process successful.<br>This moodle installation is no longer operational, upgrade to Moodle 2.0 is required.<br>";
        $message .= "For detailed information please visit: <a href=\"http://docs.moodle.org/20/en/Nwiki_to_Moodle_2.0_Migration\">Nwiki to Moodle 2.0 Migration</a>";
        notify ($message, 'notifyproblem', $align='center');
        die;
    }

  
    $result = true;

    // Checks if the current version installed in the system is old wiki (ewiki) or is new wiki (nwiki)
    // We can distinguish ewiki from nwiki checking wiki_synonymous table existence.

	$table = new XMLDBTable('wiki_synonymous');
    if (table_exists($table)) {  //New wiki is installed
       if ($result && $oldversion < 2010060002) {
       		//Wiki table
       		$table = new XMLDBTable('wiki');
       		
       		//Drop fields
            $field = new XMLDBField('introformat');
            $result = $result && drop_field($table, $field);
			
            $field = new XMLDBField('restore');
            $result = $result && drop_field($table, $field);
                        
            $field = new XMLDBField('teacherdiscussion');
            $result = $result && drop_field($table, $field);
            
            $field = new XMLDBField('studentdiscussion');
            $result = $result && drop_field($table, $field);

            $field = new XMLDBField('editanothergroup');
            $result = $result && drop_field($table, $field);

            $field = new XMLDBField('editanotherstudent');
            $result = $result && drop_field($table, $field);

            $field = new XMLDBField('votemode');
            $result = $result && drop_field($table, $field);

            $field = new XMLDBField('listofteachers');
            $result = $result && drop_field($table, $field);

            $field = new XMLDBField('editorrows');
            $result = $result && drop_field($table, $field);

            $field = new XMLDBField('editorcols');
            $result = $result && drop_field($table, $field);

            $field = new XMLDBField('evaluation');
            $result = $result && drop_field($table, $field);

            $field = new XMLDBField('notetype');
            $result = $result && drop_field($table, $field);

            $field = new XMLDBField('wikicourse');
            $result = $result && drop_field($table, $field);
            
            //Rename fields
            $field = new XMLDBField('intro');
            $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL);            
            $result = $result && rename_field($table, $field, 'summary');
            
            $field = new XMLDBField('attach');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL , null, null, null, '1', null);            
            $result = $result && rename_field($table, $field, 'ewikiacceptbinary');
            $result = $result && set_field ('wiki', 'ewikiacceptbinary', '1');
            
            //Add new fields
            $field = new XMLDBField('wtype');
			$field->setAttributes(XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL);            
            $result = $result && add_field($table, $field);
            
            $field = new XMLDBField('htmlmode');
			$field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL , null, null, null, '0', null);            
            $result = $result && add_field($table, $field);            
            
            $field = new XMLDBField('ewikiprinttitle');
			$field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL , null, null, null, '1', null);            
            $result = $result && add_field($table, $field);

            $field = new XMLDBField('disablecamelcase');
			$field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL , null, null, null, '0', null);            
            $result = $result && add_field($table, $field);

            $field = new XMLDBField('setpageflags');
			$field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL , null, null, null, '1', null);            
            $result = $result && add_field($table, $field);

            $field = new XMLDBField('strippages');
			$field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL , null, null, null, '1', null);            
            $result = $result && add_field($table, $field);
            
            $field = new XMLDBField('removepages');
			$field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL , null, null, null, '1', null);            
            $result = $result && add_field($table, $field);

            $field = new XMLDBField('revertchanges');
			$field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL , null, null, null, '1', null);            
            $result = $result && add_field($table, $field);

            $field = new XMLDBField('initialcontent');
			$field->setAttributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);            
            $result = $result && add_field($table, $field);
            
            //Generate htmlmode info
            $wikis = get_records('wiki');
            foreach ($wikis as $wiki) {
                if ($wiki->editor == 'ewiki') {
                    $result = $result && set_field ('wiki', 'htmlmode', '0', 'id', $wiki->id);
                } else {
                	$result = $result && set_field ('wiki', 'htmlmode', '2', 'id', $wiki->id);
                }
            }
            $field = new XMLDBField('editor');            
            $result = $result && drop_field($table, $field);
            
            //Generate wtype info
            $wikis = get_records('wiki');            
            foreach ($wikis as $wiki) {
                if ($wiki->editable == 0) {
                    $result = $result && set_field ('wiki', 'wtype', 'teacher', 'id', $wiki->id);
                } else if ($wiki->editable == 1 && $wiki->studentmode == 0) {
                	$result = $result && set_field ('wiki', 'wtype', 'group', 'id', $wiki->id);
                } else {
                	$result = $result && set_field ('wiki', 'wtype', 'student', 'id', $wiki->id);
                }
            }
            $field = new XMLDBField('editable');            
            $result = $result && drop_field($table, $field);
            
            $field = new XMLDBField('studentmode');            
            $result = $result && drop_field($table, $field);      
            
            //Rename tables: evaluation, evalutation_edition, pages, synonymous, votes
            $table = new XMLDBTable('wiki_evaluation');
            $result = $result && rename_table($table, 'wiki_evaluation_old');
            
            $table = new XMLDBTable('wiki_evaluation_edition');
            $result = $result && rename_table($table, 'wiki_evaluation_edition_old');

            $table = new XMLDBTable('wiki_pages');
            $result = $result && rename_table($table, 'wiki_pages_old');

            $table = new XMLDBTable('wiki_synonymous');
            $result = $result && rename_table($table, 'wiki_synonymous_old');

            $table = new XMLDBTable('wiki_votes');
            $result = $result && rename_table($table, 'wiki_votes_old');

            //Alter locks table
            $table = new XMLDBTable('wiki_locks');
            
            $field = new XMLDBField('groupid');
            $result = $result && drop_field($table, $field);
			
            $field = new XMLDBField('ownerid');
            $result = $result && drop_field($table, $field);
            
            //Create new tables: entries, locks, pages
            //New table entries
            $table = new XMLDBTable('wiki_entries');
            
            // Adding fields to table wiki_entries
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('wikiid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
            $table->addFieldInfo('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
            $table->addFieldInfo('groupid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');            
            $table->addFieldInfo('pagename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

            // Adding keys to table wiki_entries
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('foreign', XMLDB_KEY_FOREIGN, array('wikiid'), 'wiki', array('id'));            

            // Adding indexes to table wiki_entries
            $table->addIndexInfo('course', XMLDB_INDEX_NOTUNIQUE, array('course'));
            $table->addIndexInfo('groupid', XMLDB_INDEX_NOTUNIQUE, array('groupid'));
            $table->addIndexInfo('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
            $table->addIndexInfo('pagename', XMLDB_INDEX_NOTUNIQUE, array('pagename'));

            // Launch create table for wiki_entries
            $result = $result && create_table($table);
            
            // New table pages
            $table = new XMLDBTable('wiki_pages');

            // Adding fields to table wiki_pages
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('pagename', XMLDB_TYPE_CHAR, '160', null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('version', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
            $table->addFieldInfo('flags', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, '0');
            $table->addFieldInfo('content', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null,  null);            
            $table->addFieldInfo('author', XMLDB_TYPE_CHAR, '100', null, null, null, null, null, 'ewiki');
            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
            $table->addFieldInfo('created', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, '0');
            $table->addFieldInfo('lastmodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, '0');
            $table->addFieldInfo('refs', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
            $table->addFieldInfo('meta', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
            $table->addFieldInfo('hits', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, '0');
            $table->addFieldInfo('wiki', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
            
            // Adding keys to table wiki_pages
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('foreign', XMLDB_KEY_FOREIGN, array('wiki'), 'wiki', array('id'));            

            // Adding indexes to table wiki_pages
            $table->addIndexInfo('pagename-version-wiki', XMLDB_INDEX_UNIQUE, array('pagename','version','wiki'));

            // Launch create table for wiki_pages
            $result = $result && create_table($table);
               
            //Generate wiki_entries table content
            $wikis = get_records('wiki');
            
            foreach ($wikis as $wiki) {
            	//Remove non valid fields
            	$cm = get_coursemodule_from_instance('wiki', $wiki->id, $wiki->course);
    			if($cm->groupmode == '0'){
    				delete_records('wiki_pages_old', 'dfwiki', $wiki->id ,'groupid', '1');
    				delete_records('wiki_pages_old', 'dfwiki', $wiki->id ,'groupid', '2');
    			} else {
    				delete_records('wiki_pages_old', 'dfwiki', $wiki->id,'groupid', '0');
    			}
    			
            	if ($wiki->wtype == 'group') {
            	    $sql_select = "SELECT DISTINCT dfwiki, groupid";
            	} else {            			
            	    $sql_select = "SELECT DISTINCT dfwiki, groupid, userid";
            	}

            	$sql_entries = $sql_select. 
	                         " FROM {$CFG->prefix}wiki_pages_old".
	                         " WHERE dfwiki=$wiki->id";
            	
                if ($rs = get_recordset_sql($sql_entries)) {
            	    $newentry = new object();
                	$newpage = new object();
                    while ($entry = rs_fetch_next_record($rs)) {
                        $course = get_field('wiki', 'course', 'id', $entry->dfwiki);
                        if ($wiki->wtype == 'group') {
            		        $sql_where = " WHERE dfwiki=$entry->dfwiki".
                                       " AND groupid=$entry->groupid";
                        } else {            			
            		        $sql_where = " WHERE userid=$entry->userid".
                                       " AND dfwiki=$entry->dfwiki".
            		                   " AND groupid=$entry->groupid";
            	        }
                        $sql_pages = "SELECT *". 
	                                 " FROM {$CFG->prefix}wiki_pages_old".
                                     $sql_where.
                                     " ORDER BY lastmodified ASC";
                        
                        $first = true;
                        //Generate wiki_pages table content
                        if ($rs_pages = get_recordset_sql($sql_pages)) {
                        	while ($page = rs_fetch_next_record($rs_pages)) {
                        	    if ($first) {
                    		        $newentry->wikiid          = $entry->dfwiki;
                                    $newentry->course          = $course;
                                    $newentry->groupid         = $page->groupid;
                                    $newentry->userid          = ($wiki->wtype != 'group')?$page->userid:0;
                                    $newentry->pagename        = $wiki->pagename;
                                    $newentry->timemodified    = $page->lastmodified;                    		
                        		    $first = false;			                    		    
    			                    if (!$identry = insert_record('wiki_entries', $newentry)) {
			                           error('Could not create new entry');
			                        }	
                    	        }
                        	    $newpage->pagename     = addslashes($page->pagename);
                        	    $newpage->version      = $page->version;
                        	    $newpage->flags        = '0';
                        	    $newpage->author       = $page->author;
                        	    $newpage->userid       = $page->userid;
                    	        $newpage->created      = $page->created;
                        	    $newpage->lastmodified = $page->lastmodified;
                        	    $newpage->refs         = str_replace('|', '\r\n', addslashes($page->refs));
                        	    $newpage->meta         = '';
                        	    $newpage->hits         = $page->hits;
                        	    $newpage->wiki         = $identry;

                        	    //Parse content  	                      	    
                    	        switch($page->editor) {
                    	    	    case 'dfwiki':$newpage->content = addslashes(dfwiki_sintax_html_bis(stripslashes($page->content), 'dfwiki', $course, $entry->dfwiki, $identry, $page)); break;
                    	    	    case 'nwiki':$newpage->content = addslashes(parse_nwiki_to_html($course, $entry->dfwiki, $identry, $page)); break;
                    	    	    default:$newpage->content = addslashes(preg_replace("/(\[)\[(.*)\](\])/", '$1$2$3', $page->content));
                    	        }

                    	        if (!insert_record('wiki_pages', $newpage)) {
                                    error('Could not create new page');
                                }
                            }
                            rs_close($rs_pages);
                        }
                    }
                    rs_close($rs);
                }
            }
            // Move uploaded files
            $sql = "SELECT DISTINCT wikiid FROM {$CFG->prefix}wiki_entries";
            $wikiids = get_records_sql($sql);
	        if (!empty($wikiids)) {
		        $module = get_record('modules', 'name', 'wiki');
		        foreach ($wikiids as $wikiid) {
		        	$entry = get_record_sql("SELECT * FROM {$CFG->prefix}wiki_entries WHERE wikiid={$wikiid->wikiid}", true);
                    $coursemodule = get_record('course_modules', 'instance', $entry->wikiid, 'module', $module->id);
			        $result = $result && nwiki_move_uploaded_files($entry->wikiid, $coursemodule->course, $coursemodule->id, $entry->id, $entry->pagename);
		        }
            }
            
            //Drop _old tables
            if ($result) {
                $table = new XMLDBTable('wiki_evaluation_old');
                $result = $result && drop_table($table);
                
                $table = new XMLDBTable('wiki_evaluation_edition_old');
                $result = $result && drop_table($table);
                
                $table = new XMLDBTable('wiki_pages_old');
                $result = $result && drop_table($table);
                
                $table = new XMLDBTable('wiki_synonymous_old');
                $result = $result &&drop_table($table);
                
                $table = new XMLDBTable('wiki_votes_old');
                $result = $result && drop_table($table);
            }
            
            $wiki = get_record('modules', 'name', 'wiki');
            $wiki->version = 2007010100;
            $result = $result && update_record('modules', $wiki);

            if ($result){
                $message = "Downgrade successful.<br>Note that current installation is not operational please upgrade to Moodle 2.0";
                $message .= "For detailed information please visit: <a href=\"http://docs.moodle.org/20/en/Nwiki_to_Moodle_2.0_Migration\">Nwiki to Moodle 2.0 Migration</a>";
                notify ($message, 'notifysuccess', $align='center');
                die;
            }
            else{
               error('Could not downgrade.');
            }

            return $result;
       }
    } else {
       error("NWiki doesn't exist");
    }
    return $result;
}
