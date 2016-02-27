# Codebook for Image ETL
The steps below will extract pathway figures and captions from PMC image search results, perform OCR and assess relevant gene sets. Some of the steps can be manually performed on small and prototype scales. The protocol and code rely on the following folder structure:

* pmc (source)
 * pathway_signaling (subset, e.g., search term)
  * #.#_rawhtml (versioned sets of files)
  * #.#.#_figures (versioned sets of extracted images)
  * #.#.#.#_ocr (versioned sets of OCR results)
  * #.#.#.x_results (versioned summaries of gene counts from OCR results)

## PubMed Central Image Extraction

This url returns >40k figures from PMC articles matching "signaling pathways". Approximately 80% of these are actually pathway figures. These make a reasonably efficient source of sample figures to test methods. *Consider other search terms and other sources when scaling up.*

```
http://www.ncbi.nlm.nih.gov/pmc/?term=signaling+pathway&report=imagesdocsum
```

### Scrape HTML
For sample sets you can simply save dozens of pages of results and quickly get 1000s of pathway figures. *Consider automating this step when scaling up.*

```
Set Display Settings: to max (100)
Save raw html to designated folder, e.g., pmc/signaling_pathway/#.#_rawhtml
```

Next, configure and run this php script to generated annotated sets of image and html files.

```
php pmc_image_parse.php
```

* depends on simple_html_dom.php
* outputs images as "PMC######__<filename>.<ext>
* outputs caption as "PMC######__<filename>.<ext>.html

Note: these pairs of files can then be displayed together in P4L interface

### Prune Images
Another manual step here to increase accuracy of downstream counts. Make a copy of the figures dir, incrementing the version. View the extracted images in Finder, for example, and delete pairs of files associated with figures that are not actually pathways. In this first sample run, ~20% of images were pruned away. The most common non-pathway figures wer of gel electrophoresis runs. *Consider automated ways to either exclude gel figures or select only pathway images to scale this step up.*

### Imagemagick
Exploration of settings to improve OCR by pre-processing of image:

```
convert test1.jpg -colorspace gray test1_gr.jpg
convert test1_gr.jpg -threshold 50% test1_gr_th.jpg 
convert test1_gr_th.jpg -define connected-components:verbose=true -define connected-components:area-threshold=400 -connected-components 4 -auto-level -depth 8 test1_gr_th_cc.jpg
```

### Optical Character Recognition

#### Adobe 

1. Open Adobe Acrobat
2. File > Action Wizard > Create New Action (or run previously saved action)
3. Configure as follows:
 * Start with: folder on my computer (#.#.#_figures)
 * Steps: Recognize Text (Options: Eng, Searchable image, 600 dpi)
 * Save to: folder on my computer (#.#.#.#_ocr)

**Saved action as: folder-ocr-text**

#### Tesseract 
Configure for local file paths and run on batch:
```
tesseract_batch.php
```

To run on individual image:
```
tesseract <infile> <outfile.noext>  -l <lang> <configfile>
```
For example:
```
tesseract Figure1.jpg Figure1 -l eng tessconfig.txt
```

### Counting Genes
Configure for local file paths and input ocr folder and then run:
```
txt_lookup.php
```

* depends on hgnc_list_all_FINAL.txt, hgnc_list_wp_all.txt and hgnc_list_wp_human.txt 
* outputs a counts.csv file into input ocr folder
	
	
#### Making hgnc_list_all_FINAL.txt
1. Downloaded Total Approved Symbols as txt from http://www.genenames.org/cgi-bin/statistics
2. Imported txt into Excel, explicitly choosing "text" for symbol and alias columns during import wizard (to avoid date conversion of SEPT1, etc)
3. Extracted 'symbol', 'alias symbol', and 'prev symbol' into single column with single entries
4. Set all entries to uppercase
5. Added generic symbols for gene families, e.g., "WNT" in addition to all the WNT## entries
6. Also added copies of symbols without hyphens, e.g., "ERVK1" in addition to original "ERVK-1"
7. Note: these two additions above help cover common mistakes/practices among figure makers
8. Saved list as txt file for ID lookup and eng.user-words: hgnc_list_all_FINAL.txt
9. Open in TextWrangler to switch linefeed to "Unix(LF)" to work with php scripts

**Note: a mapping table can be used to map any hits from aliases, prev symbols, or generic symbols to hgnc identifiers.**

#### Making WikiPathways all and human lists
1. Downloaded http://www.pathvisio.org/data/bots/gmt/wikipathways.gmt
2. Extracted Homo sapiens subset
3. Used Biomart's ID Converter to get HGNC symbols (or Associated gene names) to then generate two files:
  * hgnc_list_wp_human.txt
  * hgnc_list_wp_all.txt
4. These were made all uppercase and unique
5. Then aliases and prior names were included by mapping to hgnc table (above)
6. Similar processing was done to add generic and unhyphenated entries as well
7. Saved as txt files for ID lookup script
8. Open in TextWrangler to switch linefeed to "Unix(LF)" to work with php scripts
