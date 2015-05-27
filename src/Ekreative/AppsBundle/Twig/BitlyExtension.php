<?php

namespace Ekreative\AppsBundle\Twig;

class BitlyExtension extends \Twig_Extension {


    private $enabled = false;
    private $rukbatBitly;
    /**
     * Constructor method
     *
     * @param IdentityTranslator $translator
     */
    public function __construct($rukbatBitly) {
        $this->rukbatBitly = $rukbatBitly;

    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('bilty', array($this, 'bilty'))
        );
    }

    public function bilty($link) {

        if($this->enabled){
            $data = $this->rukbatBitly->bitly_v3_shorten($link);
            return $data['url'];
        }

        return $link;

    }

    public function getName() {
        return 'bilty_extension';
    }

}
