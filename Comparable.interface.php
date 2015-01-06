<?php

interface Comparable {
    static function compare(self $a, self $b);
    function compareTo(self $a);
}

