<?php

namespace App\Service;

use App\Entity\Property;

class HabitatsoftXmlService
{
    private $staticUrl = "";
    private $fileName = "4385_test.xml";
    private const ACCOUNT_ID = "4385";

    public function __construct(string $staticAssetsUrl)
    {
        $this->staticUrl = $staticAssetsUrl;
    }

    public function getSituations(): array
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

    public function getLocations(string $type): array
    {
        $locations = [];
        $inmuebles = $this->loadXml();
        foreach ($inmuebles->Inmueble as $inmueble) {
            if ($this->getType($inmueble->LanguageData->Language->txt_NombreTipoInmueble->__toString()) !== $type) continue;

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

    public function getCategories(): array
    {
        $categories = [];
        $inmuebles = $this->loadXml();
        foreach ($inmuebles->Inmueble as $inmueble) {
            if (in_array($inmueble->LanguageData->Language[0]->txt_Otros->__toString(), $categories)) continue;

            $categories[] = $inmueble->LanguageData->Language[0]->txt_Otros->__toString();
        }
        return $categories;
    }

    public function getPropertyReferences(): array
    {
        $references = [];
        $inmuebles = $this->loadXml();
        foreach ($inmuebles->Inmueble as $inmueble) {
            $references[] = $inmueble->NumeroExpediente->__toString();
        }
        return $references;
    }

    public function getProperties(string $type): array
    {
        $properties = [];
        $inmuebles = $this->loadXml();
        foreach ($inmuebles->Inmueble as $inmueble) {
            if ($this->getType($inmueble->LanguageData->Language->txt_NombreTipoInmueble->__toString()) !== $type) continue;
            
            $property = [
                "address" => $inmueble->TipoCalle->__toString() . " " . $inmueble->NombreCalleInmueble->__toString() . " " . $inmueble->TipoNumero->__toString() . " " . $inmueble->NumeroCalleInmueble->__toString(),
                "postalCode" => $inmueble->CodigoPostal->__toString(),
                "province" => $inmueble->NombreProvincia->__toString(),
                "situation1" => $inmueble->Situacion1->__toString(),
                "situation2" => $inmueble->Situacion2->__toString(),
                "town" => $inmueble->NombreMunicipio->__toString(),
                "zone" => $inmueble->NombreZona->__toString(),
                "latitude" => $inmueble->Latitud->__toString(),
                "longitude" => $inmueble->Longitud->__toString(),
                "featured" => ($inmueble->Destacado->__toString() === "1"),
                "image" => null,
                "gallery" => [],
                "habitatsoftId" => $this->cleanId($inmueble->IdInmueble->__toString()),
                "operation" => $this->getOperation($inmueble),
                "area" => (float) $inmueble->SuperficieTotal->__toString(),
                "price" => (float)$inmueble->Precio1Euros->__toString(),
                "reference" => $inmueble->NumeroExpediente->__toString(),
                "services" => [],
                "translations" => [
                    "es" => [],
                    "ct" => [],
                    "en" => [],
                    "fr" => [],
                ]
            ];
            foreach ($property['translations'] as $locale => $value) {
                $localisedData = $inmueble->LanguageData->xpath('Language[@idlanguage="' . $locale . '"]');
                $property['translations'][$locale] = [
                    "title" => $localisedData[0]->txt_Cabecera1->__toString(),
                    "body" => str_replace('  ', "\n\n", $localisedData[0]->txt_DetalleAlternativo->__toString()),
                ];
            }

            if ($property["price"] !== 0 && $property["area"] !== 0) {
                $property["priceSqm"] = round($property["price"] / $property["area"], 2);
            } else {
                $property["priceSqm"] = 0;
            }

            if ($inmueble->box_MuelleCarga != 0) {
                $property["services"][] = "Muelle";
            }
            if ($inmueble->box_Grua != 0) {
                $property["services"][] = "Punte Grúa";
            }
            if ($inmueble->box_SistemaAntiIncendios != 0) {
                $property["services"][] = "Antiincendios";
            }
            if ($inmueble->box_Oficina != 0) {
                $property["services"][] = "Oficina";
            }
            if ($inmueble->box_Estructura != 0) {
                $property["services"][] = "Finca Regia";
            }
            if ($inmueble->box_Cubierta != 1) {
                $property["services"][] = "Diáfana";
            }
            if ($inmueble->box_Divisiones != 0) {
                $property["services"][] = "Divisiones";
            }
            if ($inmueble->box_Escaparate != 0) {
                $property["services"][] = "Escaparate";
            }

            if (isset($inmueble->Medias->Fotos->Foto[0])) {
                $property["image"] = trim($inmueble->Medias->Fotos->Foto[0]->__toString());
                foreach ($inmueble->Medias->Fotos->Foto as $image) {
                    $property["gallery"][] = trim($image->__toString());
                }
            }

            $properties[] = $property;
        }
        
        return $properties;
    }

    private function getType(string $type): ?string
    {
        switch ($type) {
            case 'Nave Industrial':
            case 'Nave industrial':
            case 'Nave comercial':
            case 'Terreno industrial':
            case 'Terreno':
                return Property::TYPE_WAREHOUSE;
                break;

            case 'Oficina':
                return Property::TYPE_OFFICE;
                break;

            case 'Local':
                return Property::TYPE_LOCAL;
                break;

            case 'Casa':
            case 'Piso':
            case 'Loft':
                return Property::TYPE_RESIDENCE;
                break;
            default:
                // dump($type);
                break;
        }

        return null;
    }

    private function getOperation(\SimpleXMLElement $inmueble): string
    {
        $operation = "alquiler";
        if ($inmueble->LanguageData->Language->txt_NombreOperacion->__toString() == "en venta") {
            $operation = "venta";
        }
        return $operation;
    }

    private function loadXml(): \SimpleXMLElement
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

    private function cleanId($str)
    {
        return str_replace("." . self::ACCOUNT_ID, "", $str);
    }
}
