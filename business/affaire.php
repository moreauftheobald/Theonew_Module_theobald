<?php
/* Copyright (C) 2020 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require '../config.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
dol_include_once('theobald/class/affaire.class.php');
dol_include_once('commande/class/commande.class.php');

global $user,$langs,$db,$hookmanager,$conf;
if(empty($user->rights->theobald->affaires->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('theobald@theobald');

$fk_soc = GETPOST('fk_soc', 'int');
$search_fk_user_creat = GETPOST('search_fk_user_creat', 'int');
$search_by=GETPOST('search_by', 'alpha');
if (!empty($search_by)) {
	$sall=GETPOST('sall');
	if (!empty($sall)) {
		$_GET[$search_by]=$sall;
	}
}

$massaction = GETPOST('massaction', 'alpha');
$action = GETPOST('action', 'alpha');
$confirmmassaction = GETPOST('confirmmassaction', 'alpha');
$toselect = GETPOST('toselect', 'array');
$object = new  Affaire($db);
$dictGAM = new GammeDictType($db);
$disp = 'none';
$hookmanager->initHooks(array('affairelist'));

/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions', $parameters, $object, $action);
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
if($action == 'createaffaire'){
    $error =0;
    if(!isset($_POST['fk_soc'])){
        $fk_soc_c= GETPOST('fk_soc_c', 'int');
    }else{
        $fk_soc_c=$fk_soc;
    }
    $fk_product = GETPOST('fk_product', 'int');
    $status = GETPOST('status', 'int');
    $qty=GETPOST('qty', 'int');
    $fk_c_gamme = GETPOST('fk_c_gamme', 'int');
    if($fk_product<1)$error++;
    if($fk_soc<1)$error++;
    if(!array_key_exists ($status,Affaire::$TStatus))$error++;
    if($qty<1)$error++;
    if(!array_key_exists ($fk_c_gamme,$dictGAM->getAllActiveArray('label')))$error++;
    if(empty($user->rights->theobald->affaires->write))$error++;
    if($error<1){
        $afftocreate = New Affaire($db);
        $afftocreate->qty = $qty;
        $afftocreate->fk_soc = $fk_soc_c;
        $afftocreate->fk_c_gamme = $fk_c_gamme;
        $afftocreate->fk_product = $fk_product;
        $afftocreate->fk_user_creat = $user->id;
        $afftocreate->fk_user_modif = $user->id;
        $afftocreate->status = $status;
        $afftocreate->date_creation = $db->idate(dol_now());
        $res = $afftocreate->create($user);
        if($res<0){
            setEventMessage($langs->trans('AffairecreateError', $afftocreate->id), 'errors');
        }
    }else{
        setEventMessage($langs->trans('Affairecreatebadvalue', $afftocreate->id), 'errors');
    }
}


if (!empty($confirmmassaction) && $massaction != 'presend' && $massaction != 'confirm_presend'){
    if($massaction == 'SetRunning' && !empty($toselect)){
        $val=array('status'=>2);
        foreach ($toselect as $workId){
            $objectToWork = new Affaire($db);
            $res = $objectToWork->fetch($workId);
            if($res>0){
                if($objectToWork->setValues($val)<0){
                    setEventMessage($langs->trans('AffaireSetrunningError', $objectToWork->id), 'errors');
                }else{
                    $objectToWork->update($user);
                    setEventMessage($langs->trans('AffaireUpdatedr', $objectToWork->id));
                }
            }else{
                setEventMessage($langs->trans('AffaireNotFound'), 'warnings');
            }
        }
    }
    if($massaction == 'SetPending' && !empty($toselect)){
            $val=array('status'=>5);
            foreach ($toselect as $workId){
                $objectToWork = new Affaire($db);
                $res = $objectToWork->fetch($workId);
                if($res>0){              
                    if($objectToWork->setValues($val)<0){
                        setEventMessage($langs->trans('AffaireSetrunningError', $objectToWork->id), 'errors');
                    }else{
                        $objectToWork->update($user);
                        setEventMessage($langs->trans('AffaireUpdatedr', $objectToWork->id));
                    }
                }else{
                    setEventMessage($langs->trans('AffaireNotFound'), 'warnings');
                }
            }
        }
    
    if($massaction == 'SetWon' && !empty($toselect)){
        $val=array('status'=>4);
        foreach ($toselect as $workId){
            $objectToWork = new Affaire($db);
            $res = $objectToWork->fetch($workId);
            if($res>0){
                if($objectToWork->setValues($val)<0) {
                    setEventMessage($langs->trans('AffaireSetrunningError', $objectToWork->id), 'errors');
                }else{
                    $objectToWork->update($user);
                    setEventMessage($langs->trans('AffaireUpdatedr', $objectToWork->id));
                }
            }else{
                setEventMessage($langs->trans('AffaireNotFound'), 'warnings');
            }
        }
    }
    
    if($massaction == 'SetLost' && !empty($toselect)){
        $val=array('status'=>8);
        foreach ($toselect as $workId){
            $objectToWork = new Affaire($db);
            $res = $objectToWork->fetch($workId);
            if($res>0){
                if($objectToWork->setValues($val)<0){
                    setEventMessage($langs->trans('AffaireSetrunningError', $objectToWork->id), 'errors');
                }else{
                    $objectToWork->update($user);
                    setEventMessage($langs->trans('AffaireUpdatedr', $objectToWork->id));
                }
            }else{
                setEventMessage($langs->trans('AffaireNotFound'), 'warnings');
            }
        }
    }
    
    if($massaction == 'delete' && !empty($toselect)){
        foreach ($toselect as $workId){
            $objectToWork = new Affaire($db);
            $res = $objectToWork->fetch($workId);
            if($res>0){
                if($objectToWork->delete($user)<0){
                    setEventMessage($langs->trans('AffaireSetrunningError', $objectToWork->id), 'errors');
                }else{
                    setEventMessage($langs->trans('Affairedeleted', $objectToWork->id));
                }
            }else{
                setEventMessage($langs->trans('AffaireNotFound'), 'warnings');
            }
        }
    }
    
    if($massaction == 'Update' && !empty($toselect) && $action == 'Updateconfirm'){
        $error =0;
        $fk_soc_sel = GETPOST('fk_soc_c', 'int');
        $fk_product_sel = GETPOST('fk_product', 'int');
        $qty_sel=GETPOST('qty', 'int');
        $fk_c_gamme_sel = GETPOST('fk_c_gamme', 'int');
        if($fk_product_sel<1)$error++;
        if($fk_soc_sel<1)$error++;
        if($qty_sel<1)$error++;
        if(!array_key_exists ($fk_c_gamme_sel,$dictGAM->getAllActiveArray('label')))$error++;
        if(empty($user->rights->theobald->affaires->write))$error++;
        if($error<1){
            foreach ($toselect as $id){
                $toupdate = New Affaire($db);
                $toupdate->fetch($id);
                $toupdate->fk_soc = $fk_soc_sel;
                $toupdate->fk_product = $fk_product_sel;
                $toupdate->qty = $qty_sel;
                $toupdate->fk_c_gamme = $fk_c_gamme_sel;
                $res=$toupdate->update($user);
                if($res<0){
                    setEventMessage($langs->trans('AffaireupdateError', $afftocreate->id), 'errors');
                } 
            }
        }else{
            setEventMessage($langs->trans('Affaireupdatebadvalue', $afftocreate->id), 'errors');
        }
    }

    if($massaction == 'Update' && !empty($toselect) && !$action == 'Updateconfirm'){
        $action = "Update";
        $disp ='';
        $fk_soc_sel=-1;
        $fk_c_gamme_sel = -1;
        $fk_product_sel = -1;
        $qty_sel = -1;
        foreach ($toselect as $id){
            $toupdate = New Affaire($db);
            $toupdate->fetch($id);
            if($fk_soc_sel==-1){
                $fk_soc_sel = $toupdate->fk_soc;
            }else{
                if($fk_soc_sel<>$toupdate->fk_soc)$fk_soc_sel = -2;
            }
            if($fk_c_gamme_sel==-1){
                $fk_c_gamme_sel = $toupdate->fk_c_gamme;
            }else{
                if($fk_c_gamme_sel<>$toupdate->fk_c_gamme)$fk_c_gamme_sel = -2;
            }
            if($fk_product_sel==-1){
                $fk_product_sel = $toupdate->fk_product;
            }else{
                if($fk_product_sel<>$toupdate->fk_product)$fk_product_sel = -2;
            }
            if($qty_sel==-1){
                $qty_sel = $toupdate->qty;
            }else{
                if($qty_sel<>$toupdate->qty)$qty = -2;
            }
        }
    }
}

/*
 * View
 */

