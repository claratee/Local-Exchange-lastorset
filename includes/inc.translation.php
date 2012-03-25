<?php

/* Initialize gettext */

global $supported_languages, $current_language;

// All supported languages by this version of Local Exchange.
$supported_languages = array(
	'en_US',     // English (no translation)
	'nb_NO.utf8' // Norwegian Bokmål
);

// Set the language cookie, if the user posted a preference.
storeLanguageChoice();

// Select the locale based on a variety of factors, and save it in this global variable.
$current_language = selectLocale();
$ret = setlocale(LC_MESSAGES, $current_language);

// Gettext invocations
bindtextdomain("messages", "./includes/lang");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");

// TODO These functions can probably go into a class.
/** Pick the locale to use based on the following prioritized factors:

	- The user's preferred language, as stored in his profile.
	- What the user picked in this session using the dropdown.
	- TODO: What the user agent suggests in the HTTP request.
	- The site's default language. */
function selectLocale() {
	global $supported_languages;

	// The fallback locale, used when nothing else works.
	// TODO: Move to settings
	$default_locale = "nb_NO.utf8";

	// First, if the user is logged in, check preferred language.
	$preference = retrieveLanguagePreference();
	if ($preference)
		return $preference;

	// If not logged in, or language is not available, check the cookie.
	if (isset($_SESSION['preferred_language']))
	{
		$idx = array_search($_SESSION['preferred_language'], $supported_languages);
		if ($idx !== false)
			return $supported_languages[$idx];
	}

	// If no cookie is set, accept from HTTP.
	// TODO doesn't work, http.so crashes
	// $http_locale = http_negotiate_language($supported_languages);

	// If all else fails, use the default language.
	return $default_locale;
}

/**	If the user is logged in, get their language preference.

	@return the string stored by storeLanguageChoice, or false if the user is logged in
	        or has not chosen a language. */

/*	This code (and the database portion of storeLanguageChoice) should really have been with the other
	code in the cMember class, but because of an unfortunate circular dependency chain, it cannot be
	there: this file would depend on the cMember class, which depends on inc.config.php, which
	depends on selecting a language, which is done in this file.

	The future solution is likely to translate customizable strings at display time, so that the
	configuration file no longer dependent on language.  Alternatively, use a more sophisticated
	configuration mechanism, where default strings can be set programmatically (or some other way
	Gettext can pick them up) and webmaster can override. */
	
function retrieveLanguagePreference() {
	if (!isset($_SESSION["user_login"]))
		return false;
	$user = $_SESSION["user_login"];

	// PHP will automatically reuse the link when the database class connects.
	mysql_connect(DATABASE_SERVER,DATABASE_USERNAME,DATABASE_PASSWORD)
		   or die("Could not connect to database for language selection");
	mysql_selectdb(DATABASE_NAME)
		   or die("Could not select database");

	$resource = mysql_query("SELECT preferred_language FROM ". DATABASE_MEMBERS ." WHERE member_id = '". $user ."'");
	if (!$resource)
		return false;

	$row = mysql_fetch_array($resource);
	return $row[0];
}

/**	Validate the language setting and store it in a cookie and in the database. */

/*	The direct database access performed by this function is unfortunate; see note at
	retrieveLanguagePreference for more information. */
function storeLanguageChoice() {
	global $supported_languages;

	// If user chose a language, store it in a cookie
	if (!isset($_POST['set_language']))
		return;

	$idx = array_search($_POST['set_language'], $supported_languages);
	if ($idx === false)
		return;

	$_SESSION['preferred_language'] = $supported_languages[$idx];

	// If user is logged in, store their choice in the database
	if (!isset($_SESSION["user_login"]))
		return;
	$user = $_SESSION["user_login"];

	// No escaping needed, since user is not in control of string
	mysql_query("UPDATE ". DATABASE_MEMBERS ."
		SET preferred_language = '". $_SESSION['preferred_language'] ."'
		WHERE member_id = '". $user. "'");
}

/** Custom translations for strings in inc.config.php that do not appear in other
	source code. Configure using $customTranslations. */
function translate($string) {
	global $current_language, $customTranslations;

	if (isset($customTranslations[$string]) &&
		isset($customTranslations[$string][$current_language]))
		return $customTranslations[$string][$current_language];
	else
		return $string;
}
?>
