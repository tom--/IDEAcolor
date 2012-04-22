#!/usr/bin/php
<?php
/**
 * @var
 */

// check arguments
if ($argc !== 3 || !is_readable($argv[1]) || !is_readable($argv[2]) ) {
	fwrite(STDERR, <<<EOT
Usage: {$argv[0]} csd_export_file ide_color_file
  csd_export_file — XML scheme export from http://colorschemedesigner.com
  ide_color_file — JetBrains IDE color file
On success, ide_color_file is renamed with numeric suffix and new file is
written in its place.

EOT
	);
	exit(1);
}

// read and parse input files
$s = file_get_contents($argv[1]);
if ($s === false) {
	fwrite(STDERR, 'Cannot read CSD export file' . PHP_EOL);
	exit(1);
}

try {
	$x = new SimpleXMLElement($s);
} catch (Exception $e) {
	fwrite(STDERR, 'XML error reading CSD export: ' . $e->getMessage() . PHP_EOL);
	exit(1);
}

$scheme = array();
foreach ($x[0]->colorset as $colorset) {
	foreach ($colorset->color as $color) {
		$scheme[(string) $colorset['id']][(int) $color['nr']] = (string) $color['rgb'];
	}
}

$idefile = $argv[2];
$s = file_get_contents($idefile);
if ($s === false) {
	fwrite(STDERR, 'Cannot read IDE color file: ' . $idefile . PHP_EOL);
	exit(1);
}

try {
	$x = new SimpleXMLElement($s);
} catch (Exception $e) {
	fwrite(STDERR, 'XML error reading IDE colors: ' . $e->getMessage() . PHP_EOL);
	exit(1);
}

/**
 * @var array color replacement map. use the color list tab in ColorScheme
 * Designer 3 for reference
 */
