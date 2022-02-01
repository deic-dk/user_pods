<div id="app-content">
	<div id="app-content-kubernetes" class="viewcontainer">
	<div class="info">
	Notice: Currently, pods are in beta testing. Use at your own risk - pods may be terminated or restarted.
	We appreciate <a href="mailto:cloud@deic.dk">feedback.</a></div>
		<div id="controls">
			<div class="row">
				<div class="text-right">
					<div class="actions creatable">
						<div id="loading">
							<div id="loading-text">
								<?php $l = OC_L10N::get('user_pods');
								p($l->t("Working...")); ?>
							</div>
							<div class="icon-loading-dark"></div>
						</div>
						<div id="create" title="">
							<a id="pod-create" class="btn btn-primary btn-flat" href="#">
								<?php
								p($l->t("New pod "));
								?>
							</a>
				 </div>
				</div>
			</div>
			</div>
			<div id="newpod" class="apanel">
				<span class="spanpanel" ><?php p($l->t("Manifest")); ?>:
					<select id="yaml_file" title=<?php p($l->t("YAML file")); ?>>
						<?php
						echo "<option value=''></option>";
						foreach ($_['manifests'] as $manifest) {
							echo "<option value='".$manifest."'".
								(!empty($_REQUEST['yaml_file'])&&$_REQUEST['yaml_file']==$manifest?" selected='selected'":"").
								">".$manifest."</option>";
						}
						?>
					</select>
				</span>
				<span id="links"></span>
				<span class="newpod-span">	
					<div id="ok" class="btn-pod" original-title="">
						<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Apply")); ?></a>
					</div>
					<div id="cancel" class="btn-pod" original-title="">
						<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Cancel")); ?></a>
					</div>
				</span>
				<div id="description"></div>
				<div id="ssh"><textarea id="public_key" type="text" placeholder="<?php p($l->t("Public SSH key")); ?>"
					title="<?php p($l->t("Paste your public SSH key here")); ?>"></textarea></div>
				<div id="storage">
				</div>
				<div id="file"><span id="file_text"><?php p($l->t("File")); ?>:</span>
					<input id="file_input" type="text" placeholder="<?php p($l->t("Optional file to open")); ?>"
					title="<?php p($l->t("Path of file for to open in your pod")); ?>"
					value="<?php echo(empty($_REQUEST['file'])?$_REQUEST['file']:''); ?>">
				</div>
			</div>
		</div> 
	</div>
	<h2 class="running_pods"><?php p($l->t("Running pods/containers")); ?>
	<a id="pods_refresh" class="btn btn-default" title="<?php p($l->t("Refresh")); ?>">&#8634;</a></h2>
	<div id="running_pods">
	<table id="podstable" class="panel">
		<thead class="panel-heading" >
			<tr>
			</tr>
		</thead>
		<tbody id='fileList'>
		</tbody> 
		<tfoot
			<tr class="summary text-sm">
				<td>
					<span class="info" containers="0">
					</span>
				</td>
			</tr>
		</tfoot>
	</table>
	</div>
</div>
<div id='dialogalert' title='<?php p($l->t("Delete confirmation")); ?>'>
</div>

