<?php
 include_once 'Interval.class.php';

/**
 * Interval Utilities, including sorting, union of one set, intersection of tw and max & min date 
 */
class IntervalUtils{
    
    /**
     * Creates the $interval variable to pass to getUsersHistory()
     * Finds the largest interval during which an user was under a certain reporter, using first date under and last date under
     * Disregards any gaps between the max and min where the user was not under the reporter, since it would otherwise
     * discard some important data when querying.
     * @param mixed[][][] $users_intervals The result from buildTimeIntervals() or any array of the form
     * [
     *  ...
     *   int index $user_id 
     *    [
     *     ...[
     *          int $reporter
     *          string date $start_date
     *          string date $end_date
     *        ]...
     *    ]...
     * ]
     * @return mixed[][] $g_intervals
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
     * Sorts an interval array, assuming dates are in the same format.
     * @param string dates [][] $intervals
     * @return string dates [][] $intervals
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
     * Returns the union of consecutive or overlapping intervals.
     * @param string date [][2] $intervals
     * @return string date [][2] $unioned
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

    public static function intersect($master, $master_is_sorted, $slave, $slave_is_sorted, $are_arrays = false){
        //Intersection of empty sets
        if(!count($master)){ 
            return [];
        }elseif(!count($slave)){
            return []; 
        }

        if(!$master_is_sorted){
            $master = self::orderIntervalSet($master);
        }
        if(!$slave_is_sorted){
            $slave = self::orderIntervalSet($slave);
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
    
    public static function difference($master, $slave){
        
    }
    
    public static function symmetricDifference($master, $master_is_ordered, $slave, $slave_is_ordered, $are_arrays = false){
        $union = static::union($master, $slave, $are_arrays);
        $intersection = static::intersection($master, $master_is_ordered, $slave, $slave_is_ordered, $are_arrays);
        return static::difference($union, $intersection);
    }
}