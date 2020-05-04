<?php declare(strict_types = 1);

use Fapi\FapiClient\FapiClientFactory;
use Fapi\FapiClient\Tools\SecurityChecker;

require_once __DIR__ . '/../vendor/autoload.php';

$fapiUsername = ''; //potřeba získat z pluginu
$fapiApiToken = ''; //potřeba získat z pluginu

// kontrola zda jsou v pluginu nastavné přihlašovací údaje do FAPI API
if (!$fapiUsername || !$fapiApiToken) {
	http_response_code(400);
	echo 'Missing FAPI credential';

	die;
}

// vytažení dat z post requestu
$id = isset($_POST['id']) ? (int) $_POST['id'] : null;
$time = isset($_POST['time']) ? (int) $_POST['time'] : null;
$security = isset($_POST['security']) ? (string) $_POST['security'] : null;

// kontrola zda tam jsou všechny potřebné data
if ($id === null || $time === null || $security === null) {
	http_response_code(400);
	echo 'Invalid notification request';

	die;
}

// vytvoření FAPI klienta
$client = (new FapiClientFactory())->createFapiClient($fapiUsername, $fapiApiToken);

// vytažení faktury pro kterou byla notifkace poslána
try {
	$invoice = $client->getInvoices()->find($id);
} catch (\Fapi\FapiClient\RuntimeException $exception) {
	http_response_code(400);
	echo $exception->getMessage();

	die;
}

// kontrola faktury
if (!SecurityChecker::isValid($invoice, $time, $security)) {
	http_response_code(400);
	echo 'Invalid notification request. Security check not pass.';

	die;
}

// pokud se nejedná o fakturu, zjednodušený doklad nebo dobropis, není potřeba dále notifikaci zpracovávat.
// ke každému dokladu se posílá notifikace zvlášť
// pokud tedy klient vystavuje zálohové faktury
// notifikace o zaplacení přijde jak k zálohové faktuře tak ke koncomému daňovému dokladu
if (!in_array($invoice['type'], ['invoice', 'simplified_invoice', 'credit_note'], true)) {
	echo 'Invoice type is ignored for further processing.';

	die;
}

// kontrola zda je faktura zaplacená
if ((bool) $invoice['paid'] === false) {
	http_response_code(400);
	echo 'Invoices is not paid.';

	die;
}

// emailová adresa klienta, WP bude potřeba tuhle mailovku kontrolovat. Našel jsme tohle: https://developer.wordpress.org/reference/functions/is_email/
$customerEmail = $invoice['customer_email'];

// možná k tomu slouží nějaké jiné funkce, to nevím s WP jsem nikdy nedělal.
if (!is_email($customerEmail)) {
	http_response_code(400);
	echo 'Invalid customer email address.';

	die;
}

// tady už samotné vytvoření člena v členské sekci. Tu funkci jsem střelil. Taky nevím jak se to zákládá.
$userId = wp_create_user($customerEmail);

// ...

http_response_code(201);
echo 'OK';



