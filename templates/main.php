<div id="app-content">
	<div id="app-content-kubernetes" class="viewcontainer">
	<div class="info hidden">
	Notice: Currently, Pods are in beta testing. Use at your own risk - pods may be deleted, terminated or restarted.
	We appreciate <a href="mailto:<?php echo(\OCP\Config::getSystemValue('fromemail', ''));?>">feedback.</a></div>
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
				<span class="spanpanel" >
					<select id="yaml_file" title=<?php p($l->t("YAML file")); ?>>
						<?php
						echo "<option value=''></option>";
						foreach ($_['manifests'] as $manifest) {
							echo "<option value='".$manifest."'".
								(!empty($_REQUEST['yaml_file'])&&$_REQUEST['yaml_file']==$manifest?" selected='selected'":"").
								">".preg_replace('|\.yaml$|', '', $manifest)."</option>";
						}
						?>
					</select>
				</span>
				<span id="links"></span>
				<span class="newpod-span">	
					<div id="ok" class="btn-pod" original-title="">
						<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Launch")); ?></a>
					</div>
					<div id="cancel" class="btn-pod" original-title="">
						<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Cancel")); ?></a>
					</div>
				</span>
				<div id="description"></div>
				<div id="ssh">
					<textarea id="public_key" type="text" placeholder="<?php p($l->t("Public SSH key")); ?>"
					title="<?php p($l->t("Paste your public SSH key here")); ?>"></textarea>
						<div class="key_buttons">
							<a id="save_ssh_public_key" class="btn btn-default btn-flat btn-sg" href="#" title="<?php p($l->t("Save stored SSH key to browser storage")); ?>"><?php p($l->t("Save")); ?></a>
							<br />
							<a id="load_ssh_public_key" class="btn btn-default btn-flat btn-sg" href="#" title="<?php p($l->t("Load stored SSH key from browser storage")); ?>"><?php p($l->t("Load")); ?></a>
							<br />
							<a id="clear_ssh_public_key" class="btn btn-default btn-flat btn-sg" href="#" title="<?php p($l->t("Clear stored SSH key from browser storage")); ?>"><?php p($l->t("Clear")); ?></a>
					</div>
				</div>
				<div id="storage">
				</div>
				<div id="cvmfs">
				</div>
				<div id="setup">
				</div>
				<div id="file"><span id="file_text"><?php p($l->t("File")); ?>:</span>
					<input id="file_input" type="text" placeholder="<?php p($l->t("Optional file to open in your pod")); ?>"
					title="<?php p($l->t("Path of file in your ScienceData Home")); ?>"
					value="<?php echo(empty($_REQUEST['file'])?$_REQUEST['file']:''); ?>">
				</div>
				<div id="peers"><span id="peers_text"><?php p($l->t("Peers")); ?>:</span>
					<input id="peers_input" type="text" placeholder="<?php p($l->t("Optional peers to pass to your pod")); ?>"
					title="<?php p($l->t("List of the form hostname1:ip1,hostname2:ip2,...")); ?>"
					value="<?php echo(empty($_REQUEST['peers'])?$_REQUEST['peers']:''); ?>">
				</div>
			</div>
		</div> 
	</div>
	<h2 class="running_pods"><?php p($l->t("Running pods/containers")); ?>
	<a id="pods_refresh" class="btn btn-default" title="<?php p($l->t("Refresh")); ?>">&circlearrowright;</a></h2>
	<div id="running_pods">
	<table id="podstable" class="panel">
		<thead class="panel-heading" >
			<tr>
				<th id="headerPodName">
					<div class="display sort columntitle" data-sort="public">
						<span>pod_name</span>
					</div>
				</th>
				<th id="headerPodStatus">
					<div class="display sort columntitle" data-sort="public">
						<span>status</span>
					</div>
				</th>
				<th id="headerPodView">
					<div class="display sort columntitle" data-sort="public">
						<span>view</span>
					</div>
				</th>
				<th id="headerPodMore" class="th-button">
					<div class="display sort columntitle" data-sort="public">
						<span>more</span>
					</div>
				</th>
				<th id="headerPodDelete" class="th-button">
					<div class="display sort columntitle" data-sort="public">
						<span>delete</span>
					</div>
				</th>
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

