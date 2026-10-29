<?php

function required(string $key): string {
    $v=trim($_POST[$key]??'');
    if($v==='')throw new RuntimeException('Please complete all required fields.');
    if(strlen($v)>20000)throw new RuntimeException('Please shorten your entry to 20,000 characters.');
    if(in_array($key,['intake_date','record_date','signed_date','start_date','end_date'],true)){
        $date=DateTimeImmutable::createFromFormat('!Y-m-d',$v);
        if(!$date || $date->format('Y-m-d')!==$v)throw new RuntimeException('Enter a valid date.');
    }
    return $v;
}
function choice(string $key,array $options): string {
    $v=required($key);
    if(!in_array($v,$options,true))throw new RuntimeException('Invalid selection.');
    return $v;
}
