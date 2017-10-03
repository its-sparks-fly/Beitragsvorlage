<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("newthread_do_newthread_start", "forumform_newthread");
$plugins->add_hook("admin_formcontainer_output_row", "forumform_box");
$plugins->add_hook("admin_forum_management_edit_commit", "forumform_commit");

function forumform_info()
{
	return array(
		"name"		=> "Vorlage für neue Themen",
		"description"	=> "Erlaubt es Administratoren, im Admin CP Vorlagen für neue Themen im bearbeiteten Unterforum anzugeben.",
		"website"	=> "http://storming-gates.de",
		"author"	=> "sparks fly",
		"authorsite"	=> "http://storming-gates.de",
		"version"	=> "1.0",
		"compatibility" => "18*"
	);
}

function forumform_install()
{
	global $db, $cache, $mybb;

	$db->query("ALTER TABLE `".TABLE_PREFIX."forums` ADD `form` VARCHAR(2500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `defaultsortorder`;");
	rebuild_settings();
}

function forumform_activate()
{
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("newthread", "#".preg_quote('{$message}')."#i", '{$message}{$forum[\'form\']}');
}

function forumform_is_installed()
{
	global $db;
	if($db->field_exists("form", "forums"))
	{
		return true;
	}
	return false;
}

function forumform_uninstall()
{
	global $db, $cache;
	if($db->field_exists("form", "forums"))
  {
    $db->drop_column("forums", "form");
  }

	rebuild_settings();
}

function forumform_deactivate()
{
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("newthread", "#".preg_quote('{$forum[\'form\']}')."#i", '', 0);
}

function forumform_box($above)
{
	global $mybb, $lang, $form_container, $forum_data, $form;
	if($above['title'] == $lang->misc_options && $lang->misc_options)
	{
		$above['content'] .= $form_container->output_row("Vorlage", "", $form->generate_text_area('form', $forum_data['form'], array('id' => 'form')), 'form');
	}
	return $above;
}

function forumform_commit()
{
	global $mybb, $cache, $db, $fid;
	$update_array = array(
		"form" => $db->escape_string($mybb->get_input('form'))
	);

	$db->update_query("forums", $update_array, "fid='{$fid}'");

	$cache->update_forums();
}

function forumform_newthread(&$forum)
{
	global $mybb, $db, $forum, $fid;
	$forum['form'] = $db->query("SELECT form FROM ".TABLE_PREFIX."forums
	WHERE fid = '$fid'");
	return $forum;
}

?>
