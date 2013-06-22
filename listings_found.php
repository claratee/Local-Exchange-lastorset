<?php

include_once("includes/inc.global.php");
$p->site_section = LISTINGS;

if ($_REQUEST["type"]==Offer)
	$p->page_title = _("Offered listings");
else
	$p->page_title = _("Wanted listings");

include_once("classes/class.listing.php");

if($_REQUEST["category"] == "0")
	$category = "%";
else
	$category = $_REQUEST["category"];
	
if($_REQUEST["timeframe"] == "0")
	$since = new cDateTime(LONG_LONG_AGO);
else
	$since = new cDateTime("-". $_REQUEST["timeframe"] ." days");

if($cUser->IsLoggedOn() && $_REQUEST["distance"] > 0) {
	$distance = $_REQUEST["distance"];
	$origin = $cUser->person[0]->coordinates;
}
else {
	$distance = null;
	$origin = null;
}

if ($cUser->IsLoggedOn())
	$show_ids = true;
else
	$show_ids = false;

// instantiate new cOffer objects and load them
$listings = new cListingGroup($_GET["type"]);
			
$listings->LoadListingGroup(null, $category, null, $since->MySQLTime(), true, $origin, $distance, false);

$lID = 0;

if ($listings->listing && KEYWORD_SEARCH_DIR==true && strlen($_GET["keyword"])>0) { // Keyword specified
	
		foreach($listings->listing as $l) { // Check ->title and ->description etc against Keyword
			
			$mem = $l->member;
			$pers = $l->member->person[0];
			
			$match = false;
	
			if (strpos(strtolower($l->title), strtolower($_GET["keyword"]))>-1) { // Offer title
				
				$match = true;
			}
			
			if (strpos(strtolower($l->description), strtolower($_GET["keyword"]))>-1) { // Offer description
				
				$match = true;
			}
			
			if ($cUser->IsLoggedOn()) { // Search is only performed on these params if the user is logged in
				
				if (strpos(strtolower($pers->first_name), strtolower($_GET["keyword"]))>-1) { // Member First Name
					
					$match = true;
				}
				
				if (strpos(strtolower($pers->last_name), strtolower($_GET["keyword"]))>-1) { // Member Last Name
					
					$match = true;
				}
				
				if (strpos(strtolower($mem->member_id), strtolower($_GET["keyword"]))>-1) { // Member ID
					
					$match = true;
				}
			
				if (strpos(strtolower($pers->address_post_code), strtolower($_GET["keyword"]))>-1) { // Postcode
					
					$match = true;
				}
			}
			
			if ($match!=true) {
				
				unset($listings->listing[$lID]);
			}
			
			$lID += 1;
	}
}

if ($cUser->IsLoggedOn())
	$output = replace_tags(
		_("Your own listings are omitted below. They can be seen in <a>your profile</a>."),
		array('a' => "a href=member_summary.php?member_id=". urlencode($cUser->member_id) ."#offered"));

$output .= $listings->DisplayListingGroup($show_ids);

$p->DisplayPage($output);

include("includes/inc.events.php");

?>
