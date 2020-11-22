<?php

$prereqs = ['foo', 'bar'];
$have    = ['foo', 'one', 'two', 'three'];
$unmet   = array_diff($prereqs, $have);
print ("UNMET PREREQS: " );
print_r($unmet);
print("\n");