$map = array(

	/**
	 * this mapping to ColorScheme Designer requires a 4 color scheme, either
	 * tetrad or accented tetrad. it was designed to work with the accented
	 * tetrad with the color of strings being Primary-1, e.g.
	 * http://colorschemedesigner.com/#1440peJrrw0w0
	 */

	'PHP_STRING' => $scheme['primary'][1],
	'PHP_HEREDOC_CONTENT' => $scheme['primary'][1],
	'PHP_NUMBER' => $scheme['primary'][3],
	'PHP_CONSTANT' => $scheme['primary'][3],
	'PHP_ESCAPE_SEQUENCE' => $scheme['primary'][4],

	'PHP_IDENTIFIER' => $scheme['secondary-a'][1],

	'PHP_VAR' => $scheme['complement'][4],

	'PHP_TAG' => $scheme['complement'][1],
	'PHP_BRACKETS' => $scheme['complement'][5],
	'PHP_KEYWORD' => $scheme['secondary-b'][4],
	'PHP_PREDEFINED SYMBOL' => $scheme['secondary-b'][5],


//	'PHP_HEREDOC_ID' => $scheme[''][],
//	'PHP_COMMA' => $scheme[''][],
//	'PHP_COMMENT' => $scheme[''][],
//	'PHP_DOC_COMMENT_ID' => $scheme[''][],
//	'PHP_DOC_TAG' => $scheme[''][],
//	'PHP_EXEC_COMMAND_ID' => $sclheme[''][],
//	'PHP_MARKUP_ID' => $scheme[''][],
//	'PHP_OPERATION_SIGN' => $scheme[''][],
//	'PHP_SCRIPTING_BACKGROUND' => $scheme[''][],
//	'PHP_SEMICOLON' => $scheme[''][],


	'JS.KEYWORD' => $scheme['secondary-b'][4],
	'JS.PARENTHS' => $scheme['complement'][4],
	'JS.BRACES' => $scheme['complement'][4],
	'JS.BRACKETS' => $scheme['complement'][4],

	'JS.PARAMETER' => $scheme['complement'][1],
	'JS.GLOBAL_VARIABLE' => $scheme['complement'][1],
	'JS.INSTANCE_MEMBER_VARIABLE' => $scheme['secondary-a'][1],
	'JS.LOCAL_VARIABLE' => $scheme['complement'][1],
	'JS.ATTRIBUTE' => $scheme['secondary-a'][1],
	'JS.GLOBAL_FUNCTION' => $scheme['secondary-a'][1],
	'JS.INSTANCE_MEMBER_FUNCTION' => $scheme['secondary-a'][2],

	'JS.STRING' => $scheme['primary'][1],
	'JS.NUMBER' => $scheme['primary'][3],
	'JS.VALID_STRING_ESCAPE' => $scheme['primary'][4],
	'JS.REGEXP' => $scheme['primary'][2],

//	'JS.BADCHARACTER' => $scheme[''][],
//	'JS.BLOCK_COMMENT' => $scheme[''][],
//	'JS.COMMA' => $scheme[''][],
//	'JS.DOC_COMMENT' => $scheme[''][],
//	'JS.DOC_MARKUP' => $scheme[''][],
//	'JS.DOC_TAG' => $scheme[''][],
//	'JS.DOT' => $scheme[''][],
//	'JS.INVALID_STRING_ESCAPE' => $scheme[''][],
//	'JS.LINE_COMMENT' => $scheme[''][],
//	'JS.OPERATION_SIGN' => $scheme[''][],
//	'JS.SEMICOLON' => $scheme[''][],

	'HTML_ATTRIBUTE_NAME' => $scheme['secondary-a'][4],
	'HTML_ATTRIBUTE_VALUE' =>  $scheme['secondary-a'][1],
//	'HTML_COMMENT' => $scheme[''][],
	'HTML_ENTITY_REFERENCE' => $scheme['secondary-a'][5],
	'HTML_TAG' => $scheme['secondary-a'][2],
	'HTML_TAG_NAME' => $scheme['secondary-a'][2],


//	'CSS.COMMENT' => $scheme[''][],
//	'CSS.FUNCTION' => $scheme[''][],
	'CSS.TAG_NAME' => $scheme['secondary-a'][1], // div, td
	'CSS.IDENT' => $scheme['secondary-a'][4],  // #id .class-name
	'CSS.PROPERTY_NAME' => $scheme['primary'][1], // position, margin
	'CSS.PROPERTY_VALUE' => $scheme['complement'][1], // center, none, right
	'CSS.NUMBER' => $scheme['complement'][4],
	'CSS.STRING' => $scheme['complement'][4],
	'CSS.URL' => $scheme['complement'][5],
	'CSS.IMPORTANT' => $scheme['secondary-b'][5],
	'CSS.KEYWORD' => $scheme['secondary-b'][4], // @import, @-moz-document


/*	'REGEXP.BAD_CHARACTER' => $scheme[''][],
	'REGEXP.BRACES' => $scheme[''][],
	'REGEXP.BRACKETS' => $scheme[''][],
	'REGEXP.CHAR_CLASS' => $scheme[''][],
	'REGEXP.COMMA' => $scheme[''][],
	'REGEXP.ESC_CHARACTER' => $scheme[''][],
	'REGEXP.INVALID_STRING_ESCAPE' => $scheme[''][],
	'REGEXP.META' => $scheme[''][],
	'REGEXP.PARENTHS' => $scheme[''][],
	'REGEXP.QUOTE_CHARACTER' => $scheme[''][],
	'REGEXP.REDUNDANT_ESCAPE' => $scheme[''][],

//	'SQL_BAD_CHARACTER' => $scheme[''][],
	'SQL_COLUMN' => $scheme[''][],
	'SQL_COMMENT' => $scheme[''][],
	'SQL_KEYWORD' => $scheme[''][],
	'SQL_NUMBER' => $scheme[''][],
	'SQL_PROCEDURE' => $scheme[''][],
	'SQL_SCHEMA' => $scheme[''][],
	'SQL_STRING' => $scheme[''][],
	'SQL_TABLE' => $scheme[''][],*/

);

// replace colors in the IDE color file
foreach ($x->attributes->option as $n => $v) {
	$key = (string) $v['name'];
	//echo $key . PHP_EOL; continue;
	if (isset($map[$key])) {
		$val = strtolower($map[$key]);
		foreach ($v->value->option as $m => $z) {
			$name = (string) $z['name'];
			if ($name === 'FOREGROUND') {
				fwrite(STDERR,
					$key . ' ' . $name . ' '
					. (string) $z['value'] . ' => ' . $val . PHP_EOL);
				$z['value'] = $val;
			}
		}
	}
}

// touch up the SimpleXML output
$s = $x->asXML();
$s = preg_replace('%(?<!\s)/>$%m', ' />', $s);

// backup the input IDE color file
$i = 0;
while (file_exists($backup = $idefile . '.' . $i++))
	if ($i>99) {
		fwrite(STDERR, 'Too many backups of ' . $idefile . PHP_EOL);
		exit(1);
	};
if (!copy($idefile, $backup)) {
	fwrite(STDERR, 'Cannot backup IDE color file to ' . $backup . PHP_EOL);
	exit(1);
}

// replace the input IDE color file
unlink($idefile);
file_put_contents($idefile, $s);