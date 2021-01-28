<?php
/* Copyright (C) 2003		Rodolphe Quiedeville		<rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010	Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin				<regis.houssin@capnetworks.com>
 * Copyright (C) 2008		Raphael Bertrand (Resultic)	<raphael.bertrand@resultic.fr>
 * Copyright (C) 2011		Fabrice CHERRIER
 * Copyright (C) 2013       Philippe Grand	            <philippe.grand@atoo-net.com>
 * Copyright (C) 2015       Marcos García               <marcosgdf@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/core/modules/contract/doc/pdf_strato.modules.php
 *	\ingroup    ficheinter
 *	\brief      Strato contracts template class file
 */
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';
/**
 *	Class to build contracts documents with model Strato
 */
class pdf_compte_client 
{
	var $db;
	var $name;
	var $description;
	var $type;

	var $phpmin = array(5,5,0); // Minimum version of PHP required by module
	var $version = 'dolibarr';

	var $page_largeur;
	var $page_hauteur;
	var $format;
	var $marge_gauche;
	var	$marge_droite;
	var	$marge_haute;
	var	$marge_basse;

	/**
	 * Issuer
	 * @var Societe
	 */
	public $emetteur;

	/**
	 * Recipient
	 * @var Societe
	 */
	public $recipient;

	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
		global $conf,$langs,$mysoc;

		$this->db = $db;
		$this->name = 'Open_CPT';
		$this->description = "Ouverture de compte Client";

		// Dimension page pour format A4
		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=isset($conf->global->MAIN_PDF_MARGIN_LEFT)?$conf->global->MAIN_PDF_MARGIN_LEFT:10;
		$this->marge_droite=isset($conf->global->MAIN_PDF_MARGIN_RIGHT)?$conf->global->MAIN_PDF_MARGIN_RIGHT:10;
		$this->marge_haute =isset($conf->global->MAIN_PDF_MARGIN_TOP)?$conf->global->MAIN_PDF_MARGIN_TOP:10;
		$this->marge_basse =isset($conf->global->MAIN_PDF_MARGIN_BOTTOM)?$conf->global->MAIN_PDF_MARGIN_BOTTOM:10;

		$this->option_logo = 0;                    // Affiche logo
		$this->option_tva = 0;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_modereg = 0;                 // Affiche mode reglement
		$this->option_condreg = 0;                 // Affiche conditions reglement
		$this->option_codeproduitservice = 0;      // Affiche code produit-service
		$this->option_multilang = 0;               // Dispo en plusieurs langues
		$this->option_draft_watermark = 0;		   //Support add of a watermark on drafts

		// Get source company
		$this->emetteur=$mysoc;
		if (empty($this->emetteur->country_code)) $this->emetteur->country_code=substr($langs->defaultlang,-2);    // By default, if not defined

