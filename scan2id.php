<?php

function scan2id($input)
{
    if (strlen($input) > 80 ){
        // Data matrix
    }

    if (str_starts_with($input, "1P"))
    {
        $input = substr($input, 2);
    }
    return $input;
}

?>