llxHeader('', $langs->trans('AffairesList'), '', '');

if ($fk_soc > 0){
    $search_fk_soc = $fk_soc;
    $soc_affaire = new Societe($db);
    $soc_affaire->fetch($fk_soc);
    $head = societe_prepare_head($soc_affaire);
    $picto = 'mymodule@theobald';
    dol_fiche_head($head, 'business', $langs->trans('affaires'), -1, $picto);
    $linkback = '<a href="'.dol_buildpath('/societe/list.php', 1).'?restore_lastsearch_values=1'.(!empty($fk_soc) ? '&fk_soc='.$fk_soc : '').'">'.$langs->trans("BackToList").'</a>';
    dol_banner_tab($soc_affaire, 'socid', $linkback, ($user->socid ? 0 : 1), 'rowid', 'nom');
}

if(!empty($user->rights->theobald->affaires->read_own)){
    $search_fk_user_creat = $user->id;
}

$formconfirm = '';

$parameters = array('formConfirm' => $formconfirm);
$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
if (empty($reshook)) $formconfirm .= $hookmanager->resPrint;
elseif ($reshook > 0) $formconfirm = $hookmanager->resPrint;

// Print form confirm
print $formconfirm;

$sql = 'SELECT *';

// Add fields from hooks
$parameters=array('sql' => $sql);
$reshook=$hookmanager->executeHooks('printFieldListSelect', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;

$sql.= ' FROM '.MAIN_DB_PREFIX.$object->table_element.' t ';
$sql.= ' WHERE 1=1';
$sql.= ' AND t.entity IN ('.getEntity('theobald', 1).')';
if (!empty($fk_soc) && $fk_soc > 0) $sql.= ' AND t.fk_soc = '.$fk_soc;
if (!empty($search_fk_soc) && $search_fk_soc > 0) $sql.= ' AND t.fk_soc = '.$search_fk_soc;
if (!empty($search_fk_user_creat) && $search_fk_user_creat > 0) $sql.= ' AND t.fk_user_creat = '.$search_fk_user_creat;
// Add where from hooks
$parameters=array('sql' => $sql);
$reshook=$hookmanager->executeHooks('printFieldListWhere', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;

$form = new Form($db);

$nbLine = GETPOST('limit');
if (empty($nbLine)) $nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

// configuration listView

foreach ($object->fields as $fieldKey => $infos)
{
	if (isset($infos['label']) && $infos['visible'] > 0) $TTitle[$fieldKey] = $langs->trans($infos['label']);
}
if(!empty($user->rights->theobald->affaires->write)){
    $urlaction = '<a href="" onclick="javascript:visibilite(\'create\'); return false;" >'. img_edit_add('+','') . '</a>';
    if($action=='Update') $urlaction = '';
    print load_fiche_titre($langs->trans('NewAffaire').' '. $urlaction,'','title_generic.png');
    print '<div id="create" style="display: '.$disp.';">';
    print '<form name="createaffaire" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
    print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
    print '<input type="hidden" name="fk_soc" value="' . $fk_soc . '">';
    if($action=='Update'){
        print '<input type="hidden" name="action" value="Updateconfirm">';
        print '<input type="hidden" name="massaction" value="' . $massaction . '">';
        print '<input type="hidden" name="confirmmassaction" value="' . $confirmmassaction . '">';
        foreach($toselect as $selected){
            print '<input type="hidden" name="toselect[]" value="'.$selected.'">';
        }
    }else{
        print '<input type="hidden" name="action" value="createaffaire">';
    }
    print '<table class="border tableforfield" width="100%">';
    print '<tr class="liste_titre ">' ;
    print '<th class="liste_titre">'. $langs->trans('Qty') .'</th>';
    if (!$fk_soc > 0||$action == 'Update'){
        print '<th class="liste_titre">'. $langs->trans('ThirdParty') .'</th>';
    }
    print '<th class="liste_titre">'. $langs->trans('Gamme') .'</th>';
    print '<th class="liste_titre">'. $langs->trans('Produit') .'</th>';
    if (!$action == 'Update'){
        print '<th class="liste_titre">'. $langs->trans('Status') .'</th>';
    }
    print '<th class="liste_titre">'. $langs->trans('Action') .'</th>';
    print '</tr>';
    print '<tr class="oddeven">' ;
    print '<td>';
    print '<input type="text" name="qty" size="3" value=' . (($action=='Update' && $qty_sel>0)?$qty_sel:GETPOST('qty','int')) . '>';
    print '</td>';
    if (!$fk_soc > 0||$action == 'Update'){
        print '<td>';
        print $form->select_company((($action=='Update' && $fk_soc_sel > 0)?$fk_soc_sel:GETPOST('fk_soc_c','int')), 'fk_soc_c','',1);
        print '</td>';
    }
    print '<td>';
    print $form->selectArray('fk_c_gamme', $dictGAM->getAllActiveArray('label'),(($action=='Update' && $fk_c_gamme_sel > 0)?$fk_c_gamme_sel:GETPOST('fk_c_gamme','int')),1);
    print '</td>';
    print '<td>';
    print $form->select_produits((($action=='Update' && $fk_product_sel > 0)?$fk_product_sel:GETPOST('fk_product','int')), 'fk_product',0,0,0,1,2,'',3,array(),0,1,0,'',0,'',array(),1);
    print '</td>';
    if (!$action == 'Update'){
        print '<td>';
        print $form->selectArray('status', Affaire::$TStatus,GETPOST('status','int'),0,0,0,'',1);
        print '</td>';
    }
    print '<td>';
    print '<input type="submit" align="center" class="button" value="' . $langs->trans('Save') . '" name="save" id="save"/>';
    print '</td>';
    print '</tr>';
    print '</table>';
    print '</form>';
    print '</div>';
    if($action=='Update'){
        ?>
		<script type="text/javascript">
		$(document).ready(function () {
			<?php foreach($toselect as $selected){?>
			$("#cb<?php echo $selected; ?>").attr("checked", "checked");
			<?php }?>
		});
		</script>
		<?php
    }
}

$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_affaire', 'POST');

$TTitle['status'] = $langs->trans('Status');

$listViewConfig = array(
	'view_type' => 'list' // default = [list], [raw], [chart]
	,'allow-fields-select' => true
	,'limit'=>array(
		'nbLine' => $nbLine
	)
	,'list' => array(
		'title' => $langs->trans('AffairesList')
		,'image' => 'title_generic.png'
		,'picto_precedent' => '<'
		,'picto_suivant' => '>'
		,'noheader' => 0
		,'messageNothing' => $langs->trans('NoAffaire')
		,'picto_search' => img_picto('', 'search.png', '', 0)
		,'massactions'=>array(
		    'Update'     => $langs->trans('Update'),
			'SetRunning' => $langs->trans('SetRunning'),
		    'SetPending' => $langs->trans('SetPending'),
		    'SetWon'     => $langs->trans('SetWon'),
		    'SetLost'    => $langs->trans('SetLost'),
		    'delete'     => $langs->trans('Delete')
		)
		,'param_url' => '&limit='.$nbLine.'&fk_soc'.$fk_soc
	    //,'morehtmlrighttitle' => '<b>test</b>'
	)
	,'subQuery' => array()
	,'link' => array()
	,'type' => array(
		'date_creation' => 'date' // [datetime], [hour], [money], [number], [integer]
		,'tms' => 'date'
		,'date_immat'=>'date'
	)
	,'search' => array(
		'qty' => array('search_type' => true, 'table' => 't', 'field' => 'qty')
	    ,'fk_soc' => array('search_type' => 'override', 'override'=> $form->select_company(GETPOST('search_fk_soc','int'), 'search_fk_soc','',1))
	    ,'fk_c_gamme' => array('search_type' => $dictGAM->getAllActiveArray('label'))
	    ,'fk_product' => array('search_type' => 'override', 'override'=> $form->select_produits(GETPOST('search_fk_product','int'), 'search_fk_product',0,0,0,1,2,'',1,array(),0,1,0,'',0,'',array(),1))
	    ,'status' => array('search_type' => Affaire::$TStatus, 'to_translate' => true)
	    ,'date_creation' => array('search_type' => 'calendars', 'allow_is_null' => false)
	    ,'fk_user_creat' => array('search_type' => 'override', 'override'=> $form->select_dolusers($search_fk_user_creat, 'fk_user_creat',1))
	    ,'tms' => array('search_type' => 'calendars', 'allow_is_null' => false)
	)
	,'translate' => array()
	,'hide' => array(
		'rowid',// important : rowid doit exister dans la query sql pour les checkbox de massaction
	)
	,'title'=>$TTitle
	,'eval'=>array(
	    'fk_soc'			=> '_getSocieteNomUrl("@val@")'
		,'fk_c_gamme' => '_getValueFromId("@val@", "GammeDictType")'
		,'fk_product' => '_getProductNomUrl("@val@")'
	    ,'fk_commande' => '_getCommandeNomUrl("@val@")'
	    ,'fk_user_creat' => '_getUserNomUrl("@val@")'
	    ,'fk_user_modif' => '_getUserNomUrl("@val@")'
		,'status' => 'Affaire::LibStatut("@val@", 5)' 
	)
);


$r = new Listview($db, 'affaire');

// Change view from hooks
$parameters=array(  'listViewConfig' => $listViewConfig);
$reshook=$hookmanager->executeHooks('listViewConfig',$parameters,$r);    // Note that $action and $object may have been modified by hook
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
if ($reshook>0)
{
    $listViewConfig = $hookmanager->resArray;
}
if ($fk_soc > 0){
    unset ($listViewConfig['search']['fk_soc']);
}

if(!empty($user->rights->theobald->affaires->read_own)){
    unset ($listViewConfig['search']['fk_user_creat']);
}

if(empty($user->rights->theobald->affaires->delete)){
    unset ($listViewConfig['list']['massactions']['delete']);
}
print '<input type="hidden" name="fk_soc" value="'.$fk_soc.'"/>';
echo $r->render($sql, $listViewConfig);

$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

$formcore->end_form();

?>
<script type="text/javascript">
function visibilite(thingId) {
	var targetElement;
	targetElement = document.getElementById(thingId) ;
	if (targetElement.style.display == "none") {
		targetElement.style.display = "" ;
	} else {
		targetElement.style.display = "none" ;
	}
}
</script>
<?php


llxFooter('');
$db->close();


function _getSocieteNomUrl($fk_soc)
{
	global $db;

	$soc = new Societe($db);
	if ($soc->fetch($fk_soc) > 0)
	{
		return $soc->getNomUrl(1);
	}

	return '';
}

function _getProductNomUrl($fk_product)
{
    global $db;
    
    $product = new Product($db);
    if ($product->fetch($fk_product) > 0)
    {
        return $product->getNomUrl(1);
    }
    
    return '';
}

function _getCommandeNomUrl($fk_commande)
{
    global $db;
    
    $commande = new Commande($db);
    if ($commande->fetch($fk_commande) > 0)
    {
        return $commande->getNomUrl(1);
    }
    
    return '';
}

function _getUserNomUrl($fk_user)
{
    global $db;
    
    $util = new User($db);
    if ($util->fetch($fk_user) > 0)
    {
        return $util->getNomUrl(1);
    }
    
    return '';
}

function _getValueFromId($id, $dictionaryClassname)
{
	global $db;

	if (class_exists($dictionaryClassname))
	{
		$dict = new $dictionaryClassname($db);
		return $dict->getValueFromId($id, 'label');
	}
	else return '';
}
