<?php

function epin_rand_pad()
{
    return str_pad((string )mt_rand(0, 99999999999), 11, '0', STR_PAD_LEFT);
}

function epin_rand_string()
{
    $character_set_array = array();
    $character_set_array[] = array('count' => 4, 'characters' =>
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    $character_set_array[] = array('count' => 4, 'characters' => '0123456789');
    $temp_array = array();
    foreach ($character_set_array as $character_set)
    {
        for ($i = 0; $i < $character_set['count']; $i++)
        {
            $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) -
                1)];
        }
    }

    shuffle($temp_array);
    return implode('', $temp_array);
}

function epin_rand_pairing_4()
{
    return epin_rand_key(3,4);
}

function epin_rand_key($group_num = 4, $pair_num = 2)
{
    $letters = epin_rand_string().epin_rand_pad().epin_rand_string().epin_rand_pad();
    //$letters .= '01234ABCDEFGHIJKLMNOPQRSTUVWXYZ56789ABCDEFGHIJKLMNOPQRS0123456789TUVWXYZ';
    $key = '';

    for ($i = 1; $i <= $group_num; $i++)
    {
        $key .= substr($letters, rand(0, (strlen($letters) - $pair_num)), $pair_num) .'-';
    }

    $key[strlen($key) - 1] = ' ';

    return trim($key);
}
