<?php

function scan2id($input)
{
    if (str_starts_with($input, "[)>") && str_contains($input, "?") ){
        // Data matrix
        $splitted = explode("?", $input);
        foreach ($splitted as $value) {
            if (str_starts_with($value, "1P"))
            {
                $input = $value;
                break;
            }
        }
    }

    if (str_starts_with($input, "1P"))
    {
        $input = substr($input, 2);
    }
    return $input;
}

?>