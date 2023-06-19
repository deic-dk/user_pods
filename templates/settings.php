<?php
$l = OC_L10N::get('user_pods');
?>
<fieldset id="userPodsSettings" class="section" >
<h2><img src="/apps/user_pods/img/kubernetes.png" class="user_pods_logo"> Kubernetes for users</h2>
  <br>
	<p>Set parameters to interact with your Kubernetes installation image repository.</p>
	<p>Notice - one of your Kubernetes control-plane nodes must have
	 <a href="https://github.com/deic-dk/sciencedata_kubernetes">sciencedata_kubernetes</a> installed
	 and serve the provided PHP scripts. Moreover a file server must be available and configured to serve
	 "/storage" via WebDAV and NFS.</p>
	<table>
		<tr>
			<td>
				<label for='publicIP'><?php p($l->t('Public IP of your Kubernetes frontend'));?></label>
			</td>
			<td>
				<input type='text' id='publicIP' title='Public IP' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='privateIP'><?php p($l->t('Private IP of your Kubernetes frontend'));?></label>
			</td>
			<td>
				<input type='text' id='privateIP' title='Private IP' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='storageDir'><?php p($l->t('Directory exposed as /storage via WebDAV and NFS (NFSv4.1)'));?></label>
			</td>
			<td>
				<input type='text' id='storageDir' title='Storage directory' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='manifestsURL'><?php p($l->t('URL of the Github repository holding your manifests'));?></label>
				<p>(https://github.com/some_repo/)</p>
			</td>
			<td>
				<input type='text' id='manifestsURL' title='Github manifests URL' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='rawManifestsURL'><?php p($l->t('URL of the Github repository holding your raw manifests'));?></label>
				<p>(https://raw.githubusercontent.com/some_repo/main/)</p>
			</td>
			<td>
				<input type='text' id='rawManifestsURL' title='Github raw manifests URL' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='nbViewerPublicURL'><?php p($l->t('Public URL of nbviewer server'));?></label>
			</td>
			<td>
				<input type='text' id='nbViewerPublicURL' title='nbViewerPublicURL' IP' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='nbViewerPrivateURL'><?php p($l->t('Private URL of nbviewer server'));?></label>
			</td>
			<td>
				<input type='text' id='nbViewerPrivateURL' title='nbViewerPrivateURL' IP' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='jupyterYamlFile'><?php p($l->t('YAML file to use for running Jupyter notebooks'));?></label>
			</td>
			<td>
				<input type='text' id='jupyterYamlFile' title='jupyterYamlFile' IP' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='getContainersPassword'><?php p($l->t('Password for getting the status of containers of all users'));?></label>
			</td>
			<td>
				<input type='text' id='getContainersPassword' title='getContainersPassword' IP' style='width:475px' />
			</td>
		</tr>
	</table>
	<br>
	<input type='submit' value='Save' id='podssettingssubmit' title='Store user_pods settings' />
	<label id='kubernetesstatus'></label>
</fieldset>

