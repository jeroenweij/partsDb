<?php

function scan2id($input)
{
    if ((str_starts_with($input, "[)>") || str_starts_with($input, ">[)>") || str_starts_with($input, "EA")) && str_contains($input, "?") ){
        // Data matrix
        $splitted = explode("?", $input);
        $input = "";
        foreach ($splitted as $value) {
            if (str_starts_with($value, "1P"))
            {
                $input = $value;
                break;
            }
        }
    } else {
        if (strlen($input) > 60 && substr_count($input, "&") > 6){
            $splitted = explode("&", $input);
            $input = $splitted[3];
        }
    }

    if (str_starts_with($input, "1P"))
    {
        $input = substr($input, 2);
    }
    return $input;
}

?>
