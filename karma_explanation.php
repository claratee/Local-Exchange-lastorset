<?php
/* The explanations on this page are intentionally vague, to allow us to refine the algorithm later. */

include_once("includes/inc.global.php");
$p->site_section = SECTION_INFO;
$p->page_title = _("What are experience points?");

print $p->MakePageHeader();
print $p->MakePageMenu();
print $p->MakePageTitle();

if (GAME_MECHANICS) {
	// Find whose member info to display
	if ($cUser->IsLoggedOn()) {
		if (isset($_GET['member'])) {
			// Specified user
			$member = new cMember();
			$member->LoadMember($_GET['member']);
		}
		else
			// Current user
			$member = $cUser;
	}
	else {
		// Mock user
		$member = new cMember();
		$member->balance
			= $member->spent
			= $member->earned
			= 0;
	}

	$balance = $member->balance;
	// Format the balance display as −0.00 or +0.00. The − character replaces the hyphen since it has a predictable width
	$balance_text = mb_ereg_replace("-", "−", sprintf("%+.2f", $balance));

	if ($member == $cUser) {
		// "You", "your"
		$karma_help = _("These are your experience points. They reflect your activity in the LETS system, and the equilibrium between how much you have done for others and how much others have done for you.");
		// Translation hint: %s is the currency name, which may be "hours".
		$balance_help = sprintf(_("This is your account balance. It shows how many %s you have earned minus what you have spent."), $site_settings->getUnitString());
		if ($balance > 0)
			// Translation hint: "it" refers to the member's account balance.
			$balance_hint = _("Spend some of them to gain experience points!");
		else if ($balance < 0)
			$balance_hint = _("Do something for other members to gain experience points!");
	}
	else {
		// "John Doe's", "the member's", "they"
		// Translation hint: %s is a member's name.
		$karma_help = sprintf(_("These are %s's experience points. They reflect the member's activity in the LETS system, and the equilibrium between how much they have done for others and how much others have done for them."), "<a href=//". HTTP_BASE ."/member_summary.php?member_id={$member->member_id}>{$member->PrimaryName()}</a>");
		// Translation hint: %1$s is a member's name. %2$s is the currency name, which may be "hours".
		$balance_help = sprintf(_('This is %1$s\'s account balance. It shows how many %2$s the member has earned minus what they have spent.'), $member->person[0]->first_name, $site_settings->getUnitString());
		$balance_hint = "";
	}

	// mb_strlen is necessary since we use a double-byte minus sign.
	$calculation_width = mb_strlen($balance_text) - .3;
	$earned_text = sprintf("%.2f", $member->earned);
	$spent_text = sprintf("%.2f", $member->spent);

	// Find out how wide the largest addend (earned/spent figure) is.
	$addend_width = max(strlen($earned_text), strlen($spent_text));

	/* The calculation below is intended to be nicely aligned, like so:
		Earned     7.00
		Spent   − 10.00
		‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾
		−   3  .  0   0  (large)
	*/
	$c = get_defined_constants();
	print <<<HTML
		<article class=karma-explanation>
			<table id=karma-explanation>
				<tr>
					<th/>
					<th>
						{$_("Experience points")}
					<th/>
					<th>
						{$_("Balance")}
				<tr class=calculation>
					<th>
						{$_("Calculation")}
					<td colspan=2 />
					<td>
						<table style="width: {$calculation_width}ex;">
							<tr>
								<td>{$_("Earned")}<td/><td style="width: {$addend_width}ex">{$earned_text}</td>
							<tr>
								<td>{$_("Spent")}<td> − <td>{$spent_text}</td>
						</table>

				<tr class=main-numbers>
					<th>
						{$_("Numbers")}
					<td class=karma>
						{$member->GetKarma()}
					<td rowspan=2 class=picture />
						<!-- Picture inserted via CSS -->
					<td class=balance>
						{$balance_text}
				<tr class=exposition>
					<th>
						{$_("Explanation")}
					<td class=karma>
						$karma_help
					<td>
						$balance_help
						<strong>$balance_hint</strong>
			</table>
		</article>
HTML;
}
else {
	// Translation hint: %s is the site's name.
	printf(_("Experience points are disabled in %s. If they were enabled, they would reflect your activity in the LETS system, and the equilibrium between how much you have done for others and how much others have done for you. Contact the administrators if you wish to request that they be enabled."), SITE_SHORT_TITLE);
	print "<img src=//". HTTP_BASE ."/images/handshake-color.svgz style='display: block; margin: 5em auto; width: 150px' width=75>";
}

print $p->MakePageFooter();

?>
