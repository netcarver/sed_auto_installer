<?php

//$plugin['url'] = '$HeadURL: http://porteo.us/svn/txp_plugins/trunk/l10n.php $';
//$plugin['date'] = '$LastChangedDate: 2006-12-28 16:11:37 +0800 (Thu, 28 Dec 2006) $';
$plugin['revision'] = '$LastChangedRevision$';

$revision = @$plugin['revision'];
if( !empty( $revision ) )
	{
	$parts = explode( ' ' , trim( $revision , '$' ) );
	$revision = $parts[1];
	if( !empty( $revision ) )
		$revision = ' (r' . $revision . ')';
	}

$plugin['name'] = 'sed_auto_inst';
$plugin['version'] = '0.2' . $revision;
$plugin['author'] = 'Netcarver';
$plugin['author_uri'] = 'http://txp-plugins.netcarving.com';
$plugin['description'] = 'Plugin auto-installer';
$plugin['type'] = '1';

@include_once('../zem_tpl.php');

if (0) {
?>
<!-- CSS SECTION
# --- BEGIN PLUGIN CSS ---
	<style type="text/css">
	div#sed_auto_inst_help td { vertical-align:top; }
	div#sed_auto_inst_help code { font-weight:bold; font: 105%/130% "Courier New", courier, monospace; background-color: #FFFFCC;}
	div#sed_auto_inst_help .code_tag{ font-weight:normal; border:1px dotted #999; background-color: #f0e68c; display:block; margin:10px 10px 20px; padding:10px; }
	div#sed_auto_inst_help a:link, div#sed_auto_inst_help a:visited { color: blue; text-decoration: none; border-bottom: 1px solid blue; padding-bottom:1px;}
	div#sed_auto_inst_help a:hover, div#sed_auto_inst_help a:active { color: blue; text-decoration: none; border-bottom: 2px solid blue; padding-bottom:1px;}
	div#sed_auto_inst_help h1 { color: #369; font: 20px Georgia, sans-serif; margin: 0; text-align: center; }
	div#sed_auto_inst_help h2 { border-bottom: 2px solid black; padding:10px 0 0; color: #369; font: 17px Georgia, sans-serif; }
	div#sed_auto_inst_help h2 a { text-decoration: none; }
	div#sed_auto_inst_help ul ul { font-size:85%; }
	div#sed_auto_inst_help h3 { color: #693; font: bold 12px Arial, sans-serif; letter-spacing: 1px; margin: 10px 0 0;text-transform: uppercase;}
	</style>
# --- END PLUGIN CSS ---
-->
<!-- HELP SECTION
# --- BEGIN PLUGIN HELP ---
<div id="sed_auto_inst_help">

h1(#top). SED Auto Installer Help.

<br />

|_. Copyright 2007 Stephen Dickinson. |

<br />

h2. Table Of Contents.

* "Introduction":#intro
* "Credits":#credits

<br/>

h2(#intro). Introduction

This plugin allows the system to automatically load and enable a set of plugins from the files/sed_autoinst/plugins directory of your current installation.

 <span style="float:right"><a href="#top" title="Jump to the top">top</a></span>


h2(#credits). Credits.

Inspiration for this plugin came from the need to quickly be able to setup (restore) a site to a known state for testing of plugins.

 <span style="float:right"><a href="#top" title="Jump to the top">top</a></span>

<br />

-- _Stephen Dickinson_
2007.

</div>
# --- END PLUGIN HELP ---
-->
<?php
}
# --- BEGIN PLUGIN CODE ---

if( @txpinterface === 'admin' )
	{
	add_privs('sed_plugin_auto_install', '1');
	register_callback('sed_plugin_auto_install', 'admin' , 'sed_auto_inst' );
	}

function sed_plugin_auto_install( $event , $step )
	{
	global $txpcfg , $prefs;

	require_privs('sed_plugin_auto_install');

	include_once $txpcfg['txpath'].'/include/txp_plugin.php';

	$debug = false;
	if( $debug ) echo br , "Loading: Auto Install Plugin.";

	#
	#	Build a list of all files in the special plugins dir...
	#
	$files = array();
	$path = $prefs['file_base_path'].DS.'sed_autoinst'.DS.'plugins';
	if( $debug ) echo br , "Auto Install Plugins... Accessing dir($path) ...";
	$dir = @dir( $path );
	if( $dir === false )
		{
		if( $debug ) echo " failed!";
		return;
		}
	while( $file = $dir->read() )
		{
		if($file!=='.' && $file!=='..')
			{
			if( $debug ) echo br , "... found ($file)";
			$fileaddr = $path.DS.$file;
			if( !is_dir($fileaddr) )
				{
				$files[] = $file;
				if( $debug ) echo " : accepting as plugin.";
				}
			}
		}
	$dir->close();

	#
	#	Exit if there is nothing to do...
	#
	if( empty( $files ) )
		{
		if( $debug ) echo " no plugins found: exiting.";
		return;
		}

	#
	#	Process each file...
	#
	foreach( $files as $file )
		{
		if( $debug ) echo br , "Processing $file : ";
		#
		#	Load the file into the $_POST['plugin64'] entry and try installing it...
		#
		$plugin = join( '', file($path.DS.$file) );
		$_POST['plugin64'] = $plugin;
		if( $debug ) echo "installing,";
		include_once $txpcfg['txpath'].'/lib/txplib_head.php';
		plugin_install();

		#
		#	Drop the file extension to leave only the name...
		#
		$bits = explode( '.' , $file );
		array_pop( $bits );
		$file = join( '.' , $bits );

		#
		#  Try enabling it now (guesses at plugin name from file name)...
	 	#

		$plugin_name = strtr( $file , array('-'=>'_') );
		$_POST['name'] = $plugin_name;
		$_POST['status'] = '0';
		if( $debug ) echo " attempting to activate $plugin_name.";
		switch_status();
		}

	if( !$debug )
		{
		#
		#	Remove ourself from the plugin installation now (nice idea Ruud!)...
		#
		safe_delete('txp_plugin', "name = 'sed_auto_inst'");
		while( @ob_end_clean() );
		header('Location: '.hu.'textpattern/index.php?event=plugin');
		header('Connection: close');
		header('Content-Length: 0');
		exit(0);
		}
	}

# --- END PLUGIN CODE ---

?>
