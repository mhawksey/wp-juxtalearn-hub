<?php 
 $tabs = array(
			array('name' => __('Terminology', self::LOC_DOMAIN),
				 'id'=> 'term',
				 'description' =>
					__('Problems with use of language and scientific terms, inconsistent and overlapping terminology.', self::LOC_DOMAIN),
				 'prompt' => array( 'term01' =>
					__('One scientific term has a different meaning depending on the context it is used in. e.g. volts and voltage in Physics. Use of the term kinetic energy in both Physics and in Biology.', self::LOC_DOMAIN),
									'term02' =>
					__('Different terms are used to refer to the same concept. e.g. voltage is also referred to as potential difference. Confusion between voltage and charge.', self::LOC_DOMAIN),
									'term03' =>
					__('Reuse of everyday terms that students believe they understand in a scientific context. e.g. the "drop" part of "forward voltage drop", "work done" in physics.', self::LOC_DOMAIN),
								   ),
				 ),
			array('name' => __('Incomplete Pre-Knowledge', self::LOC_DOMAIN),
				 'id'=> 'pre',
				 'description' =>
					__('Previous understandings that need to be unlearned, modified or improved to understand the Tricky Topic', self::LOC_DOMAIN),
				 'prompt' => array( 'pre01' =>
					__('Simplistic understandings that need to be unlearned e.g. imagining atomic structure as balls on sticks suggests space between atoms.', self::LOC_DOMAIN),
									'pre02' =>
					__('Understanding  that the student is expected to know already. e.g. to do the calculations related to Avogadro\'s number in Chemistry assumes a math understanding of powers of ten and ratios.', self::LOC_DOMAIN),
								   ),
				 ),
			array('name' => __('Essential concepts', self::LOC_DOMAIN),
				 'id'=> 'esn',
				 'description' =>
					__('Key assumptions and knowledge that relate to the tricky topic, without which it is impossible to understand it', self::LOC_DOMAIN),
				 'prompt' => array( 'esn01' =>
					__('Knowledge is required in order to understand the Stumbling Block. e.g. to understand genetic drift, a student needs to already know about natural selection.', self::LOC_DOMAIN),
									'esn02' =>
					__('Complementary knowledge the student needs to learn alongside the stumbling block. Understanding genetic drift involves learning about its causes; founder effect and bottleneck effect.', self::LOC_DOMAIN),
								  ),
 					),
			array('name' => __('Intuitive Beliefs', self::LOC_DOMAIN),
 				  'id'=> 'bel',
				 'description' =>
					__('Informal, intuitive ways of thinking about the world. Strongly biased toward causal explanations', self::LOC_DOMAIN),
				 'prompt' => array('bel01' =>
					__('Human-Like or world like analogy. Viewing scientific concepts in terms of everyday phenomena e.g. males of any species are bigger than females. Plants suck up food from soil thru roots. Analogy based on metaphor that doesn\'t carry through e.g. "Stage" and "Costume" used in Sense programming.', self::LOC_DOMAIN),
								   'bel02' =>
					__('The belief that if one condition is fulfilled, then the object is automatically a member of a group. One unobservable core feature defines membership of a category eg: one to one relationship between DNA and physical traits. Birds have wings therefore all creatures with wings are birds.', self::LOC_DOMAIN),
									'bel03' =>
					__('Reasoning based on the assumption of goal or purpose eg birds have wings so they can fly. Genes turn off so that cell can develop properly. Inappropriate assumption of cause and effect, eg release an object along a curved path and it will continue in a curve, rocks are pointy so animals won\'t sit on them and crush them. ', self::LOC_DOMAIN),
								   ),
 					),
  			); 
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