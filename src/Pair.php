<?php
namespace wrossmann\maidenhead;

class Pair {
    protected $lat, $lng;
    
    public function __construct($lat, $lng) {
        $this->lat = $lat;
        $this->lng = $lng;
    }
    
    public function __get($name) {
        return $this->$name;
    }
    
    public function __set($name, $value) {
        throw new Exception('Immutable');
    }
}
