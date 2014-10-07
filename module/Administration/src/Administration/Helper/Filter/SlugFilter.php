<?php
namespace Administration\Helper\Filter;

use Zend\Filter\FilterInterface;

class SlugFilter implements FilterInterface
{
    public function filter($value)
    {
        $valueFiltered = $this->convertToEnglishChars($value);
        $valueFiltered = $this->strip($valueFiltered);
        return $valueFiltered;
    }

    private function strip($string)
    {
        $string  = strtolower($string);
        $pattern = "([^[:alnum:]])";
        $string  = preg_replace('/((&#39))/', '-', $string);
        $anchor  = preg_replace($pattern, '-', $string);
        $pattern = "([[:space:]]|[[:blank:]])";
        $anchor  = preg_replace($pattern, '-', $anchor);
        return $anchor;
    }

    /**
     * Returns a english representation of a given string.
     */
    private function convertToEnglishChars($string)
    {
        $string = preg_replace('/ά/', 'α', $string);
        $string = preg_replace('/έ/', 'ε', $string);
        $string = preg_replace('/ή/', 'η', $string);
        $string = preg_replace('/ί/', 'ι', $string);
        $string = preg_replace('/ό/', 'ο', $string);
        $string = preg_replace('/ύ/', 'υ', $string);
        $string = preg_replace('/ώ/', 'ω', $string);

        $m = array( '/ου/','/ού/','/ευ/','/εύ/','/αυ/','/αύ/',
            '/α/','/β/','/γ/','/δ/','/ε/','/ζ/','/η/','/θ/','/ι/','/κ/','/λ/','/μ/','/ν/',
            '/ξ/','/ο/','/π/','/ρ/','/σ/','/τ/','/υ/','/φ/','/χ/','/ψ/','/ω/',
            '/ς/',
            '/ά/','/έ/','/ό/','/ί/','/ύ/','/ώ/','/ή/','/ϊ/','/ϋ/','/ΐ/','/ΰ/',
            '/ʼ/','/Ό/','/Ί/','/Έ/','/Ύ/','/Ώ/','/Ή/','/Ϊ/','/Ϋ/',
            '/Α/','/Β/','/Γ/','/Δ/','/Ε/','/Ζ/','/Η/','/Θ/','/Ι/','/Κ/','/Λ/','/Μ/','/Ν/',
            '/Ξ/','/Ο/','/Π/','/Ρ/','/Σ/','/Τ/','/Υ/','/Φ/','/Χ/','/Ψ/','/Ω/'
        );

        $r = array( 'ou','ou','ef','ef','af','af',
            'a','b','g','d','e','z','i','th','i','k','l','m','n',
            'ks','o','p','r','s','t','i','f','h','ps','o',
            's',
            'a','e','o','i','i','o','i','i','i','i','i',
            'a','o','i','e','i','o','i','i','i',
            'A','B','G','D','E','Z','I','Th','I','K','L','M','N',
            'Ks','O','P','R','S','T','I','F','H','Ps','O'
        );

        return preg_replace($m,$r,$string);
    }
}