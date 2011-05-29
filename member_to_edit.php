<?php
include_once("includes/inc.global.php");
$p->site_section = 0;

$cUser->MustBeLevel(1);
include("includes/inc.forms.php");

$form->addElement("header", null, _("Choose Member to Edit"));
$form->addElement("html", "<TR></TR>");

$ids = new cMemberGroup;
$ids->LoadMemberGroup(null,true);

$form->addElement("select", "member_id", _("Member"), $ids->MakeIDArray());
$form->addElement("static", null, null, null);
$form->addElement('submit', 'btnSubmit', _("Edit"));

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $cUser;
	header("location:http://".HTTP_BASE."/member_edit.php?mode=admin&member_id=".$values["member_id"]);
	exit;	
}

?>
