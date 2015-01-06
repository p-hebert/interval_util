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
    public static function orderIntervalSet($interval_set, $are_arrays = false){
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
     * Returns the union of consecutive or overlapping intervals.
     * @param string date [][2] $intervals
     * @return string date [][2] $unioned
     */
    public static function unionIntervals($interval_set1, $interval_set2, $first_is_sorted, $second_is_sorted, $are_arrays = false){          
        //Dealing with union of empty sets
        if(!count($interval_set1) && !count($interval_set2)){
            return [];
        }elseif(!count($interval_set1)){
            return $interval_set2;
        }elseif(!count($interval_set2)){
            return $interval_set1;
        }
        
        //Sorting interval sets
        if(!$first_is_sorted){
            $ordered_set1 = self::orderIntervalSet($interval_set1, $are_arrays);
        }else{
            $ordered_set1 = $interval_set1;
        }
        
        if(!$second_is_sorted){
            $ordered_set2 = self::orderIntervalSet($interval_set2, $are_arrays);
        }else{
            $ordered_set2 = $interval_set2;
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


    public static function intersectIntervals($masters, $master_is_sorted, $slaves, $slave_is_sorted){
        //Intersection of empty sets
        if(!count($masters)){ 
            return [];
        }elseif(!count($slaves)){
            return []; 
        }

        if(!$master_is_sorted){
            $masters = self::orderIntervalSet($masters);
        }
        if(!$slave_is_sorted){
            $slaves = self::orderIntervalSet($slaves);
        }
        $intersect = [];
        foreach($masters as $master){
            foreach($slaves as $slave){
                $maxStart = $master[0];
                $minEnd = $master[1];
                if($master[0] >= $slave[1] || $master[1] <= $slave[0]){
                    //then no intersection\
                    continue;
                }elseif($master[0] > $slave[0] && $master[1] < $slave[1]){
                    //then intersection is [ $master[0] , $master[1] ]
                    //don't do anything
                }else{
                    if($master[0] < $slave[0]){
                        if($master[1] < $slave[1]){
                            $maxStart = $slave[0];
                        }else{
                            $maxStart = $slave[0];
                            $minEnd = $slave[1];
                        }
                    }elseif($master[1] > $slave[1]){
                        $minEnd = $slave[1];
                    }
                }
                $intersect[] = [$maxStart, $minEnd]; 
                if($maxStart == $master[0] && $minEnd == $master[1]){
                    break;
                }
            } 
        }
        $unioned = self::unionIntervals($intersect, false);
        return $unioned;
    }
}