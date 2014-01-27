<?php 
 $tabs = array(
			array('name' => 'Terminology',
				 'id'=> 'term',
				 'description' => 'Problems with use of language and scientific terms, inconsistent and overlapping terminology.',
				 'prompt' => array( 'term01' => 'One scientific term has a different meaning depending on the context it is used in. e.g. volts and voltage in Physics. Use of the term kinetic energy in both Physics and in Biology.',
				 					'term02' => 'Different terms are used to refer to the same concept. e.g. voltage is also referred to as potential difference. Confusion between voltage and charge.',
									'term03' => 'Reuse of everyday terms that students believe they understand in a scientific context. e.g. the "drop" part of "forward voltage drop", "work done" in physics.',
								   ),
				 ),
			array('name' => 'Incomplete Pre-Knowledge',
				 'id'=> 'pre',
				 'description' => 'Previous understandings that need to be unlearned, modified or improved to understand the Tricky Topic',
				 'prompt' => array( 'pre01' => 'Simplistic understandings that need to be unlearned e.g. imagining atomic structure as balls on sticks suggests space between atoms. ',
				 					'pre02' => 'Understanding  that the student is expected to know already. e.g. to do the calculations related to Avogadro\'s number in Chemistry assumes a math understanding of powers of ten and ratios.',
								   ),
				 ),
			array('name' => 'Essential concepts',
				 'id'=> 'esn',
				 'description' => 'Key assumptions and knowledge that relate to the tricky topic, without which it is impossible to understand it',
				 'prompt' => array( 'esn01' => 'Knowledge is required in order to understand the Stumbling Block. e.g. to understand genetic drift, a student needs to already know about natural selection.',
				 					'esn02' => 'Complementary knowledge the student needs to learn alongside the stumbling block. Understanding genetic drift involves learning about its causes; founder effect and bottleneck effect. ',
								  ),
 					),
  			array('name' => 'Intuitive Beliefs',
 				  'id'=> 'bel',
				 'description' => 'Informal, intuitive ways of thinking about the world. Strongly biased toward causal explanations',
				 'prompt' => array('bel01' => 'Human-Like or world like analogy. Viewing scientific concepts in terms of everyday phenomena e.g. males of any species are bigger than females. Plants suck up food from soil thru roots. Analogy based on metaphor that doesn\'t carry through e.g. "Stage" and "Costume" used in Sense programming.',
				 				   'bel02' => 'The belief that if one condition is fulfilled, then the object is automatically a member of a group. One unobservable core feature defines membership of a category eg: one to one relationship between DNA and physical traits. Birds have wings therefore all creatures with wings are birds. ',
				 					'bel03' => 'Reasoning based on the assumption of goal or purpose eg birds have wings so they can fly. Genes turn off so that cell can develop properly. Inappropriate assumption of cause and effect, eg release an object along a curved path and it will continue in a curve, rocks are pointy so animals won\'t sit on them and crush them. ',
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
  	
        <div class="tabs-check">Please tick all that apply:
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