#!/usr/bin/env php
<?php
/**	op-asset-hooks:/GetRules.php
 *
 * @created    2026-03-21
 * @license    Apache-2.0
 * @package    op-asset-hooks
 * @copyright  Tomoaki Nagahara
 */

/**	Declare strict type
 *
 */
declare(strict_types=1);

/**	Include
 *
 */
require_once(__DIR__.'/SetAssetRoot.php');

/**	Get rebase rules from php format config file.
 *
 * @created    2026-01-08 op-asset-hooks:/pre-rebase.php
 * @moved      2026-03-21 op-asset-hooks:/GetRules.php
 * @return     array      $rules
 */
function GetRules()
{
	//	Config file path.
	$config_file = _ROOT_ASSET_.'/config/git-rebase-rules.php';

	//	Load rule from php config file.
	if( file_exists($config_file) ){
		//	Load config value.
		$rules = (function($config_file){
			return include($config_file);
		})($config_file);

		//	Validate value.
		if(!is_array($rules) ){
			Fail("Config file must return array: $config_file");
		}
	}else{
		$rules = [];
	}

	//	Check values.
	foreach( ['prefix','deny'] as $key ){
		//	...
		if( isset($rules[$key]) ){
			if(!is_array($rules[$key])){
				Fail("The value of {$key} must be a array: type=".gettype($rules[$key]));
			}
		}

		//	...
		foreach( $rules[$key] ?? [] as $value ){
			if (!is_string($value)) {
				Fail("The value of {$key} must be a string.");
			}
		}
	}

	//	Add current branch name.
	if( isset($rules['prefix']) ){
		if( $branch = trim(`git branch --show-current` ?? '') ){
			$rules['prefix'][] = "{$branch}: ";
		}
	}

	//	Return rules.
	return $rules;
}
