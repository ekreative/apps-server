<?php

namespace Ekreative\AppsBundle\Helper;


class Helper
{

    public static function getPlistString($ipa, $bundleIdentifier, $version, $title)
    {


        $imp = new \DOMImplementation();
        $dtd = $imp->createDocumentType("plist","-//Apple//DTD PLIST 1.0//EN", "http://www.apple.com/DTDs/PropertyList-1.0.dtd");
        $dom = $imp->createDocument("", "", $dtd);

        $dom->encoding = "UTF-8";

        $dom->formatOutput = true;
        $dom->appendChild($element = $dom->createElement('plist'));
        $element->setAttribute('version','1.0');

        $element->appendChild($dict = $dom->createElement('dict') );
        $dict->appendChild($dom->createElement('key','items') );
        $dict->appendChild($array = $dom->createElement('array') );

        $array->appendChild($mainDict = $dom->createElement('dict') );

        $mainDict->appendChild($dom->createElement('key','assets') );
        $mainDict->appendChild($array = $dom->createElement('array') );

        $array->appendChild($dict = $dom->createElement('dict') );
        $dict->appendChild($dom->createElement('key','kind') );
        $dict->appendChild($dom->createElement('string','software-package') );
        $dict->appendChild($dom->createElement('key','url') );
        $dict->appendChild($dom->createElement('string',$ipa) );


        $mainDict->appendChild($dom->createElement('key','metadata') );

        $mainDict->appendChild($dict = $dom->createElement('dict') );
        $dict->appendChild($dom->createElement('key','bundle-identifier') );
        $dict->appendChild($dom->createElement('string',$bundleIdentifier) );

        $dict->appendChild($dom->createElement('key','bundle-version') );
        $dict->appendChild($dom->createElement('string',$version) );

        $dict->appendChild($dom->createElement('key','kind') );
        $dict->appendChild($dom->createElement('string','software') );

        $dict->appendChild($dom->createElement('key','title') );
        $dict->appendChild($titleElement = $dom->createElement('string') );

        $titleElement->appendChild($dom->createTextNode($title . '-v.' . $version));
        return $dom->saveXML();


    }
}