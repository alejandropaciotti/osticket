<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info=array();
$qstr='';
if($dept && $_REQUEST['a']!='add') {
    //Editing Department.
    $title=__('Update Department');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$dept->getInfo();
    $info['id']=$dept->getId();
    $info['groups'] = $dept->getAllowedGroups();

    $qstr.='&id='.$dept->getId();
} else {
    $title=__('Add New Department');
    $action='create';
    $submit_text=__('Create Dept');
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $info['ticket_auto_response']=isset($info['ticket_auto_response'])?$info['ticket_auto_response']:1;
    $info['message_auto_response']=isset($info['message_auto_response'])?$info['message_auto_response']:1;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="departments.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo __('Department');?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?php echo __('Department Information');?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?php echo __('Name:');?>
            </td>
            <td>
                <input type="text" size="30" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('Type:');?>
            </td>
            <td>
                <input type="radio" name="ispublic" value="1" <?php echo $info['ispublic']?'checked="checked"':''; ?>><strong><?php echo __('Public');?></strong>
                <input type="radio" name="ispublic" value="0" <?php echo !$info['ispublic']?'checked="checked"':''; ?>><strong><?php echo __('Private');?></strong> <?php echo __('(Internal)');?>
                &nbsp;<span class="error">*&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('Email:');?>
            </td>
            <td>
                <select name="email_id">
                    <option value="0">&mdash; <?php echo __('Select Department Email');?> &mdash;</option>
                    <?php
                    $sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' email ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$email,$name)=db_fetch_row($res)){
                            $selected=($info['email_id'] && $id==$info['email_id'])?'selected="selected"':'';
                            if($name)
                                $email=Format::htmlchars("$name <$email>");
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$email);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('Template:');?>
            </td>
            <td>
                <select name="tpl_id">
                    <option value="0">&mdash; <?php echo __('System default');?> &mdash;</option>
                    <?php
                    $sql='SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_TABLE.' tpl WHERE isactive=1 ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['tpl_id'] && $id==$info['tpl_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['tpl_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('SLA:');?>
            </td>
            <td>
                <select name="sla_id">
                    <option value="0">&mdash; <?php echo __('System default');?> &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['sla_id']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['sla_id']; ?></span>
            </td>
        </tr>
        <?php
        if($dept && $dept->getNumUsers()){ ?>
        <tr>
            <td width="180" class="required">
                <?php echo __('Manager:');?>
            </td>
            <td>
                <select name="manager_id">
                    <option value="0">&mdash; <?php echo __('None');?> &mdash;</option>
                    <option value="0" disabled="disabled"><?php echo __('Select Department Manager (Optional)');?></option>
                    <?php
                    $sql='SELECT staff_id,CONCAT_WS(", ",lastname, firstname) as name '
                        .' FROM '.STAFF_TABLE.' staff '
                        .' ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['manager_id'] && $id==$info['manager_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['manager_id']; ?></span>
            </td>
        </tr>
        <?php 
        } ?>

        <tr>
            <td width="180">
                <?php echo __('Group Membership:');?>
            </td>
            <td>
                <input type="checkbox" name="group_membership" value="0" <?php echo $info['group_membership']?'checked="checked"':''; ?> >
                <?php echo __('Extend membership to groups with access.');?> <i>( <?php echo __('Alerts and  notices will include groups');?>)</i>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Auto Response Settings');?></strong>: <?php echo __('Overwrite global auto-response settings for tickets routed to the Dept.');?></em>
            </th>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('New Ticket:');?>
            </td>
            <td>
                <input type="checkbox" name="ticket_auto_response" value="0" <?php echo !$info['ticket_auto_response']?'checked="checked"':''; ?> >
                
                <?php echo __('<strong>Disable</strong> new ticket auto-response for this Dept.');?>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('New Message:');?>
            </td>
            <td>
                <input type="checkbox" name="message_auto_response" value="0" <?php echo !$info['message_auto_response']?'checked="checked"':''; ?> >
                    <?php echo __('<strong>Disable</strong> new message auto-response for this Dept.');?>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('Auto Response Email:');?>
            </td>
            <td>
                <select name="autoresp_email_id">
                    <option value=""  disabled="disabled"><?php echo __('Select Outgoing  Email');?></option>
                    <option value="0">&mdash; <?php echo __('Department Email (Above)');?> &mdash;</option>
                    <?php
                    $sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' email ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$email,$name)=db_fetch_row($res)){
                            $selected=($info['email_id'] && $id==$info['email_id'])?'selected="selected"':'';
                            if($name)
                                $email=Format::htmlchars("$name <$email>");
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$email);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['autoresp_email_id']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Department Access');?></strong>: <?php echo __('Check all groups allowed to access this department.');?></em>
            </th>
        </tr>
        <tr><td colspan=2><em><?php echo __('Department manager and primary members will always have access independent of group selection or assignment.');?></em></td></tr>
        <?php
         $sql='SELECT group_id, group_name, count(staff.staff_id) as members '
             .' FROM '.GROUP_TABLE.' grp '
             .' LEFT JOIN '.STAFF_TABLE. ' staff USING(group_id) '
             .' GROUP by grp.group_id '
             .' ORDER BY group_name';
         if(($res=db_query($sql)) && db_num_rows($res)){
            while(list($id, $name, $members) = db_fetch_row($res)) {
                if($members>0) 
                    $members=sprintf('<a href="staff.php?a=filter&gid=%d">%d</a>', $id, $members);

                $ck=($info['groups'] && in_array($id,$info['groups']))?'checked="checked"':'';
                echo sprintf('<tr><td colspan=2>&nbsp;&nbsp;<label><input type="checkbox" name="groups[]" value="%d" %s>&nbsp;%s</label> (%s)</td></tr>',
                        $id, $ck, $name, $members);
            }
         }
        ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Department Signature');?></strong>: <?php echo __('Optional signature used on outgoing emails.');?> &nbsp;<span class="error">&nbsp;<?php echo $errors['signature']; ?></span></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea name="signature" cols="21" rows="5" style="width: 60%;"><?php echo $info['signature']; ?></textarea>
                <br><em><?php echo __('Signature is made available as a choice, for public departments, on ticket reply.');?></em>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
    <input type="button" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="departments.php"'>
</p>
</form>
