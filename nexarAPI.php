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
    global $token;
    $apiurl = "https://api.nexar.com/graphql";

    $curl = curl_init($apiurl);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 180);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Accept-Encoding: gzip, deflate',
        'Content-Type: application/json',
        'Accept:application/json',
        'Connection:keep-alive',
        "Authorization: Bearer $token"));

    $jsonData='{"query":"
    query PartSearch {
      supSearch(q: \"' . $manuPartId . '\", inStockOnly: false, limit: 1) {
        results {
          part {
            mpn
            category {
              name
              path
            }
            shortDescription
            specs{
              value
              valueType
              units
              attribute
              {
                name
              }
            }
          }
        }
      }
    }
    "}';
    $jsonData = trim(preg_replace('/\s+/', ' ', $jsonData));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);

    $result = curl_exec($curl);
    curl_close($curl);

    return $result;
}

?>
