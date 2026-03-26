#!/usr/bin/env php
<?php
/**	op-asset-hooks:/pre-rebase.php
 *
 * @created    2026-01-08
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

/**	Config file path.
 *
 *	<pre>
 *	return = [
 *		//	Allow prefix. If you don't use it, you can leave it empty.
 *		'prefix' => [
 *			'New: ',
 *			'Add: ',
 *			'Chg: ',
 *			'Fix: ',
 *			'Doc: ',
 *			'Del: ',
 *		],
 *
 *		//	Deny words. If you don't use it, you can leave it empty.
 *		'deny' => [
 *			'foo',
 *			'bar',
 *		],
 *	];
 *	</pre>
 */
$rules = GetRules();
$commits = GetCommitIdsList($argv[1] ?? '');

//	Validate commit messages
while( $commit = array_pop($commits) ){
	//	Get commit.
	$comand = 'git log -1 --format=%s ' . escapeshellarg($commit);
	$output = [];
	$status = null;
	exec($comand, $output, $status);
	if( $status ){
		Fail("Read commit message is failed: $commit");
	}

	//	Get commit message.
	if(!$commit_message = $output[0] ?? null ){
		Fail("Commit message is empty: $commit");
	}

	//	Check prefix.
	if(!empty($rules['prefix']) ){
		$allowed = false;
		foreach( $rules['prefix'] ?? [] as $prefix ){
			if( str_starts_with($commit_message, $prefix) ){
				$allowed = true;
				break;
			}
		}
		if(!$allowed ){
			Fail(
				"This commit message prefix is not allowed\n" .
				"  allowed: " . implode(', ', $rules['prefix']) . "\n" .
				"  message: $commit_message"
			);
		}
	}

	//	Check deny word.
	foreach( $rules['deny'] ?? [] as $word ){
		if( str_contains($commit_message, $word) ){
			Fail("Commit message contains a denied word: $commit_message");
		}
	}
}

//	Success
exit(0);

/**	Get rebase target commit ids list.
 *
 * @created    2026-01-08
 * @param      string     $arg
 * @return     array
 */
function GetCommitIdsList( string $arg )
{
	/*
	//	Get rebase target commit from shell argument ($1 in shell)
	if( $target = $arg ){
		$target = escapeshellarg($target);
	}else{
		Fail("No rebase destination specified.");
	}

	//	Get rebase target commit id.
	if( $result = exec("git show-ref {$target}") ){
		$commit_id = explode(' ', trim($result))[0];
	}else if( $result = exec("git rev-parse {$target}") ){
		$commit_id  = trim($result);
	}else{
		Fail("Does not found commit id: {$target}");
	}
	*/

	//	Generate get commit id list comand.
	$comand = "git rev-list HEAD..{$arg}";

	//	Get commit id list.
	$commits = [];
	$status  = null;
	$line = exec($comand, $commits, $status);
	if( $status ){
		Fail("Get commit list is failed: status={$status}, $line");
	}

	//	...
	return $commits;
}

/** Exit immediately on error
 *
 * @created    2026-01-08
 * @param      string     $message
 */
function Fail( string $message ) : void
{
	fwrite(STDERR, "✗ Rebase blocked: {$message}\n");
	exit(1);
}
