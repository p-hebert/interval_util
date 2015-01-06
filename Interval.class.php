<?php


/**
 * Interval class. Represent a continuous interval of sort, where the startpoint
 * and endpoints are of the same type and follow each other on a one dimensional
 * line.
 * Can include a supplementary data for some purpose, potentially sorting.
 */
class Interval implements Comparable{
    private $start;
    private $end;
    private $data;
    private $data_type;
    
    /**
     * Constructor
     * Must receive two parameter of the same type in increasing order.
     * @param T $start
     * @param T $end
     * @param type $data
     * @throws Exception
     */
    public function __construct($start, $end, $data = null){
        if(!$this->setStart($start)){
            throw new Exception('Invalid startpoint in ' . get_called_class() . ' constructor');
        }
        if(!$this->setEnd($end)){
            throw new Exception('Invalid endpoint in ' . get_called_class() . ' constructor');
        }
        $this->start = $start;
        $this->end = $end;
        $this->data = $data;
        $this->data_type = self::getType($data);
    }
    
    /**
     * Accessor of startpoint
     * @return type
     */
    public function getStart(){
        return $this->start;
    }
    
    /**
     * Mutator of startpoint
     * @param type $start
     * @return boolean
     */
    public function setStart($start){
        if(isset($this->end)){
            $getTypeStart = self::getType($start);
            if($getTypeStart === self::getType($this->end) 
               && ($getTypeStart !== '' || $getTypeStart !== 'unknown type')
               && $start < $this->end){
                $this->start = $start;
                return true;
            }else{
                return false;
            }
        }else{
            $this->start = $start;
            return true;
        }
    }
    
    /**
     * Accessor of endpoint
     * @return type
     */
    public function getEnd(){
        return $this->end;
    }
    
    /**
     * Mutator of endpoint
     * @param type $end
     * @return boolean
     */
    public function setEnd($end){
        if(isset($this->start)){
            $getTypeEnd = self::getType($end);
            if($getTypeEnd === self::getType($this->start)
               && ($getTypeEnd !== '' || $getTypeEnd !== 'unknown type')
               && $end > $this->start){
                $this->end = $end;
                return true;
            }else{
                return false;
            }
        }else{
            $this->end = $end;
            return true;
        }
    }
    
    /**
     * Accessor of data
     * @return type
     */
    public function getData(){
        return $this->data;
    }
    
    /**
     * Accessor of data type
     * @return string
     */
    public function getDataType(){
        return $this->data_type;
    }
    
    /**
     * Mutator of Data and its type
     * @param type $data
     */
    public function setData($data){
        $this->data = $data;
        $this->data_type = self::getType($data);
    }
    
    /**
     * Non-static version of compare(). Compares $this to another Interval object of same class.
     * Returns -1 ($this is before), 0 ($this is same) or 1 ($this is after)
     * @param self $a
     * @return int
     */
    public function compareTo(self $a) {
        return self::compare($this, $a);
    }
    
    //TYPING METHODS-------------------------------------------------------------------------------------------------------
    /**
     * Verifies if the variable is an Interval.
     * @param $var
     * @return boolean
     */
    public final static function isInterval($var){
        $type = self::getType($var);
        if($type === 'object' && $var instanceof Interval){
            return true;
        }elseif($type === 'array' && self::isIntervalToArray($var)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Returns an array representation of an Interval object supplied.
     * @param Interval $interval
     */
    public final static function intervalToArray(Interval $interval){
        $array = [];
        $array[] = $interval->start;
        $array[] = $interval->end;
        if(isset($interval->data)){
            $array[] = $interval->data;
        }
    }
    
    /**
     * Returns an Interval representation of a single interval array parameter
     * Returns false if array is not an interval.
     * @param single interval array $array
     * @return static\boolean
     */
    public static function arrayToInterval($array){
        if(self::isIntervalToArray($array)){
            if(count($array) == 3){
                return new static($array[0], $array[1], $array[2]); 
            }else{
                return new static($array[0], $array[1]);
            }   
        }else{
            return false;
        }
    }
    
    /**
     * Verifies if the variable is a single interval array
     * @param type $var
     * @return boolean
     */
    private final static function isIntervalToArray($var){
        return (count($var) < 4 && count($var) > 1) && (self::getType($var[0]) === self::getType($var[0]) 
                && (self::getType($var[0]) !== '' || self::getType($var[0]) !== 'unknown type'));
    }
    
    /**
     * Gets the type of the var
     * (necessary since PHP is loosely typed)
     * @param type $var The variable to find the type
     * @param bool $return_class If true returns the class of an object.
     * @return string
     */
    protected final static function getType($var, $return_class = false){
        $type = gettype($var);
        if($type === 'NULL'){
            return '';
        }elseif($type === 'object' && $return_class === true){
            return get_class($var);
        }else{
            return $type;
        }
    }
    
    //COMPARISON METHODS---------------------------------------------------------------------------------------------------
    /**
     * Compares two Intervals
     * Returns -1 ($a is before), 0 ($a is same) or 1 ($a is after)
     * @param self $a
     * @param self $b
     * @return int
     */
    public static function compare(self $a, self $b) {
        if($a->getStart() < $b->getStart()){
            return -1;
        }elseif($a->getStart() == $b->getStart()){
            if($a->getEnd() < $b->getEnd()){
                return -1;
            }elseif($a->getEnd() == $b->getEnd()){
                return 0;
            }else{
                return 1;
            }
        }else{
            return 1;
        }
    }
    
    /**
     * Compares two Intervals
     * Returns -1 ($a is before), 0 ($a is same) or 1 ($a is after)
     * Returns false if $a or $b are not single array intervals.
     * @param type $a
     * @param type $b
     * @return int
     */
    public static function compareArrIntervals($a, $b){
        if(!self::intervalToArray($a) || !self::isIntervalToArray($b)){
            return false;
        }
        if($a[0] < $b[0]){
            return -1;
        }elseif($a[0] == $b[0]){
            if($a[1] < $b[1]){
                return -1;
            }elseif($a[1] == $b[1]){
                return 0;
            }else{
                return 1;
            }
        }else{
            return 1;
        }
    }
    
    /**
     * Returns if two intervals are intersecting
     * @param single interval array\Interval.class object $a
     * @param single interval array\Interval.class object $b
     * @param boolean $are_arrays
     * @return boolean
     */
    public static function areIntersecting($a, $b , $are_arrays = false){
        if(!$are_arrays && ($a->getEnd() > $b->getStart() || $b->getEnd() > $a->getStart())){
            return true;
        }elseif($are_arrays && ($a[1] > $b[0] || $b[1] > $a[0])){
            return true;
        }else{
            return false;
        }
    }
}

