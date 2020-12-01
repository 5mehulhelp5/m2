<?php 

$envFilePath = '.\app\etc\env.php';
$configResult = include $envFilePath;


$DB_PREFIX = $configResult['db']['table_prefix'];
$SERVER_NAME = $configResult['db']['connection']['default']['host'];
$SERVER_DB = $configResult['db']['connection']['default']['dbname'];
$SERVER_USER = $configResult['db']['connection']['default']['username'];
$SERVER_PWD = $configResult['db']['connection']['default']['password'];



$conn = new mysqli($SERVER_NAME, $SERVER_USER, $SERVER_PWD, $SERVER_DB);
// Check connection
if ($conn->connect_error) {
    // die("Connection failed: " . $conn->connect_error);
} 


// die;

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS '.$DB_PREFIX.'payme_log (
    id_log int(11) NOT NULL AUTO_INCREMENT,
    id_order int(11),
    authorizationResult VARCHAR(50),
    authorizationCode VARCHAR(50),
    errorCode VARCHAR(50),
    errorMessage VARCHAR(50),
    bin VARCHAR(50),
    brand VARCHAR(50),
    paymentReferenceCode VARCHAR(50),
    purchaseOperationNumber VARCHAR(50),
    purchaseAmount VARCHAR(50),
    purchaseCurrencyCode VARCHAR(50),
    purchaseVerification VARCHAR(50),
    plan VARCHAR(50),
    cuota VARCHAR(50),
    montoAproxCuota VARCHAR(50),
    resultadoOperacion VARCHAR(50),
    paymethod VARCHAR(20),
    fechaHora VARCHAR(50),
    reserved1 VARCHAR(50),
    reserved2 VARCHAR(50),
    reserved3 VARCHAR(50),
    reserved4 VARCHAR(50),
    reserved5 VARCHAR(50),
    reserved6 VARCHAR(50),
    reserved7 VARCHAR(50),
    reserved8 VARCHAR(50),
    reserved9 VARCHAR(50),
    reserved10 VARCHAR(50),
    numeroCip VARCHAR(50),
    PRIMARY KEY  (id_log)
) ';


$sql[] =  "CREATE TABLE IF NOT EXISTS ".$DB_PREFIX."payme_request (
            id_log int(11) NOT NULL AUTO_INCREMENT,
            purchaseOperationNumber VARCHAR(50),
            purchaseAmount VARCHAR(50),
            purchaseCurrencyCode VARCHAR(50),
            language VARCHAR(50),
            billingFirstName VARCHAR(50),
            billingLastName VARCHAR(50),
            billingEmail VARCHAR(50),
            billingAddress VARCHAR(50),
            billingZip VARCHAR(50),
            billingCity VARCHAR(50),
            billingState VARCHAR(50),
            billingCountry VARCHAR(50),
            billingPhone VARCHAR(50),
            shippingFirstName VARCHAR(50),
            shippingLastName VARCHAR(50),
            shippingEmail VARCHAR(50),
            shippingAddress VARCHAR(50),
            shippingZip VARCHAR(50),
            shippingCity VARCHAR(50),
            shippingState VARCHAR(50),
            shippingCountry VARCHAR(50),
            shippingPhone VARCHAR(50),
            programmingLanguage VARCHAR(50),
            userCommerce VARCHAR(50),
            userCodePayme VARCHAR(50),
            descriptionProducts VARCHAR(100),
            purchaseVerification VARCHAR(200),
            reserved1 VARCHAR(50),
            reserved2 VARCHAR(50),
            reserved3 VARCHAR(50),
            reserved4 VARCHAR(50),
            reserved5 VARCHAR(50),
            reserved6 VARCHAR(50),
            reserved7 VARCHAR(50),
            reserved8 VARCHAR(50),
            reserved9 VARCHAR(50),
            reserved10 VARCHAR(50),
            PRIMARY KEY  (id_log)
); 



";

foreach ($sql as $query) {
   if ($conn->query($query) === TRUE) {
	    // echo "Table MyGuests created successfully";
	} else {
	    // echo "Error creating table: " . $conn->error;
	};
}



$conn->close();




 ?>