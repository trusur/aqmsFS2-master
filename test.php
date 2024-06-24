<?php
    // $a =[];
    for ($i = 0; $i < 10; $i++) {
        $a[$i] = (object) [
            'id' => $i+1,
            'is_valid' => rand(0,1),
            'value' => rand(0, 1000)
        ];
    }

    // average value
    $avg = array_sum(array_column($a, 'value')) / count($a);
    print_r($a);
    echo $avg;

    