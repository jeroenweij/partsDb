<?php

function farnell($manuPartId)
{
//    $manuPartId=urlencode($manuPartId);
//    $apiurl = "https://api.element14.com/catalog/products?callInfo.responseDataFormat=JSON&term=manuPartNum:$manuPartId&storeInfo.id=nl.farnell.com&callInfo.apiKey=xdudq88vsr5q5y2mdtghukkq";
//
//    $curl = curl_init($apiurl);
//    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
//    curl_setopt($curl, CURLOPT_TIMEOUT, 180);
//    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
//    $result = curl_exec($curl);
//    curl_close ($curl);

    $result='{"manufacturerPartNumberSearchReturn":{"numberOfResults":1,"products":[{"sku":"1122487","displayName":"BINDER - 08 2433 000 001 - Connector Accessory, Rear Mount, For Snap-In Receptacles, IP67, Adaptor, Series 720 Socket, 720","packSize":1,"unitOfMeasure":"PER STUK","id":"pf_NLE_1122487_0","brandName":"BINDER","translatedManufacturerPartNumber":"08 2433 000 001","translatedMinimumOrderQuality":1,"publishingModule":null}]}}';
    $json_data =json_decode($result);
    print_r($json_data);
}
//farnell("ERJ3EKF2493V");
//{"manufacturerPartNumberSearchReturn":{"numberOfResults":4,"products":[{"sku":"2059513","displayName":"PANASONIC - ERJ3EKF2493V - SMD Chip Resistor, 249 kohm, ± 1%, 100 mW, 0603 [1608 Metric], Thick Film, Precision","packSize":1,"unitOfMeasure":"TAPE EN REEL, AFGESNEDEN","id":"pf_NLE_2059513_0","brandName":"PANASONIC","translatedManufacturerPartNumber":"ERJ3EKF2493V","translatedMinimumOrderQuality":10,"publishingModule":null},{"sku":"2059513RL","displayName":"PANASONIC - ERJ3EKF2493V - SMD Chip Resistor, 249 kohm, ± 1%, 100 mW, 0603 [1608 Metric], Thick Film, Precision","packSize":1,"unitOfMeasure":"TAPE EN REEL, AFGESNEDEN","id":"pf_NLE_2059513RL_0","brandName":"PANASONIC","translatedManufacturerPartNumber":"ERJ3EKF2493V","translatedMinimumOrderQuality":500,"publishingModule":null},{"sku":"2447306","displayName":"MULTICOMP PRO - MCWR06X2493FTL - SMD Chip Resistor, 249 kohm, ± 1%, 100 mW, 0603 [1608 Metric], Thick Film, General Purpose","packSize":1,"unitOfMeasure":"TAPE EN REEL, AFGESNEDEN","id":"pf_NLE_2447306_0","brandName":"MULTICOMP PRO","translatedManufacturerPartNumber":"MCWR06X2493FTL","translatedMinimumOrderQuality":10,"publishingModule":null},{"sku":"2446623","displayName":"MULTICOMP PRO - MCWR06X2493FTL - SMD Chip Resistor, 249 kohm, ± 1%, 100 mW, 0603 [1608 Metric], Thick Film, General Purpose","packSize":1,"unitOfMeasure":"TAPE EN REEL, VOLLEDIG","id":"pf_NLE_2446623_0","brandName":"MULTICOMP PRO","translatedManufacturerPartNumber":"MCWR06X2493FTL","translatedMinimumOrderQuality":5000,"publishingModule":null}]}}

farnell("08 2433 000 001");
//{"manufacturerPartNumberSearchReturn":{"numberOfResults":1,"products":[{"sku":"1122487","displayName":"BINDER - 08 2433 000 001 - Connector Accessory, Rear Mount, For Snap-In Receptacles, IP67, Adaptor, Series 720 Socket, 720","packSize":1,"unitOfMeasure":"PER STUK","id":"pf_NLE_1122487_0","brandName":"BINDER","translatedManufacturerPartNumber":"08 2433 000 001","translatedMinimumOrderQuality":1,"publishingModule":null}]}}
?>
