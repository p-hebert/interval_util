<?php
 include_once 'Interval.class.php';

/**
 * Library of set operations for Interval sets
 * The $are_arrays parameter specifies whether the programmer uses arrays of
 * [start, end, data = null] or arrays of objects instanciating Interval.class.php as parameters.
 * All of these methods will provoke loss of any $data field that may be associated 
 * to the Interval objects. 
 * You are most welcome to modify this for your own purposes and add your
 * own logic rules such as using the data field as a second dimension for your
 * set operations.
 */
class IntervalUtils{
    
    /**
     * Returns the extremums of a set of Intervals
     * @param interval array\array of Interval $interval_set
     * @param boolean $are_arrays
     * @return single interval array\Interval
     */
    public static function getExtremums($interval_set, $are_arrays = false){
        if(!count($interval_set)){
            return [];
        }
        $ordered_set = self::orderIntervalSet($interval_set, $are_arrays);
        if(!$are_arrays){
            $min = $interval_set[0]->getStart();
            $max = $interval_set[count($interval_set) - 1]->getEnd();
            return new Interval($min, $max);
        }else{
            $min = $interval_set[0][0];
            $max = $interval_set[count($interval_set) - 1][1];
            return [$min, $max];
        }
    }

    /**
     * Sorts an array of intervals.
     * Based on PHP usort (Implemented as a quicksort in C).
     * @param interval array\Interval.class array $interval_set
     * @param boolean $are_arrays
     * @return interval array\Interval.class array
     */
    public final static function orderIntervalSet($interval_set, $are_arrays = false){
        if(!count($interval_set)){
            return [];
        }
        //To optimize on average the quicksort time (usort() is a quicksort)
        shuffle($interval_set);
        
        if(!$are_arrays){
            usort($interval_set, Interval::compare($a, $b));
        }else{
            usort($interval_set, Interval::compareArrIntervals($a, $b));
        }
        return $interval_set;
    }
    
    /**
     * Union of a set over itself. Overlapping intervals are merged together.
     * @param interval array\Interval.class array $interval_set
     * @param boolean $is_ordered
     * @param boolean $are_arrays
     * @return interval array\Interval.class array
     */
    public static function reflexiveUnion($interval_set, $is_ordered, $are_arrays = false){
        //Dealing with union of empty sets
        if(!count($interval_set)){
            return [];
        }
        
        //Sorting interval sets
        if(!$is_ordered){
            $ordered_set = self::orderIntervalSet($interval_set, $are_arrays);
        }else{
            $ordered_set = $interval_set;
        }
        
        $union_set = [$ordered_set[0]];
        $index = 0;
        
        //Checks if $ordered_set[$i] overlaps with $union_set[$index]
        //If yes, extends the length of the $union_set[$index]
        //If not, create a new entry in $union_set.
        for($i = 0 ; $i < count($ordered_set) ; $i++){
            if(!$are_arrays){
                if($union_set[$index]->getEnd() < $ordered_set[$i]->getStart()){
                    $union_set[] = $ordered_set[$i];
                    $index++;
                }elseif($union_set[$index]->getEnd() < $ordered_set[$i]->getEnd()){
                    $union_set[$index]->setEnd($ordered_set[$i]->getEnd());
                }
            }else{
                if($union_set[$index][1] < $ordered_set[$i][0]){
                    $union_set[] = $ordered_set[$i];
                    $index++;
                }elseif($union_set[$index][1] < $ordered_set[$i][1]){
                    $union_set[$index][1] = $ordered_set[$i][1];
                }
            }
        }
        return $union_set;
    }

   /**
    * Returns the union of two sets
    * @param interval array\Interval.class array $master
    * @param interval array\Interval.class array $slave
    * @param boolean $are_arrays
    * @return interval array\Interval.class array $unioned_set
    */
    public static function union($master, $slave, $are_arrays = false){          
        //Dealing with union of empty sets
        if(!count($master) && !count($slave)){
            return [];
        }elseif(!count($master)){
            return $slave;
        }elseif(!count($slave)){
            return $master;
        }
        
        foreach($slave as $interval){
            $master[] = $interval;
        }
        
        return self::reflexiveUnion($master, false);   
    }   

