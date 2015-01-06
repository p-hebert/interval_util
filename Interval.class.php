<?php

class Interval implements Comparable{
    private $start;
    private $end;
    private $data;
    private $data_type;
    
    /**
     * Constructor
     * @param timestamp/string/DateTime Object $start
     * @param timestamp/string/DateTime Object $end
     * @param mixed $data If the programmer needs to associate any data with the interval object.
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
    
    public function getStart(){
        return $this->start;
    }
    
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
    
    public function getEnd(){
        return $this->end;
    }
    
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
    
    public function getData(){
        return $this->data;
    }
    
    public function getDataType(){
        return $this->data_type;
    }
    
    public function setData($data){
        $this->data = $data;
        $this->data_type = self::getType($data);
    }
    
    public function compareTo(self $a) {
        return self::compare($this, $a);
    }
    
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
    
    public final static function intervalToArray(Interval $interval){
        $array = [];
        $array[] = $interval->start;
        $array[] = $interval->end;
        if(isset($interval->data)){
            $array[] = $interval->data;
        }
    }
    
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
    
    public static function compareArrIntervals($a, $b){
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
}

