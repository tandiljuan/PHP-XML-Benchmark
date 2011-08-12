<?php

/**
 * DOM
 */
function test_dom($xml_file)
{
  $items_number = 0;

  $doc = new DOMDocument();
  $doc->loadXML(file_get_contents($xml_file));

  foreach ($doc->getElementsByTagName(ITEM_TAG) as $item)
  {
    $data = array();

    foreach($item->childNodes as $child)
    {
      if ($child->nodeType == XML_ELEMENT_NODE)
      {
        $data[$child->nodeName] = $child->nodeValue;
      }
    }

    unset($data);
    $items_number++;
  }

  return $items_number;
}

/**
 * SimpleXML
 */
function test_simplexml($xml_file)
{
  $items_number = 0;

  $sxe = simplexml_load_file($xml_file);

  foreach ($sxe->{ITEM_TAG} as $item)
  {
    $data = array();

    foreach ($item->children() as $child)
    {
      $data[$child->getName()] = (string)$child;
    }

    unset($data);
    $items_number++;
  }

  return $items_number;
}

/**
 * XMLReader
 */
function test_xmlreader($xml_file)
{
  $items_number = 0;

  $reader = new XMLReader();
  $reader->open($xml_file, null, 1<<19);

  while ($reader->read())
  {
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->localName == ITEM_TAG)
    {
      $data = array();

      while ($reader->read())
      {
        if ($reader->nodeType == XMLReader::ELEMENT)
        {
          $name = $reader->localName;
          $value = '';

          $reader->read();

          if ($reader->nodeType == XMLReader::TEXT)
          {
            $value = $reader->value;
          }

          $data[$name] = $value;
        }

        if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == ITEM_TAG)
        {
          break;
        }
      }

      unset($data);
      $items_number++;
    }
  }

  $reader->close();

  return $items_number;
}

/**
 * XMLReader + SimpleXML
 */
function test_xmlreader_simplexml($xml_file)
{
  $items_number = 0;

  $reader = new XMLReader();
  $reader->open($xml_file, null, 1<<19);

  while ($reader->read())
  {
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == ITEM_TAG)
    {
      do
      {
        $data = array();
        $sxe = simplexml_load_string($reader->readOuterXml());

        foreach ($sxe->children() as $child)
        {
          $data[$child->getName()] = (string)$child;
        }

        unset($data);
        $items_number++;
      }
      while ($reader->next(ITEM_TAG));
    }
  }

  $reader->close();

  return $items_number;
}

/**
 * XMLReader + SimpleXML + XPath
 */
function test_xmlreader_simplexml_xpath($xml_file, array $xpath_queries)
{
  $items_number = 0;

  $reader = new XMLReader();
  $reader->open($xml_file, null, 1<<19);

  while ($reader->read())
  {
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == ITEM_TAG)
    {
      do
      {
        $data = array();
        $sxe = simplexml_load_string($reader->readOuterXml());

        foreach($xpath_queries as $key => $value)
        {
          $data[$key] = (string) array_shift($sxe->xpath($value));
        }

        unset($data);
        $items_number++;
      }
      while ($reader->next(ITEM_TAG));
    }
  }

  $reader->close();

  return $items_number;
}

/**
 * XMLReader + DOM
 */
function test_xmlreader_dom($xml_file)
{
  $items_number = 0;

  $reader = new XMLReader();
  $reader->open($xml_file, null, 1<<19);

  while ($reader->read())
  {
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == ITEM_TAG)
    {
      do
      {
        $data = array();
        $dom = $reader->expand();

        foreach($dom->childNodes as $child)
        {
          if ($child->nodeType == XML_ELEMENT_NODE)
          {
            $data[$child->nodeName] = $child->nodeValue;
          }
        }

        unset($data);
        $items_number++;
      }
      while ($reader->next(ITEM_TAG));
    }
  }

  $reader->close();

  return $items_number;
}

/**
 * XMLReader + DOM + XPath
 */
function test_xmlreader_dom_xpath($xml_file, array $xpath_queries)
{
  $items_number = 0;

  $reader = new XMLReader();
  $reader->open($xml_file, null, 1<<19);

  $doc = new DomDocument();

  while ($reader->read())
  {
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == ITEM_TAG)
    {
      do
      {
        $data = array();
        $dom = $doc->appendChild($reader->expand());

        $xpath = new DOMXPath($doc);

        foreach($xpath_queries as $key => $value)
        {
          $data[$key] = $xpath->query($value)->item(0)->nodeValue;
        }

        $doc->removeChild($dom);

        unset($data);
        $items_number++;
      }
      while ($reader->next(ITEM_TAG));
    }
  }

  $reader->close();

  return $items_number;
}

function test($function, $xml_file, $xpath)
{
  $result = 0;

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
    case 'xmlreader-simplexml':
      $result = test_xmlreader_simplexml($xml_file);
      break;
    case 'xmlreader-simplexml-xpath':
      $result = test_xmlreader_simplexml_xpath($xml_file, $xpath);
      break;
    case 'xmlreader-dom':
      $result = test_xmlreader_dom($xml_file);
      break;
    case 'xmlreader-dom-xpath':
      $result = test_xmlreader_dom_xpath($xml_file, $xpath);
      break;
  }

  return $result;
}

if (defined('STDIN'))
{
  $time = microtime(true);

  $function  = $argv[1];
  $xml_file = $argv[2];

  define('PARENT_TAG', 'parent');
  define('ITEM_TAG', 'item');

  $xpath = array(
    'title' => '/'.ITEM_TAG.'/title',
    'link' => '/'.ITEM_TAG.'/link',
    'body' => '/'.ITEM_TAG.'/body',
    'number' => '/'.ITEM_TAG.'/@number', // Attribute 'number' of element 'ITEM_TAG'
  );

  $items_number = test($function, $xml_file, $xpath);

  $memory_peak = memory_get_peak_usage(true);

  $time = microtime(true) - $time;

  $to_file = <<<TOFILE
Function:            $function
XML File:            $xml_file
Items Number:        $items_number
Time (seconds):      $time
Memory Peak (bytes): $memory_peak
--------------------

TOFILE;

  file_put_contents('benchmark_parse.txt', $to_file, FILE_APPEND);
}
