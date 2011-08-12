<?php

/**
 * Create with DOM
 */
function test_dom($items_number, $item_data)
{
  $doc = new DomDocument('1.0');
  $doc->formatOutput = true;

  $parent = $doc->createElement(PARENT_TAG);
  $parent = $doc->appendChild($parent);

  for ($i = 1; $i <= $items_number; $i++)
  {
    $item = $doc->createElement(ITEM_TAG);
    $item = $parent->appendChild($item);
    $item->setAttribute('number', $i);

    foreach ($item_data as $key => $value)
    {
      $child = $doc->createElement($key);
      $child = $item->appendChild($child);
      $val = $doc->createTextNode($value);
      $val = $child->appendChild($val);
    }

    $number = $doc->createElement('number');
    $number = $item->appendChild($number);
    $number->setAttribute('value', $i);
    $val = $doc->createTextNode($i);
    $val = $number->appendChild($val);
  }

  file_put_contents("file_dom_{$items_number}.xml", $doc->saveXML());
}

/**
 * Create with SimpleXML
 */
function test_simple_xml($items_number, array $item_data)
{
  $sxe = new SimpleXMLElement('<'.PARENT_TAG.' />');

  for ($i = 1; $i <= $items_number; $i++)
  {
    $item = $sxe->addChild(ITEM_TAG);
    $item->addAttribute('number', $i);

    foreach ($item_data as $key => $value)
    {
      $item->addChild($key, $value);
    }

    $number = $item->addChild('number', $i);
    $number->addAttribute('value', $i);
  }

  file_put_contents("file_simplexml_{$items_number}.xml", $sxe->asXML());
}

/**
 * Create with XMLWriter
 */
function test_xml_writer($items_number, array $item_data)
{
  $writer = new XMLWriter();

  //$writer->openURI('php://output'); // Echo to user
  $writer->openURI("file_xmlwriter_{$items_number}.xml");
  $writer->setIndent(true);
  $writer->setIndentString("  ");

  $writer->startDocument('1.0');

  $writer->startElement(PARENT_TAG);

  for ($i = 1; $i <= $items_number; $i++)
  {
    $writer->startElement(ITEM_TAG);
    $writer->writeAttribute('number', $i);

      foreach ($item_data as $key => $value)
      {
        $writer->startElement($key);
          $writer->writeCData($value);
        $writer->endElement();
      }

      $writer->startElement('number');
        $writer->writeAttribute('value', $i);
        $writer->writeCData($i);
      $writer->endElement();

    $writer->endElement(); // ITEM_TAG
  }

  $writer->endElement(); // PARENT_TAG
}

/**
 * Create with string
 */
function test_string($items_number, array $item_data)
{
  $string = '<?xml version="1.0"?>'.PHP_EOL;
  $string.= '<'.PARENT_TAG.'>'.PHP_EOL;

  for ($i = 1; $i <= $items_number; $i++)
  {
    $string.= '  <'.ITEM_TAG.' number="'.$i.'">'.PHP_EOL;

    foreach ($item_data as $key => $value)
    {
      $string.= '    <'.$key.'><![CDATA['.$value.']]></'.$key.'>'.PHP_EOL;
    }

    $string.= '    <number value="'.$i.'">'.$i.'</number>'.PHP_EOL;
    $string.= '  </'.ITEM_TAG.'>'.PHP_EOL;
  }

  $string.= '</'.PARENT_TAG.'>'.PHP_EOL;

  file_put_contents("file_string_{$items_number}.xml", $string);
}

/**
 * Create with Stream
 */
function test_stream($items_number, array $item_data)
{
  $handle = fopen("file_stream_{$items_number}.xml", 'w');
  fwrite($handle, '<?xml version="1.0"?>'.PHP_EOL);
  fwrite($handle, '<'.PARENT_TAG.'>'.PHP_EOL);

  for ($i = 1; $i <= $items_number; $i++)
  {
    fwrite($handle, '  <'.ITEM_TAG.' number="'.$i.'">'.PHP_EOL);

    foreach ($item_data as $key => $value)
    {
      fwrite($handle, '    <'.$key.'><![CDATA['.$value.']]></'.$key.'>'.PHP_EOL);
    }

    fwrite($handle, '    <number value="'.$i.'">'.$i.'</number>'.PHP_EOL);
    fwrite($handle, '  </'.ITEM_TAG.'>'.PHP_EOL);
  }

  fwrite($handle, '</'.PARENT_TAG.'>'.PHP_EOL);
  fclose($handle);
}

