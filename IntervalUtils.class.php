<?php


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
    public static function getGlobalIntervals($users_intervals){
        $g_intervals = [];
        foreach($users_intervals as $key=>$date_intervals){
            $min = '3000-00-00';
            $max = '0000-00-00';
            foreach($date_intervals as $range){
                if($range[1] < $min){
                    $min = $range[1];
                }
                if($range[2] > $max){
                    $max = $range[2];
                }
            } 
            $g_intervals[] = [$key, $min, $max];
        }    
        return $g_intervals;
    }

    /**
     * Sorts an interval array, assuming dates are in the same format.
     * @param string dates [][] $intervals
     * @return string dates [][] $intervals
     */
    public static function orderIntervalList($intervals){
        //To optimize on average the quicksort time (usort() is a quicksort)
        shuffle($intervals);

        usort($intervals, function ($interval1, $interval2){
            if($interval1[0] < $interval2[0]){
                return -1;
            }elseif($interval1[0] == $interval2[0]){
                if($interval1[1] < $interval2[1]){
                    return -1;
                }elseif($interval1[1] == $interval2[1]){
                    return 0;
                }else{
                    return 1;
                }
            }else{
                return 1;
            }
        });

        return $intervals;
    }

    /**
     * Returns the union of consecutive or overlapping intervals.
     * @param string date [][2] $intervals
     * @return string date [][2] $unioned
     */
    public static function unionIntervals($intervals, $is_sorted){          
        if(!count($intervals)){
            return [];
        }
        if(!$is_sorted){
            $sorted = self::orderIntervalList($intervals);
        }else{
            $sorted = $intervals;
        }

        $unioned = [$sorted[0]];
        $index = 0;

        for($i = 0 ; $i < count($sorted) ; $i++){
            if($unioned[$index][1] < $sorted[$i][0]){
                $current = new DateTime($unioned[$index][1]);
                $current->add(new DateInterval('P1D'));
                $next = new DateTime($sorted[$i][0]);
                //checking for consecutive intervals
                if($current == $next){
                    $unioned[$index][1] = $sorted[$i][1];
                }else{
                    $unioned[] = $sorted[$i];
                    $index++;
                }
            }elseif($unioned[$index][1] < $sorted[$i][1]){
                $unioned[$index][1] = $sorted[$i][1];
            }
        }
        return $unioned;
    }


    public static function intersectIntervals($masters, $master_is_sorted, $slaves, $slave_is_sorted){
        //Intersection of empty sets
        if(!count($masters)){ 
            return [];
        }elseif(!count($slaves)){
            return []; 
        }

        if(!$master_is_sorted){
            $masters = self::orderIntervalList($masters);
        }
        if(!$slave_is_sorted){
            $slaves = self::orderIntervalList($slaves);
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