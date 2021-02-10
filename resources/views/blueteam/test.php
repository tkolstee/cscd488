<?php $cart = ['name1','name1']; 
    $list = array();
    foreach($cart as $item){
        if(!isset($list[$item])){
            $list += [$item => 1];
        }else{
            $list[$item]++;
        }
    }
    echo $list['name1'] . "\n";
    foreach($cart as $name=>$quantity){
        echo $quantity . "\n";
    }
?>