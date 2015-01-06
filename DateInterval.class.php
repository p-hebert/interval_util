<?php
require_once 'Interval.class.php';
require_once 'Comparable.interface.php';
class Date_Interval extends Interval{
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
        super($start, $end, $data);
    }
    
    public function setStart($date){
        $datetime = self::toDateTime($date);
        if(isset($this->end)){
            if($datetime !== false && $this->end > $datetime){
                $this->start = $datetime;
                return true;
            }else{
                return false;
            }
        }else{
            if($datetime !== false){
                $this->start = $datetime;
                return true;
            }else{
                return false;
            }
        }
    }
    
    public function setEnd($date){
        $datetime = self::toDateTime($date);
        if(isset($this->start)){
            if($datetime !== false && $this->start < $datetime){
                $this->end = $datetime;
                return true;
            }else{
                return false;
            }
        }else{
            if($datetime !== false){
                $this->end = $datetime;
                return true;
            }else{
                return false;
            }
        }
    }
    
    public function getIntervalLength(){
        return date_diff($this->start, $this->end);
    }
    
    private static function toDateTime($date){
        $type = self::isADate($date);
        if($type === false){
            return false;
        }
        switch($type){
            case 'string':
                return new DateTime('@' .  (string) strtotime($date));
            case 'DateTime':
                return $date;
            case 'timestamp':
                return new DateTime('@' .  (string) $date);
            default:
                return false;
        }
    }
    
    private static function isADate($date){
        $date_type = self::getType($date);
        if($date_type === ''){
            return false;
        }elseif($date_type === 'string'){
            $time = strtotime($date);
            return $time !== false ? 'string' : false;
        }elseif($date_type === 'object'){
            return $date instanceof DateTime ? 'DateTime' : false;
        }elseif($date_type === 'integer'){
            return ((string) (int) $date === $date) ? 'timestamp' : false; 
        }    
    }
}