<?php
/*********************************************************************
    templates.php

    Email Templates

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.template.php');

$template=null;
if($_REQUEST['id'] && !($template=Template::lookup($_REQUEST['id'])))
    $errors['err']=__('Unknown or invalid template ID.');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'updatetpl':
            if(!$template){
                $errors['err']=__('Unknown or invalid template');
            }elseif($template->updateMsgTemplate($_POST,$errors)){
                $template->reload();
                $msg=__('Message template updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=__('Error updating message template. Try again!');
            }
            break;
        case 'update':
            if(!$template){
                $errors['err']=__('Unknown or invalid template');
            }elseif($template->update($_POST,$errors)){
                $msg=__('Template updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=__('Error updating template. Try again!');
            }
            break;
        case 'add':
            if((Template::create($_POST,$errors))){
                $msg=__('Template added successfully');
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=__('Unable to add template. Correct error(s) below and try again.');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=__('You must select at least one template to process.');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.EMAIL_TEMPLATE_TABLE.' SET isactive=1 '
                            .' WHERE tpl_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg = __('Selected templates enabled');
                            else
                                $warn = sprintf(__('%1$d of %2$d selected templates enabled'), $num, $count);
                        } else {
                            $errors['err'] = __('Unable to enable selected templates');
                        }
                        break;
                    case 'disable':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=Template::lookup($v)) && !$t->isInUse() && $t->disable())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg = __('Selected templates disabled');
                        elseif($i)
                            $warn = sprintf(__('%1$d of %2$d selected templates disabled (in-use templates can\'t be disabled)'), $i, $count);
                        else
                            $errors['err'] = __("Unable to disable selected templates (in-use or default template can't be disabled)");
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=Template::lookup($v)) && !$t->isInUse() && $t->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = __('Selected templates deleted successfully');
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d selected templates deleted'), $i, $count);
                        elseif(!$errors['err'])
                            $errors['err'] = __('Unable to delete selected templates');
                        break;
                    default:
                        $errors['err']=__('Unknown template action');
                }
            }
            break;
        default:
            $errors['err']=__('Unknown action');
            break;
    }
}

$page='templates.inc.php';
if($template && !strcasecmp($_REQUEST['a'],'manage')){
    $page='tpl.inc.php';
}elseif($template || !strcasecmp($_REQUEST['a'],'add')){
    $page='template.inc.php';
}

$nav->setTabActive('emails');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
