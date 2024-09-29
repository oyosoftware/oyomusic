<?php

class oyoCountryList {

    private $countries = [];
    private $countryCodes = [];
    private $oyoCountryListLocation = "";

    /**
        Construct a country list for the specified language.
        @param {string} $languageCode The language for which the country list is constructed.
    */
    public function __construct($languageCode = "en") {
        $root = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
        if (is_null($root)) {
            $root = filter_input(INPUT_ENV, 'DOCUMENT_ROOT');
        }
        $root = realpath($root);
        $root = str_replace("\\", "/", $root);
        $dir = __DIR__;
        $dir = realpath($dir);
        $dir = str_replace("\\", "/", $dir);
        $this->oyoCountryListLocation = str_replace($root, "", $dir);

        $filename = __DIR__ . "/langs/" . $languageCode . ".json";
        $file = fopen($filename, "r");
        $json = fread($file, filesize($filename));
        $data = json_decode($json, true);
        $this->countries = $data["countries"];
        fclose($file);
        foreach ($this->countries as $countryCode => $country) {
            if (!is_array($country)) {
                $this->setCountryCode($country, $countryCode);
            } else {
                foreach ($country as $country) {
                    $this->setCountryCode($country, $countryCode);
                }
            }
        }
    }

    /**
        Set country code for the specified country.
        @param {string} $country The country for which the country code is set.
        @param {string} $countryCode The country code which is set.
    */
    private function setCountryCode($country, $countryCode) {
        if (array_key_exists("$country", $this->countryCodes)) {
            if (!is_array($this->countryCodes[$country])) {
                $this->countryCodes[$country] = [$this->countryCodes[$country]];
            }
            array_push($this->countryCodes[$country], $countryCode);
        } else {
            $this->countryCodes[$country] = $countryCode;
        }
    }

    /**
        Get country for the specified country code.
        @param {string} $countryCode The country code for which the country is retrieved.
        @param {int} $index The index of the country if several names are found.
        @return {string} The country.
     *
    */
    public function getCountry($countryCode, $index = 0) {
        $countryCode = strtoupper($countryCode);
        $country = $this->countries[$countryCode];
        if (is_array($country)) {
            if ($index > sizeof($country) - 1) {
                $index = 0;
            }
            $country = $country[$index];
        }
        if (is_null($country)) {
            $country = "Unknown";
        }
        return $country;
    }

    /**
        Get country code for the specified country.
        @param {string} $country The country for which the country code is retrieved.
        @param {int} $index The index of the country code if several codes are found.
        @return {string} The country code.
    */
    public function getCountryCode($country, $index = 0) {
        $countryCode = $this->countryCodes[$country];
        if (is_array($countryCode)) {
            if ($index > sizeof($countryCode) - 1) {
                $index = 0;
            }
            $countryCode = $countryCode[$index];
        }
        if (is_null($countryCode)) {
            $countryCode = "XX";
        }
        return $countryCode;
    }

    /**
        Get country flag element for the specified country code.
        @param {string} $countryCode The country code for which the country flag is retrieved.
     *  @param {number} width The width of the flag element.
        @return {string} The flag element.
    */
    public function getCountryFlag($countryCode, $width = "100px") {
        $dom = new DOMDocument();
        $img = $dom->createElement("img");
        $src = $this->oyoCountryListLocation . "/flags/" . strtolower($countryCode) . ".svg";
        $img->setAttribute('src', $src);
        $img->setAttribute('width', $width);
        $dom->appendChild($img);
        $flag = trim($dom->saveHTML());
        return $flag;
    }

    /**
        Translate a country to another language using another country list.
        @param {string} $country The country which has to be translated.
        @param {string} $transCountryList The other country list to which the country has to be translated.
        @param {string} $index The index of the country if several names are found.
        @return {string} The translated country.
    */
    public function translateCountry($country, $transCountryList, $index = 0) {
        $countryCode = $this->getCountryCode($country);
        $country = $transCountryList->getCountry($countryCode, $index);
        return $country;
    }

    /**
        Get the full countries list.
        @return {array} All the countries.
    */
    public function getCountries() {
        return $this->countries;
    }

    /**
        Get the full country codes list.
        @return {array} All the country codes.
    */
    public function getCountryCodes() {
        return $this->countryCodes;
    }

}