		// Define position of columns
		$this->posxdesc=$this->marge_gauche+1;
	}

	/**
     *  Function to build pdf onto disk
     *
     *  @param		CommonObject	$object				Id of object to generate
     *  @param		object			$outputlangs		Lang output object
     *  @param		string			$srctemplatepath	Full path of source filename for generator using a template file
     *  @param		int				$hidedetails		Do not show line details
     *  @param		int				$hidedesc			Do not show desc
     *  @param		int				$hideref			Do not show ref
     *  @return		int									1=OK, 0=KO
	 */
	function write_file($object,$outputlangs,$srctemplatepath='',$hidedetails=0,$hidedesc=0,$hideref=0)
	{
		global $user,$langs,$conf,$hookmanager,$mysoc;

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("bills");
		$outputlangs->load("products");
		$outputlangs->load("orders");

		if ($conf->societe->dir_output)
		{
            $object->fetch_thirdparty();

			// Definition of $dir and $file
			if ($object->specimen)
			{
				$dir = $conf->societe->dir_output;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->societe->dir_output . "/" . $objectref;
				$file = $dir . "/cpt_" . $objectref . ".pdf";
			}

			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$outputlangs->trans("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{

                $pdf=pdf_getInstance($this->format);
                $default_font_size = pdf_getPDFFontSize($outputlangs)-0.3;	// Must be after pdf_getInstance
                $heightforinfotot = 50;	// Height reserved to output the info and total part
		        $heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
	            $heightforfooter = $this->marge_basse + 8;	// Height reserved to output the footer (value include bottom margin)
                $pdf->SetAutoPageBreak(1,0);

                if (class_exists('TCPDF'))
                {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }
                $pdf->SetFont(pdf_getPDFFont($outputlangs));


				$pdf->Open();
				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTitle($outputlangs->convToOutputCharset('cpt-' . $object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("OUverture de compte"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right

				// Add Pages from models
				$infile= DOL_DATA_ROOT.'/theobald/modelpdf/ouverture_cpt.pdf';
				if (file_exists($infile) && is_readable($infile)) {
					$pagecount = $pdf->setSourceFile($infile);
					for($i = 1; $i <= $pagecount; $i ++) {
						$tplIdx = $pdf->importPage($i);
						if ($tplIdx!==false) {
							$s = $pdf->getTemplatesize($tplIdx);
							$pdf->AddPage($s['h'] > $s['w'] ? 'P' : 'L');
							$pdf->useTemplate($tplIdx);
						} else {
							setEventMessages(null, array($infile.' cannot be added, probably protected PDF'),'warnings');
						}
					}
				}
				
				$pdf->SetPage(1);

 				$pdf->SetFont('','', $default_font_size+1);
 				$pdf->SetXY(90, 45);
 				$out = $outputlangs->convToOutputCharset($object->name);
 				$pdf->MultiCell(100, 0, $out,0,'L');
				
 				$out .= $object->address. "\r\n";
 				$out .= $object->zip . ' ' . $object->town ."\r\n";
 				$pdf->SetFont('','', $default_font_size+1);
 				$pdf->SetXY(90, 53);
 				$out = $outputlangs->convToOutputCharset($out);
 				$pdf->MultiCell(100, 0, $out,0,'L');
 				
 				$out .= 'Tel: ' . $object->thirdparty->phone . ' - Fax: ' . $object->thirdparty->fax . ' -  Mail: ' . $object->thirdparty->email;

 				$pdf->SetFont('','', $default_font_size);
  				$pdf->SetXY(55, 86.2);
  				$out = $outputlangs->convToOutputCharset($out);
  				$pdf->MultiCell(120, 0, $out,0,'L');

  				$pdf->SetFont('','', $default_font_size);
  				$pdf->SetXY(55, 115.7);
  				$out = $outputlangs->convToOutputCharset($this->emetteur->name);
  				$pdf->MultiCell(120, 0, $out,0,'L');

  				$object->fetchObjectLinked();
  				if(!empty($object->linkedObjects['operationorder'])){
  					$operationorder = new OperationOrder($object->db);
  					$operationorder=array_values($object->linkedObjects['operationorder'])[0];
  					$operationorder->fetch_optionals();
  					if(!empty($operationorder->array_options['options_fk_dolifleet_vehicule'])){
  						$vehicule = new doliFleetVehicule($object->db);
  						$res = $vehicule->fetch($operationorder->array_options['options_fk_dolifleet_vehicule']);
  						if($res>0){
  							$dictVT = new dictionaryVehiculeType($object->db);
  							$dictVM = new dictionaryVehiculeMark($object->db);
  							$dictVM->fetch($vehicule->fk_vehicule_mark);
  							$dictVT->fetch($vehicule->fk_vehicule_type);
  							$pdf->SetFont('','', $default_font_size);
  							$pdf->SetXY(45.5, 124.1);
  							$text = $dictVT->label . ' ' . $dictVM->label. ' - VIN: ' . $vehicule->vin . ' - Immatriculation: ' . $vehicule->immatriculation;
  							$out = $outputlangs->convToOutputCharset($text);
  							$pdf->MultiCell(150, 0, $out,0,'L');
  						}
  					}

  				}
  				$pdf->SetFont('','', $default_font_size);
  				$pdf->SetXY(44, 128.6);
  				$out = $outputlangs->convToOutputCharset($object->ref);
  				$pdf->MultiCell(150, 0, $out,0,'L');

  				$pdf->SetFont('','', $default_font_size);
  				$pdf->SetXY(40, 136.9);
  				$out = $outputlangs->convToOutputCharset(strip_tags($object->lines['0']->desc));
  				$pdf->MultiCell(150, 16.5, $out,0,'L');

  				$pdf->SetFont('','', $default_font_size + 0.3);
  				$pdf->SetXY(95.5, 154.05);
  				$out = '<b>' . $outputlangs->convToOutputCharset(' : ' . price($object->total_ht). ' € Hors Taxes') .'</b>';
  				$pdf->writeHTML ($out);

  				$pdf->SetFont('','', $default_font_size);
  				$pdf->SetXY(126.7, 195.4);
  				if(!empty($object->cond_reglement_id)){
  					$text = $object->cond_reglement;
  				}else{
  					$text = '60 Jours fin de mois';
  				}
  				$out = $outputlangs->convToOutputCharset($text);
  				$pdf->MultiCell(80, 0, $out,0,'L');

				$pdf->Close();

				$pdf->Output($file,'F');

				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;
			}
			else
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","SUPPLIERORDER_OUTPUTDIR");
			return 0;
		}
		$this->error=$langs->trans("ErrorUnknown");
		return 0;   // Erreur par defaut
	}
}