    /**
     * Returns the reflexively unioned intersection of the $master and $slave sets.
     * Method will sort $master/$slave if the parameters *_is_sorted is false.
     * @param interval array\Interval.class array $master
     * @param boolean $master_is_sorted
     * @param interval array\Interval.class array $slave
     * @param boolean $slave_is_sorted
     * @param boolean $are_arrays
     * @return interval array\Interval.class array $union_set
     */
    public static function intersect($master, $master_is_sorted, $slave, $slave_is_sorted, $are_arrays = false){
        //Intersection of empty sets
        if(!count($master)){ 
            return [];
        }elseif(!count($slave)){
            return []; 
        }

        if(!$master_is_sorted){
            $master = self::orderIntervalSet($master, $are_arrays);
        }
        if(!$slave_is_sorted){
            $slave = self::orderIntervalSet($slave, $are_arrays);
        }
        $intersect_set = [];
        foreach($master as $m){
            foreach($slave as $s){
                if(!$are_arrays){
                    $maxStart = $m->getStart();
                    $minEnd = $m->getEnd();
                    if($m->getStart() >= $s->getEnd() || $m->getEnd() <= $s->getStart()){
                        //then no intersection
                        continue;
                  //}elseif($m->getStart() > $->getStart() && $m->getEnd() < $s->getEnd()){
                        //then intersection is [ $master->getStart() , $master->getEnd() ]
                        //don't do anything
                    }else{
                        if($m->getStart() < $s->getStart()){
                            if($m->getEnd() < $s->getEnd()){
                                $maxStart = $s->getStart();
                            }else{
                                $maxStart = $s->getStart();
                                $minEnd = $s->getEnd();
                            }
                        }elseif($m->getEnd() > $s->getEnd()){
                            $minEnd = $s->getEnd();
                        }
                    }
                    $intersect_set[] = new Interval($maxStart, $minEnd);
                    //Then the m interval has a complete match in the slave intervals. No need to check for more intersection.
                    if($maxStart == $m->getStart() && $minEnd == $m->getEnd()){
                        break;
                    }
                }else{
                    $maxStart = $m[0];
                    $minEnd = $m[1];
                    if($m[0] >= $s[1] || $m[1] <= $s[0]){
                        //then no intersection\
                        continue;
                  //}elseif($m[0] > $[0] && $m[1] < $s[1]){
                        //then intersection is [ $master[0] , $master[1] ]
                        //don't do anything
                    }else{
                        if($m[0] < $s[0]){
                            if($m[1] < $s[1]){
                                $maxStart = $s[0];
                            }else{
                                $maxStart = $s[0];
                                $minEnd = $s[1];
                            }
                        }elseif($m[1] > $s[1]){
                            $minEnd = $s[1];
                        }
                    }
                    $intersect_set[] = [$maxStart, $minEnd];
                    //Then the m interval has a complete match in the slave intervals. No need to check for more intersection.
                    if($maxStart == $m[0] && $minEnd == $m[1]){
                        break;
                    }
                }
            } 
        }
        
        //cleaning up the set
        $union_set = self::reflexiveUnion($intersect_set, false, $are_arrays);
        return $union_set;
    }
    
    /**
     * Returns {e : e element of $master && e ! element of $slave}
     * $master and $slave need to be ordered and unioned before difference is done.
     * Runs in O(n^4) as it relies on differenceOnSingleInterval() which runs in O(n^3).
     * @param interval array\Interval.class array $master
     * @param boolean $m_ordered
     * @param boolean $m_unioned
     * @param interval array\Interval.class array $slave
     * @param boolean $s_ordered
     * @param boolean $s_unioned
     * @param boolean $are_arrays
     * @return interval array\Interval.class array $difference
     */
    public static function difference($master, $m_ordered, $m_unioned, $slave, $s_ordered, $s_unioned, $are_arrays = false){
        //Difference of empty sets
        if(!count($master)){ 
            return [];
        }elseif(!count($slave)){
            return $master; 
        }

        if(!$m_ordered){
            $master = self::orderIntervalSet($master, $are_arrays);
        }
        if(!$m_unioned){
            $master = self::reflexiveUnion($master, true, $are_arrays);
        }
        if(!$s_ordered){
            $slave = self::orderIntervalSet($slave, $are_arrays);
        }
        if(!$s_unioned){
            $slave = self::reflexiveUnion($slave, true, $are_arrays);
        }
        
        $difference = [];
        //Does the difference on each $master interval $m one at a time.
        foreach($master as $m){
            $diff = self::differenceOnSingleInterval($m, $slave, $are_arrays);
            if($diff !== false){
                foreach($diff as $interval){
                    $difference[] = $interval;
                }
            }   
        }
        
        return $difference;
    }
    
