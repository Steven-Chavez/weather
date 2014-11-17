<?php
// open weather map's API key
// ironically, they make us sign up but never verify it...
$apiKey = "6040819e5f2e8d834e881202f2e5e57c";

// base URL is the basis for *ALL* API calls
$baseUrl = "http://api.openweathermap.org/data/2.5/weather";

// input to the API: direct search
if(empty($_GET["city"]) === false && ($city = filter_input(INPUT_GET, "city", FILTER_SANITIZE_ENCODED)) !== false){
	$query = "?q=$city";
}

// input to the API: geolocation
if(isset($_GET["useGps"]) && $_GET["useGps"] === "on"
	&& empty($_GET["latitude"]) === false && empty($_GET["longitude"]) === false)
{
	$latitude = filter_input(INPUT_GET, "latitude", FILTER_VALIDATE_FLOAT);
	$longitude = filter_input(INPUT_GET, "longitude", FILTER_VALIDATE_FLOAT);

	// FIXME: there's no range veriffication on the coordinates
	if($latitude === false || $longitude === false){
		throw( new RuntimeException("invalid longitude or latitude"));
	}
	$query = "?lat=$latitude&lon=$longitude";
}

// defeat malicious & incompetent users
if(empty($query) === true) {
	throw(new RuntimeException("Invalid city detected"));
	exit;
}

// final URL to get data from
$urlGlue = "$baseUrl$query";

// fetch the raw JSON data
$jsonData = @file_get_contents($urlGlue);
if($jsonData === false) {
	throw(new RuntimeException("Unable to download weather data"));
}

// convert the JSON data into a big associative array
$weatherData = json_decode($jsonData, true);

//var_dump($weatherData);

// echo select fields from the array (cut superflous data)
if($weatherData["cod"] == 200)
{
	// get the image icon URL
	$imageIcon = "http://openweathermap.org/img/w/" . $weatherData["weather"][0]["icon"] . ".png";

	// as a preprocessing step, format the date
	$dateTime = new DateTime();
	$dateTime->setTimestamp($weatherData["dt"]);
	$formattedDate = $dateTime->format("Y-m-d H:i:s");

	// convert the temperature
	$kevlin  = floatval($weatherData["main"]["temp"]);
	$celsius = $kevlin - 273.15;

	echo "<p><img src=\"$imageIcon\" style=\"float: left;\" alt=\"" . $weatherData["weather"][0]["description"] ."\" />"
		. $weatherData["name"]             . ", "
		. $weatherData["sys"]["country"]   . "<br />"
		. $celsius                         . " &deg;C<br />"
		. $weatherData["main"]["pressure"] . " hPa<br />"
		. $formattedDate                   . "</p>";
}
else
{
	echo "<p>Unable to get weather data: " . $weatherData["message"] . "</p>";
}
?>
