<?php

namespace App\Service;

class HabitatsoftXmlService
{
    private $staticUrl = "";
    private $fileName = "4385_test.xml";

    public function __construct(string $staticAssetsUrl)
    {
        $this->staticUrl = $staticAssetsUrl;
    }

    public function getSituations()
    {
        $situations = [];
        $inmuebles = $this->loadXml();
        foreach ($inmuebles->Inmueble as $inmueble) {
            if (!array_key_exists($inmueble->Situacion1->__toString(), $situations)) {
                $situations[$inmueble->Situacion1->__toString()] = [];
            }

            if (empty($inmueble->Situacion2->__toString())) continue;

            if (!in_array($inmueble->Situacion2->__toString(), $situations[$inmueble->Situacion1->__toString()])) {
                $situations[$inmueble->Situacion1->__toString()][] = $inmueble->Situacion2->__toString();
            }
        }
        return $situations;
    }

    public function getLocations()
    {
        $locations = [];
        $inmuebles = $this->loadXml();
        foreach ($inmuebles->Inmueble as $inmueble) {
            if (!array_key_exists($inmueble->NombreProvincia->__toString(), $locations)) {
                $locations[$inmueble->NombreProvincia->__toString()] = [];
            }

            if (empty($inmueble->NombreMunicipio->__toString())) continue;

            if (!isset($locations[$inmueble->NombreProvincia->__toString()][$inmueble->NombreMunicipio->__toString()])) {
                $locations[$inmueble->NombreProvincia->__toString()][$inmueble->NombreMunicipio->__toString()] = [];
            }

            if (empty($inmueble->NombreZona->__toString())) continue;

            if (!in_array($inmueble->NombreZona->__toString(), $locations[$inmueble->NombreProvincia->__toString()][$inmueble->NombreMunicipio->__toString()])) {
                $locations[$inmueble->NombreProvincia->__toString()][$inmueble->NombreMunicipio->__toString()][] = $inmueble->NombreZona->__toString();
            }
        }
        return $locations;
    }

    private function loadXml()
    {
        $file = $this->staticUrl . $this->fileName;
        $fileContent = file_get_contents($file);
        $fileContent = $this->cleanXml($fileContent);
        $xml = simplexml_load_string($fileContent);
        return $xml->Inmuebles;
    }

    private function cleanXml($content)
    {
        $ret = "";
        $current = 0;
        if (empty($content)) {
            return $ret;
        }

        $length = strlen($content);
        for ($i = 0; $i < $length; $i++) {
            $current = ord($content[$i]);
            if (($current == 0x9) ||
                ($current == 0xA) ||
                ($current == 0xD) ||
                (($current >= 0x20) && ($current <= 0xD7FF)) ||
                (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                (($current >= 0x10000) && ($current <= 0x10FFFF))
            ) {
                $ret .= chr($current);
            } else {
                $ret .= " ";
            }
        }
        return $ret;
    }
}
