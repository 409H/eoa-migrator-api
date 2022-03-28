<?php

namespace App\Utils;

class NetworkMapping {

    public function isSupportedNetworkId(int $id): bool {
        $mapping = config('network_mapping');
        $mappedIds = array_column($mapping, "id");

        return in_array($id, $mappedIds);
    }

    public function getNameFromId(int $id): string {
        if(!$this->isSupportedNetworkId($id)) {
            throw new \Exception("Unsupported network id: {$id}");
        }

        $mapping = config('network_mapping');
        foreach($mapping as $map) {
            if($map["id"] === $id) {
                return $map["name"];
            }
        }
    }

    public function formatNetworkName(string $name): string {
        $words = explode("_", strtolower($name));

        $ret = "";
        foreach($words as $word) {
            $ret .= ucfirst(trim($word));
        }

        return $ret;
    }

}