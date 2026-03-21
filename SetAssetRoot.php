#!/usr/bin/env php
<?php
/**	op-asset-hooks:/SetAssetRoot.php
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

//	...
$dirs = explode('/', __DIR__);

//	...
while( $dirs ){
	//	...
	$dir = array_pop($dirs);

	//	...
	if( $dir === 'asset' ){
		break;
	}
}

//	...
$path = join('/', $dirs)."/{$dir}/";

//	...
define('_ROOT_ASSET_', $path);

//	...
unset($dirs, $dir, $path);
