<?php
 
$configfile = "/git/pathways4life/image-etl/tessconfig.txt";
$indir = "/git/pathways4life/image-etl/pmc/signaling_pathway/1.1.2_figures /";
$outdir = "/git/pathways4life/image-etl/pmc/signaling_pathway/1.1.2.2_ocr/";

chdir($indir);

foreach(glob('*.*') as $fn){
	
	if(pathinfo($fn)['extension'] != "html"){
		exec("tesseract " . $fn . " " . $outdir . $fn . " -l eng $configfile");
		echo "processing " . $fn . "\r\n";
	}
}

?>
