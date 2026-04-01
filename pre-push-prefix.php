#!/usr/bin/env php
<?php
/**	op-asset-hooks:/pre-push-prefix.php
 *
 * @created    2026-03-22
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
require_once(__DIR__.'/GetRules.php');

//	Init
$rules = GetRules();
$stdin = fopen('php://stdin', 'r');

//	...
while( $line = fgets($stdin) ){
	//	...
	list($local_ref, $local_sha, $remote_ref, $remote_sha) = explode(' ', trim($line));
	unset($local_ref, $remote_ref);

	//	Check if new branch.
	if( $remote_sha === str_repeat('0', 40) ){
		//	New branch is all check.
		$range = $local_sha;
	}else{
		$range = "{$remote_sha}..{$local_sha}";
	}
}

//	Get commit id list.
if( $range ?? null ){
	$commits = explode("\n", trim(shell_exec("git rev-list {$range}") ?? ''));
}else{
	$commits = [];
}

//	...
foreach( $commits as $commit ){
	//	...
	if(!$commit ){
		continue;
	}

	//	Get commit message.
	$message = trim(shell_exec("git log -1 --pretty=%s {$commit}"));

	//	Check prefix.
	if(!empty($rules['prefix']) ){
		$allowed = false;
		foreach( $rules['prefix'] as $prefix ){
			if( str_starts_with($message, $prefix) ){
				$allowed = true;
				break;
			}
		}
		if(!$allowed ){
			Fail(
				"This commit message prefix is not allowed\n" .
				"  allowed: " . implode(', ', $rules['prefix']) . "\n" .
				"  message: $message"
				);
		}
	}

	//	Check deny word.
	foreach( $rules['deny'] ?? [] as $word ){
		if( str_contains($message, $word) ){
			Fail("Commit message contains a denied word: $message");
		}
	}
}

//	Success
exit(0);

/**	Exit immediately on error
 *
 * @created    2026-01-08
 * @param      string     $message
 */
function Fail( string $message ) : void
{
	fwrite(STDERR, "✗ Rebase blocked: {$message}\n");
	exit(1);
}
