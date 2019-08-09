This is a very barbones library that can currently decode, transform, and re-encode Maidenhead Coordinates

## Usage

Basic usage as illustrated in `tests/test.php`

```
use wrossmann\maidenhead\Coordinate;

// extend to 5 pairs of precision, enforce uppercase coords
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
```

Output:

```
string(10) "JO22OI60JD"
string(10) "JO22OI60JE"
string(10) "JO22OI60JF"
string(10) "JO22OI60KD"
string(10) "JO22OI60KF"
string(10) "JO22OI60LD"
string(10) "JO22OI60LE"
string(10) "JO22OI60LF"
```
