<?php

$indir = 'pmc/singaling_pathway/1.1.2.1_ocr/';

$lookarr = file("hgnc_list_all_FINAL.txt",FILE_IGNORE_NEW_LINES);
$wparr = file("hgnc_list_wp_all.txt",FILE_IGNORE_NEW_LINES);
$wphsarr = file("hgnc_list_wp_human.txt",FILE_IGNORE_NEW_LINES);

chdir($indir);

$tofile = fopen("counts.csv", "w"); 
fputcsv($tofile, array("file","hgnc hits","on wp_all","on wp_hs","new wp_hs only","new wp_all","new hits only to wp_hs","new hits to wp_all"));

$totarr = array();

foreach(glob('*.txt') as $fn){
	$str = file_get_contents($fn);
	preg_match_all("/(\w+(-\w+){0,3})/", $str, $arr);

	$uniarr = array_unique(array_map("strtoupper",$arr[0]));

	$intlookarr = array_intersect($uniarr,$lookarr);
	$intwparr = array_intersect($intlookarr,$wparr);
        $intwphsarr = array_intersect($intwparr,$wphsarr);

        $totarr = array_merge($totarr, $intlookarr);
	
	$diffwpstr = '';
	foreach(array_diff($intlookarr,$intwparr) as $diffwp){
		$diffwpstr .= $diffwp . "|";
	}
        $diffwphsstr = '';
        foreach(array_diff($intwparr,$intwphsarr) as $diffwphs){
                $diffwphsstr .= $diffwphs . "|";
        }
	
	
	$cntlook = count($intlookarr);
	$cntwp = count($intwparr);
	$cntwphs = count($intwphsarr);

	fputcsv($tofile, array($fn,$cntlook,$cntwp,$cntwphs,$cntwp-$cntwphs,$cntlook-$cntwp,rtrim($diffwphsstr,"|"),rtrim($diffwpstr,"|")));
}

$unitotarr = array_unique($totarr);
$intlookarr = array_intersect($unitotarr,$lookarr);
$intwparr = array_intersect($intlookarr,$wparr);
$intwphsarr = array_intersect($intwparr,$wphsarr);
$difflookwparr = array_diff($intlookarr,$intwparr);
$difflookwphsarr = array_diff($intwparr,$intwphsarr);

fputcsv($tofile, array("UNIQUE CNTS",count($intlookarr),count($intwparr),count($intwphsarr),count($difflookwphsarr),count($difflookwparr)));
fwrite($tofile, "HGNC hits already on WP pathways (".count($intwparr)."):".PHP_EOL);
fputcsv($tofile, $intwparr);
fwrite($tofile, "HGNC hits already on human WP pathways (".count($intwphsarr)."):".PHP_EOL);
fputcsv($tofile, $intwphsarr);
fwrite($tofile, "HGNC hits already on non-human WP pathways, but not found on any human WP pathway (".count($difflookwphsarr)."):".PHP_EOL);
fputcsv($tofile, $difflookwphsarr);
fwrite($tofile, "HGNC hits not found on any WP pathway (".count($difflookwparr)."):".PHP_EOL);
fputcsv($tofile, $difflookwparr);
fclose($tofile);


?>
