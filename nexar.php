<?php
session_start();
$token = "";
if (!isset($_SESSION["token"]) || strlen($_SESSION["token"]) < 5) {
    $un = getenv('nexarAPI', true);
    $pw = getenv('nexarAPIP', true);

    $curl = curl_init('https://identity.nexar.com/connect/token');
    curl_setopt($curl, CURLOPT_USERAGENT, "PARTSDB");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 180);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=$un&client_secret=$pw&scope=supply.domain");

    $result = curl_exec($curl);
    curl_close($curl);

    $nexarData = json_decode($result, true);

    if (array_key_exists("access_token", $nexarData)) {
        $token = $nexarData["access_token"];
        $_SESSION["token"] = $token;
    }
} else {
    $token = $_SESSION["token"];
}

function nexarQuery($manuPartId)
{
//    global $token;
//    $apiurl = "https://api.nexar.com/graphql";
//
//    $curl = curl_init($apiurl);
//    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//    curl_setopt($curl, CURLOPT_TIMEOUT, 180);
//    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
//    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
//        'Accept-Encoding: gzip, deflate',
//        'Content-Type: application/json',
//        'Accept:application/json',
//        'Connection:keep-alive',
//        "Authorization: Bearer $token"));
//
//    $jsonData='{"query":"
//    query PartSearch {
//      supSearch(q: \"' . $manuPartId . '\", inStockOnly: false, limit: 1) {
//        results {
//          part {
//            mpn
//            category {
//              name
//            }
//            shortDescription
//            specs{
//              value
//              valueType
//              units
//              attribute
//              {
//                name
//              }
//            }
//          }
//        }
//      }
//    }
//    "}';
//    $jsonData = trim(preg_replace('/\s+/', ' ', $jsonData));
//    curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
//
//    $result = curl_exec($curl);
//    curl_close($curl);

    $result = '{"data":{"supSearch":{"results":[{"part":{"mpn":"SMF45A","category":{"name":"TVS Diodes"},"shortDescription":"SMF Series 200 W 45 V Uni-Directional Surface Mount TVS Diode - SOD-123F","specs":[{"value":"50","valueType":"number","displayValue":"50 V","units":"V","attribute":{"name":"Breakdown Voltage"}},{"value":"72.7","valueType":"number","displayValue":"72.7 V","units":"V","attribute":{"name":"Clamping Voltage"}},{"value":"Zener","valueType":"text","displayValue":"Zener","units":"","attribute":{"name":"Composition"}},{"value":"2","valueType":"number","displayValue":"2 mm","units":"mm","attribute":{"name":"Depth"}},{"value":"Unidirectional","valueType":"text","displayValue":"Unidirectional","units":"","attribute":{"name":"Direction"}},{"value":"Single","valueType":"text","displayValue":"Single","units":"","attribute":{"name":"Element Configuration"}},{"value":"1.1","valueType":"number","displayValue":"1.1 mm","units":"mm","attribute":{"name":"Height"}},{"value":"Lead Free","valueType":"text","displayValue":"Lead Free","units":"","attribute":{"name":"Lead Free"}},{"value":"1","valueType":"number","displayValue":"1 \u00B5A","units":"\u00B5A","attribute":{"name":"Leakage Current"}},{"value":"2.9","valueType":"number","displayValue":"2.9 mm","units":"mm","attribute":{"name":"Length"}},{"value":"Production","valueType":"text","displayValue":"Production (Last Updated: 2 years ago)","units":"","attribute":{"name":"Lifecycle Status"}},{"value":"55.3","valueType":"number","displayValue":"55.3 V","units":"V","attribute":{"name":"Max Breakdown Voltage"}},{"value":"150","valueType":"number","displayValue":"150 \u00B0C","units":"\u00B0C","attribute":{"name":"Max Junction Temperature (Tj)"}},{"value":"150","valueType":"number","displayValue":"150 \u00B0C","units":"\u00B0C","attribute":{"name":"Max Operating Temperature"}},{"value":"1","valueType":"number","displayValue":"1 \u00B5A","units":"\u00B5A","attribute":{"name":"Max Reverse Leakage Current"}},{"value":"2.8","valueType":"number","displayValue":"2.8 A","units":"A","attribute":{"name":"Max Surge Current"}},{"value":"50","valueType":"number","displayValue":"50 V","units":"V","attribute":{"name":"Min Breakdown Voltage"}},{"value":"-65","valueType":"number","displayValue":"-65 \u00B0C","units":"\u00B0C","attribute":{"name":"Min Operating Temperature"}},{"value":"Surface Mount","valueType":"text","displayValue":"Surface Mount","units":"","attribute":{"name":"Mount"}},{"value":"1","valueType":"number","displayValue":"1","units":"","attribute":{"name":"Number of Channels"}},{"value":"1","valueType":"number","displayValue":"1","units":"","attribute":{"name":"Number of Elements"}},{"value":"2","valueType":"number","displayValue":"2","units":"","attribute":{"name":"Number of Pins"}},{"value":"1","valueType":"number","displayValue":"1","units":"","attribute":{"name":"Number of Unidirectional Channels"}},{"value":"2.8","valueType":"number","displayValue":"2.8 A","units":"A","attribute":{"name":"Peak Pulse Current"}},{"value":"200","valueType":"number","displayValue":"200 W","units":"W","attribute":{"name":"Peak Pulse Power "}},{"value":"Unidirectional","valueType":"text","displayValue":"Unidirectional","units":"","attribute":{"name":"Polarity"}},{"value":"No","valueType":"text","displayValue":"No","units":"","attribute":{"name":"Power Line Protection"}},{"value":"No","valueType":"text","displayValue":"No","units":"","attribute":{"name":"Radiation Hardening"}},{"value":"50","valueType":"number","displayValue":"50 V","units":"V","attribute":{"name":"Reverse Breakdown Voltage"}},{"value":"45","valueType":"number","displayValue":"45 V","units":"V","attribute":{"name":"Reverse Standoff Voltage"}},{"value":"45","valueType":"number","displayValue":"45 V","units":"V","attribute":{"name":"Reverse Voltage"}},{"value":"Compliant","valueType":"text","displayValue":"Compliant","units":"","attribute":{"name":"RoHS"}},{"value":"8541100080, 8541100080|8541100080, 8541100080|8541100080|8541100080, 8541100080|8541100080|8541100080|8541100080","valueType":"text","displayValue":"8541100080, 8541100080|8541100080, 8541100080|8541100080|8541100080, 8541100080|8541100080|8541100080|8541100080","units":"","attribute":{"name":"Schedule B"}},{"value":"SMD/SMT","valueType":"text","displayValue":"SMD/SMT","units":"","attribute":{"name":"Termination"}},{"value":"1","valueType":"number","displayValue":"1 mA","units":"mA","attribute":{"name":"Test Current"}},{"value":"45","valueType":"number","displayValue":"45 V","units":"V","attribute":{"name":"Working Voltage"}}]}}]}},"extensions":{"requestId":"f16945ff-c300-462a-95f2-82da2e1d852a"}}';
    return $result;
}

?>