function test($function, $items_number, array $item_data)
{
  switch ($function)
  {
    case 'dom':
      test_dom($items_number, $item_data);
      break;
    case 'simplexml':
      test_simple_xml($items_number, $item_data);
      break;
    case 'xmlwriter':
      test_xml_writer($items_number, $item_data);
      break;
    case 'string':
      test_string($items_number, $item_data);
      break;
    case 'stream':
      test_stream($items_number, $item_data);
      break;
  }
}

if (defined('STDIN'))
{
  $time = microtime(true);

  $function  = $argv[1];
  $items_number = $argv[2];

  define('PARENT_TAG', 'parent');
  define('ITEM_TAG', 'item');

  $item_data = array(
    'title' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce auctor.',
    'link' => 'http://www.lipsum.com/feed/html',
    'body' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent [leo] nulla, porttitor sit amet euismod ut, venenatis eget erat. Mauris at lectus eget lorem molestie rhoncus. Praesent &amp; dictum dapibus ligula, vel elementum tellus imperdiet egestas. Vestibulum id lorem ut lacus malesuada commodo sit amet ac magna. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce nunc lacus, accumsan nec fermentum ac, tristique at erat. Pellentesque id accumsan mauris. Fusce ante erat, suscipit id % ullamcorper a, dignissim in neque!. Ut suscipit, magna quis ultricies rhoncus, neque odio accumsan neque, volutpat ornare est urna in ipsum. Vivamus orci leo, mollis sed faucibus in, adipiscing vitae risus. Curabitur ornare facilisis feugiat. Proin venenatis nunc {eget} nisi mollis sit/amet feugiat dolor convallis. Suspendisse congue, dolor vitae consequat tempus, arcu &amp; dolor posuere arcu, vel mollis lacus = purus vehicula elit?. Ut in consequat eros.</p><p>Donec quis quam mauris, sed porttitor arcu. "Nulla at arcu ac lorem blandit sodales ullamcorper sed lectus". Mauris sed erat metus, ut molestie sem. Nulla et purus arcu. Suspendisse potenti. Sed egestas, neque non accumsan iaculis, diam nulla eleifend eros, eu ultricies # nisl velit eu + elit. Curabitur et tortor congue nisi adipiscing pretium et non - arcu. Vivamus ut sem ante. Integer sed justo nec est malesuada commodo. Nunc porta mattis mauris, in luctus nulla facilisis sed. Vestibulum velit nibh, pretium eget luctus non, rhoncus $ ac tellus. Nulla mattis justo a nulla viverra commodo. Quisque porta ipsum et urna blandit cursus. Mauris hendrerit rhoncus tortor, a luctus odio imperdiet a. Suspendisse ac diam et sem _ imperdiet porta ut et dolor.</p><p>Vestibulum eleifend, quam tincidunt vulputate consectetur, sem lacus tincidunt metus, pharetra bibendum nulla leo quis massa. In et consectetur erat. In eget diam (at justo vestibulum hendrerit) non ut arcu. Maecenas: nec aliquam ipsum. Quisque bibendum nisl eu dui tempor a <tristique> mi posuere. Etiam accumsan nisi ac lacus euismod ut feugiat sem lobortis. Aliquam a neque sapien, non volutpat eros. Donec tincidunt vehicula odio, sit amet @ fringilla dolor fringilla eget. Vestibulum placerat volutpat sapien, vitae porta dui feugiat vitae. Curabitur quis tincidunt lectus. Integer fermentum condimentum euismod.</p>',
  );

  test($function, $items_number, $item_data);

  $memory_peak = memory_get_peak_usage(true);

  $time = microtime(true) - $time;

  $to_file = <<<TOFILE
Function:            $function
Items Number:        $items_number
Time (seconds):      $time
Memory Peak (bytes): $memory_peak
--------------------

TOFILE;

  file_put_contents('benchmark_create.txt', $to_file, FILE_APPEND);
}
