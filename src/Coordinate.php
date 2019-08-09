<?php
namespace wrossmann\maidenhead;

class Coordinate {
    const ENC_FIELD        = 0;
    const ENC_SQUARE       = 1;
    const ENC_SUBSQUARE    = 2;
    
    const ENC_MAX = [
        self::ENC_FIELD => 18,
        self::ENC_SQUARE => 10,
        self::ENC_SUBSQUARE => 24
    ];
    
    const ENC_CHARS = [
        self::ENC_FIELD => 'abcdefghijklmonpqr',
        self::ENC_SQUARE => '0123456789',
        self::ENC_SUBSQUARE => 'abcdefghijklmonpqrstuvwx'
    ];
    
    // The spec only covers the first 4 pairs, the 4th pair "extended subsquare" uses the same encoding as "square"
    // Non-standard extensions of the spec are to be implemented as extensions of this class.
    protected static $encode_order = [ self::ENC_FIELD, self::ENC_SQUARE, self::ENC_SUBSQUARE, self::ENC_SQUARE ];
    
    protected $pairs = [];
    protected $precision;
    
    public function __construct($pairs) {
        foreach($pairs as $pair) {
            $this->addPair($pair);
        }
        $this->precision = count($pairs);
    }
    
    public function transform($offsets) {
        $offset_count = count($offsets);
        $pair_count = $this->precision;
        $encoding_count = count(static::$encode_order);
        
        if( $offset_count > $pair_count ) {
            throw new \Exception('Number of offsets greater than the number of coordinate pairs');
        }
        
        $carry = [0, 0];
        $new_pairs = [];
        
        // process the smallest offset first so that we don't have to specify a full array all the time
        // and also so that carries can be efficiently handled
        for( $o=1,$c=$pair_count; $o<=$c; $o++ ) {
            $offset_index = $offset_count - $o;
            $pair_index   = $this->precision - $o;
            
            $cur_pair = $this->pairs[$pair_index];
            if( $offset_index < 0 ) {
                $cur_offset = $carry;
            } else {
                $cur_offset = $offsets[$offset_index];
                
                // apply carry
                $cur_offset = [
                    $cur_offset[0] + $carry[0],
                    $cur_offset[1] + $carry[1]
                ];
            }
            
            $new_lat = $this->rollover($cur_pair->lat + $cur_offset[0], static::ENC_MAX[static::$encode_order[$pair_index]]);
            $new_lng = $this->rollover($cur_pair->lng + $cur_offset[1], static::ENC_MAX[static::$encode_order[$pair_index]]);
            
            $carry = [ $new_lat[1], $new_lng[1] ];
            $new_pair = new Pair( $new_lat[0], $new_lng[0] );
            array_unshift($new_pairs, $new_pair);
        }
        return new static($new_pairs);
    }
    
    public function toString() {
        $output = '';
        for( $i=0; $i<$this->precision; $i++ ) {
            $output .= $this->encodeAs($this->pairs[$i]->lat, static::$encode_order[$i]);
            $output .= $this->encodeAs($this->pairs[$i]->lng, static::$encode_order[$i]);
        }
        return $output;
    }
    
    protected function rollover($value, $base) {
        if( $value < 0 ) {
            $result = ($value % $base) ? $base + ($value % $base) : 0;
            $carry = (int)ceil(abs($value) / $base) * -1;
        } else if( $value >= $base ) {
            $result = $value % $base;
            $carry = (int)floor($value / $base);
        } else {
            $result = $value;
            $carry = 0;
        }
        
        return [ $result, $carry ];
    }
    
    protected function addPair(Pair $pair) {
        $this->pairs[] = $pair;
    }
    
    public static function fromString($input, $pad=true) {
        $pairs = [];
        $raw_pairs = array_map('str_split', str_split($input, 2));
        for( $i=0,$c=count($raw_pairs); $i<$c; $i++ ) {
            if( ! isset(static::$encode_order[$i]) ) {
                throw new \Exception("No decoding specified for pair index $i");
            }
            $encoding = static::$encode_order[$i];
            $pairs[] = new Pair(
                self::decodeAs($raw_pairs[$i][0], $encoding),
                self::decodeAs($raw_pairs[$i][1], $encoding)
            );
        }
        if( $pad ) {
            for( $c=count(static::$encode_order); $i<$c; $i++ )
            $pairs[] = new Pair(0,0);
        }
        return new static($pairs);
    }
    
    public static function decodeAs($str, $encoding) {
        $value = strpos(self::ENC_CHARS[$encoding], strtolower($str));
        if( $value === false ) {
            throw new \Exception("Invalid character $str for encoding $encoding");
        }
        return $value;
    }
    
    public static function encodeAs($int, $encoding) {
        return self::ENC_CHARS[$encoding][$int];
    }
}

