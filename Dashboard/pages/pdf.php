<?php
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_POST['timetable_html'])) {
    die("No timetable data received.");
}

$html = $_POST['timetable_html'];

// Set Dompdf options
$options = new Options;
$options->setChroot(__DIR__);
$options->setIsRemoteEnabled(true);

$dompdf = new Dompdf($options);
$dompdf->setPaper("A4", "landscape");
$dompdf->loadHtml($html);
$dompdf->render();

// Stream PDF to browser (force download)
$dompdf->stream("timetable_generate.pdf", ["Attachment" => 1]);
exit;
?>
