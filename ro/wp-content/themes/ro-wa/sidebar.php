</div>
			<div class="right" id="sidebar">

<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>

				<div class="section" style="display:none">

					<div class="section-title">Cautare</div>

					<div class="section-content">

						<form method="get" action="<?php bloginfo('url'); ?>">
							<input type="text" id="s" name="s" value="" class="text" size="26" /> <input type="submit" value="Cauta" class="button" />
						</form>
					
					</div>

				</div>				
							
				<div class="section">
					<div class="section-title">Facebook</div>
						<ul class="nice-list">
							Asociatia noastra este si pe Facebook.<BR>
							Dă-ne un like si vei primi cele mai importante <BR>
							știri din comunitate direct in News Feed-ul tau. 
							</P>
							<div class="fb-like" data-href="https://www.facebook.com/rowaorg" data-width="250" 
							data-layout="standard" data-action="like" data-show-faces="true" data-share="true">
							</div>	
						</ul>
					</div>
					<P>&nbsp;</P>

					<div class="section-title">Categorii</div>

					<div class="section-content">
						<ul class="nice-list">
							<?php wp_list_categories('title_li=&show_count=1'); ?>
						</ul>
					
					</div>

				</div>

				<div class="section" style="display:none">

					<div class="section-title">Arhive</div>

					<div class="section-content">

						<ul class="nice-list">
							<?php wp_get_archives('show_post_count=1'); ?> 
						</ul>
					
					</div>

				</div>

				<div class="section" style="display:none">

					<div class="section-title">Autori</div>

					<div class="section-content">

						<ul class="nice-list">
							<?php wp_list_authors('exclude_admin=0&optioncount=1&hide_empty=1'); ?> 
						</ul>
					
					</div>

				</div>
        <div class="section" style="display:none">

          <div class="section-title">Subiecte</div>

          <div class="section-content">

            <p>
              <?php wp_tag_cloud(array('smallest'=>12, 'largest'=>18, 'unit'=>'px')); ?>
            </p>

          </div>

        </div>

        <?php endif; ?>

			</div>		

			</div>
			<div class="clearer">&nbsp;</div>

		</div>

		<?php /*  sm_subnav(); */ ?>

	</div>

	<?php sm_splash(); ?>

	<div class="main" id="main-two-columns">

		<div class="left" id="main-content">