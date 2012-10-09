<?php
/**
 * e-mail obfuscator
 *
 * This file is part of a module for pluck (http://www.pluck-cms.org/).
 * It is the main module file.
 *
 * @copyright 2012 Paul Voegler
 * @author Paul Voegler (http://www.voegler.eu/)
 * @version 1.0 (October 2012)
 * @license GPL Version 3, 29 June 2007
 * See docs/COPYING for the complete license.
 */

defined('IN_PLUCK') OR exit('Access denied!');

function email_obfuscator_info() {
	global $lang;

	return array(
		'name'          => $lang['email_obfuscator']['module_name'],
		'intro'         => $lang['email_obfuscator']['module_intro'],
		'version'       => '1.0',
		'author'        => 'Paul Voegler',
		'website'       => 'http://www.voegler.eu/',
		'icon'          => 'images/icon.png',
		'compatibility' => '4.7'
	);
}

function dechex2($number) {
	$out = strtoupper(dechex($number));

	if (strlen($out) % 2 > 0) {
		return '0' . $out;
	} else {
		return $out;
	}
}

function encrypt_ent($text) {
	$out = '';
	$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
	$n = strlen($text);
	for ($i = 0; $i < $n; $i++) {
		if (rand(0, 1)) {
			$out .= '&#' . ord($text[$i]) . ';';
		} else {
			$out .= '&#x' . dechex2(ord($text[$i])) . ';';
		}
	}

	return $out;
}

function encrypt_urlent($text) {
	$out = '';
	$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
	$text = urldecode($text);
	$n = strlen($text);
	for ($i = 0; $i < $n; $i++) {
		if (rand(0, 1) || ord($text[$i]) > 0x7F) {
			$tmp = '%' . dechex2(ord($text[$i]));
			for ($j = 0; $j < 3; $j++) {
				if (rand(0, 1)) {
					$out .= encrypt_ent($tmp[$j]);
				} else {
					$out .= $tmp[$j];
				}
			}
		} else {
			$out .= encrypt_ent($text[$i]);
		}
	}

	return $out;
}

function email_obfuscator_callback($matches) {
	return '<!--googleoff: all-->' . $matches[1] . encrypt_ent($matches[2]) . encrypt_urlent($matches[3]) . $matches[4] . encrypt_ent($matches[5]) . $matches[6] . '<!--googleon: all-->';
}

function email_obfuscator_theme_content(&$content) {
	$content = preg_replace_callback('/(\<a [^>]*?(?<= )href[\s\r\n\t]*\=[\s\r\n\t]*")(mailto:)([^"]*)("[^>]*\>)([^<]*)(\<\/a[\s\r\n\t]*\>)/s', 'email_obfuscator_callback', $content);
}
?>