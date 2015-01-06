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
        $this->data_type = static::getType($data);
    }
    
    public function getStart(){
        return $this->start;
    }
    
    public function setStart($start){
        if(isset($this->end)){
            if(static::getType($start) === static::getType($this->end)){
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
            if(static::getType($end) === static::getType($this->start)){
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
        $this->data_type = static::getType($data);
    }
    
    public function compareTo(self $a) {
        return self::compare($this, $a);
    }
    
    /**
     * Gets the type of the var
     * (necessary since PHP is loosely typed)
     * @param type $var The variable to find the type
     * @param bool $return_class If true returns the class of an object.
     * @return string
     */
    protected static function getType($var, $return_class = false){
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
}