    /**
     * Returns the difference of a single interval and a set of intervals.
     * Returns {e : e element of $master && e ! element of $slave}
     * Runs in O(n^3) since intersects all the difference obtained in the first loop
     * and intersect() runs already in O(n^2). 
     * @param interval array\Interval.class array $master
     * @param interval array\Interval.class array $slave
     * @param boolean $are_arrays
     * @return boolean\single interval array\Interval.class object
     */
    private static function differenceOnSingleInterval($master, $slave, $are_arrays){
        $diff = [];
        
        //Adds 1 to 2 intervals at the index $i depending on how the $slave[$i] overlaps with the $master interval.
        for($i = 0; $i < count($slave); $i++){
            if(!$are_arrays){
                $minStart = $master->getStart();
                $maxEnd = $master->getEnd();
                if($slave[$i]->getStart() > $minStart){
                    if($slave[$i]->getEnd() < $maxEnd){
                        $diff[$i][] = new Interval($minStart, $slave[$i]->getStart());
                        $diff[$i][] = new Interval($slave[$i]->getEnd(), $maxEnd);
                    }else{
                        $diff[$i][] = new Interval($minStart, $slave[$i]->getStart());
                    }
                }else{
                    if($slave[$i]->getEnd() < $maxEnd){
                        $diff[$i][] = new Interval($slave[$i]->getEnd(), $maxEnd);
                    }else{
                        //$s covers the whole of $m, nothing is left.
                        return false;
                    }
                }
            }else{
                $minStart = $master[0];
                $maxEnd = $master[1];
                if($slave[$i][0] > $minStart){
                    if($slave[$i][1] < $maxEnd){
                        $diff[$i][] = [$minStart, $slave[$i][1]];
                        $diff[$i][] = [$slave[$i][1], $maxEnd];
                    }else{
                        $diff[$i][] = [$minStart, $slave[$i][0]];
                    }
                }else{
                    if($slave[$i][1] < $maxEnd){
                        $diff[$i][] = [$slave[$i][1], $maxEnd];
                    }else{
                        //$s covers the whole of $m, nothing is left.
                        return false;
                    }
                }
            }
        }
        //Intersect all the intervals obtained in order to simulate the difference 
        //of the $slave intervals on the master interval.
        for($i = 0; $i < count($diff) ; $i++){
            //Take the previous index, intersect with current and save in current
            //allows to intersect multiple sets over the full iteration of the loop.
            //(commutativity of intersection)
            if(isset($diff[$i - 1])){
                $diff[$i] = static::intersect($diff[$i], false, $diff[$i-1], false, $are_arrays);
                if(empty($diff[$i])){
                    return false;
                }
            }
        }
        return $diff[count($diff) - 1];
    }
    
    /**
     * Returns {e : e is element of $master xor $slave}
     * @param interval array\Interval.class array $master
     * @param boolean $master_is_ordered
     * @param interval array\Interval.class array $slave
     * @param boolean $slave_is_ordered
     * @param boolean $are_arrays
     * @return interval array\Interval.class array $difference
     */
    public static function symmetricDifference($master, $master_is_ordered, $slave, $slave_is_ordered, $are_arrays = false){
        $union = static::union($master, $slave, $are_arrays);
        $intersection = static::intersection($master, $master_is_ordered, $slave, $slave_is_ordered, $are_arrays);
        return static::difference($union, $intersection);
    }
}