<?php

/**
 * DOM
 */
function test_dom($xml_file)
{
  $doc = new DOMDocument();

  $valid = @$doc->loadXML(file_get_contents($xml_file));

  unset($doc);

  return $valid;
}

/**
 * SimpleXML
 */
function test_simplexml($xml_file)
{
  $sxe = @simplexml_load_file($xml_file);

  $valid = is_object($sxe)? true: false;

  unset($sxe);

  return $valid;
}

/**
 * XMLReader
 */
function test_xmlreader($xml_file)
{
  $reader = new XMLReader();
  $reader->open($xml_file, null, 1<<19);

  libxml_clear_errors();
  $use_internal_errors = libxml_use_internal_errors(true);

  while (@$reader->read()) {}

  $errors = libxml_get_errors();
  $valid = count($errors)? false: true;

  libxml_use_internal_errors($use_internal_errors);
  libxml_clear_errors();

  $reader->close();

  return $valid;
}

function test($function, $xml_file)
{
  switch ($function)
  {
    case 'dom':
      $result = test_dom($xml_file);
      break;
    case 'simplexml':
      $result = test_simplexml($xml_file);
      break;
    case 'xmlreader':
      $result = test_xmlreader($xml_file);
      break;
  }

  return $result;
}

if (defined('STDIN'))
{
  $time = microtime(true);

  $function  = $argv[1];
  $xml_file = $argv[2];

  $valid = test($function, $xml_file);
  $valid = ($valid? 'true': 'false');

  $memory_peak = memory_get_peak_usage(true);

  $time = microtime(true) - $time;

  $to_file = <<<TOFILE
Function:            $function
XML File:            $xml_file
Is valid file:       $valid
Time (seconds):      $time
Memory Peak (bytes): $memory_peak
--------------------

TOFILE;

  file_put_contents('benchmark_validate.txt', $to_file, FILE_APPEND);
}
