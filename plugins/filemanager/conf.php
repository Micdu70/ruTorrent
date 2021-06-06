<?php

$fm['tempdir'] = '/tmp';		// path were to store temporary data ; must be writable
$fm['mkdperm'] = 755; 		// default permission to set to new created directories

// set with fullpath to binary or leave empty

if(empty($pathToExternals['rar']))	// May be path already defined?
{
	$pathToExternals['rar'] = '';
}
if(empty($pathToExternals['zip']))	// May be path already defined?
{
	$pathToExternals['zip'] = '';
}
if(empty($pathToExternals['unzip']))	// May be path already defined?
{
	$pathToExternals['unzip'] = '';
}
if(empty($pathToExternals['tar']))	// May be path already defined?
{
	$pathToExternals['tar'] = '';
}
if(empty($pathToExternals['gzip']))	// May be path already defined?
{
	$pathToExternals['gzip'] = '';
}
if(empty($pathToExternals['bzip2']))	// May be path already defined?
{
	$pathToExternals['bzip2'] = '';
}

// archive mangling, see archiver man page before editing

$fm['archive']['types'] = array('rar', 'zip', 'tar', 'gzip', 'bzip2');

$fm['archive']['compress'][0] = range(0, 5);
$fm['archive']['compress'][1] = array('-0', '-1', '-9');
$fm['archive']['compress'][2] = $fm['archive']['compress'][3] = $fm['archive']['compress'][4] = array(0);

?>
