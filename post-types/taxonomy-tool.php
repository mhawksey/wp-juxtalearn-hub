<?php
  $tabs = Student_Problem_Template::get_tax_tool_tabs();
?>
<div id="juxtalearn_hub_tax_tabs">
  <ul>
  <?php foreach ($tabs as $tab){ ?>
    <li><a href="#tabs-<?php echo $tab['id']; ?>"><?php echo $tab['name']; ?></a></li>
  <?php } ?>
  </ul>
  <?php foreach ($tabs as $tab){ ?>
  <?php $desc = '<div class="tax_term '.$tab['id'].'desc"><strong>'.$tab['name'].'</strong><br/>'.$tab['description'].'</div>'; ?>
  <div id="tabs-<?php echo $tab['id']; ?>" class="tax-row">
  	
        <div class="tabs-check"><?php echo
            __('Please tick all that apply:', self::LOC_DOMAIN) ?>
        <?php foreach ($tab['prompt'] as $id => $val){ ?>
        <?php 	$name = "juxtalearn_hub_$id";
                $value = get_post_meta( $post->ID, $name, true );
                $desc .= '<div class="tax_term juxtalearn_hub_'.$id.'">'.$val.'</div>';
                ?>
                <div><input
                    type="checkbox"
                    name="<?php echo $name; ?>"
                    id="<?php echo $name; ?>"
                    <?php if ($value) echo 'checked'; ?>
                /><label for="<?php echo $name; ?>"  class="eh_label"><?php echo $sub_options[$id]['label']; ?></label></div>
        
        <?php } //end of $tab['prompt'] loop ?>
        </div>
    <div class="tabs-text-col">
    	<div class="tabs-text <?php echo $tab['id']; ?>"><?php echo $desc; ?></div>
    </div>
    </div>
  <?php } // end of $tabs loop ?>
  </div>
<br style="clear:both"/>