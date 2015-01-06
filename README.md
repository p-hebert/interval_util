#Interval Utilities
Interval utilities for handling intervals, such as time intervals. It includes set
operations such as union, intersection and difference. The Interval class allows
the user to create intervals without dealing with all the logic inherent to it.

##How To Use
Two paths are available to you: the OO one and the procedural one. The first one
requires you to use/implement the Interval class for your needs, while the second
relies on a simple array format where your input is composed of intervals in the
format [start, end, data = null], where the third element is optional.

##Reserves
These utilities have not been tested. The IntervalUtils::reflexiveUnion(), IntervalUtils::intersect() 
are however based on working, tested logic (a previous version).

The overall code should be working, as the logic underlying it is working and my IDE does not detect
any problem.

The comparisons in all of these classes assume that the data can be compared
via the normal comparison operators (<,>, <=, >=). These includes, but are not limited
to: all scalar values (except boolean, please), and DateTime objects.

Finally, all of the utilities in IntervalUtils do not consider nor keep the $data field.
You will have to modify those in order to add your own rules about how to handle that.
