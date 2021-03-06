<?php
/* Author : Romain "Artefact2" Dalmaso <artefact2@gmail.com>
* This program is free software. It comes without any warranty, to
* the extent permitted by applicable law. You can redistribute it
* and/or modify it under the terms of the Do What The Fuck You Want
* To Public License, Version 2, as published by Sam Hocevar. See
* http://sam.zoy.org/wtfpl/COPYING for more details. */

namespace CommonLoadoutFormat;

/* Just some (simple) helper functions */

function print_generic_error($message, $near = null) {
	if($near !== null) {
		$near = json_encode($near);
		$message .= ', near: '.$near;
	}

	global $__clf_errors;
	$__clf_errors[] = $message;
}

function fatal($message, $near = null) {
	print_generic_error('Fatal error: '.$message, $near);
}

$__had_warnings = false;
function warning($message, $near = null) {
	global $__clf_had_warnings;
	$__clf_had_warnings = true;

	print_generic_error('Warning: '.$message, $near);
}

function notice($message, $near = null) {
	print_generic_error('Notice: '.$message, $near);
}

function assume_string($name, $ctx) {
	if(isset($ctx[$name])) {
		if(!is_string($ctx[$name])) {
			warning("key '$name' is present but does not contain a string", $ctx);
			return false;
		}

		return true;
	}

	return false;
}

function assume_integer($name, $ctx) {
	if(isset($ctx[$name])) {
		if(!is_int($ctx[$name])) {
			warning("key '$name' is present but does not contain an integer", $ctx);
			return false;
		}

		return true;
	}

	return false;
}

function assume_array($name, $ctx) {
	if(isset($ctx[$name])) {
		if(!is_array($ctx[$name])) {
			warning("key '$name' is present but does not contain an array");
			return false;
		}

		return true;
	}

	return false;
}

function assume_thing_with_typeid($type, $entity) {
	if(!isset($entity['typeid'])) {
		fatal("required key 'typeid' not found", $entity);
		return FATAL;
	} else if(!is_int($entity['typeid'])) {
		fatal("required key 'typeid' does not contain an integer", $entity);
		return FATAL;
	}

	return assume_string('typename', $entity);
}

function assume_unique_names($presets, $namekey = 'presetname') {
	$names = array();
	$duplicatefree = true;

	foreach($presets as $p) {
		if(isset($p[$namekey])) {
			if(isset($names[$p[$namekey]])) {
				warning("duplicate preset name '".$p[$namekey]."'", $p);
				$duplicatefree = false;
			} else {
				$names[$p[$namekey]] = true;
			}
		}
	}

	return $duplicatefree;
}

function check_extraneous_properties(array $tocheck, array $expectedkeys) {
	foreach($tocheck as $k => $v) {
		if(!in_array($k, $expectedkeys, true)) {
			if(strpos($k, 'X-') !== 0) {
				warning("extraneous key '$k' is not prefixed by 'X-'");
			} else {
				notice("extraneous key '$k' not covered by the validation process");
			}
		}
	}
}
