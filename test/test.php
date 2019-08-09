<?php
require(__DIR__ . '/../vendor/autoload.php');
use wrossmann\maidenhead\Coordinate;

class ExtendedCoordinate extends Coordinate {
    protected static $encode_order = [ self::ENC_FIELD, self::ENC_SQUARE, self::ENC_SUBSQUARE, self::ENC_SQUARE, self::ENC_SUBSQUARE ];
    public function toString() {
        return strtoupper(parent::toString());
    }
}

$c = ExtendedCoordinate::fromString('JO22OI60KE');

// generate list of adjacent cells
foreach( [-1,0,1] as $x ) {
    foreach( [-1,0,1] as $y ) {
        if( $x == 0 && $y == 0 ) { continue; }
        var_dump($c->transform([[$x, $y]])->toString());
    }
}

