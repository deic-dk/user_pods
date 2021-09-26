<fieldset id="userPodsSettings" class="section" >
<h2><img src="/apps/user_pods/img/kubernetes.png" class="user_pods_logo"> Kubernetes for users</h2>
  <br>
	<p>Set parameters to interact with your Kubernetes installation image repository.</p>
	<p>Notice - one of your Kubernetes control-plane nodes must hav
	 <a href="https://github.com/deic-dk/sciencedata_kubernetes">sciencedata_kubernetes</a> installed
	 and serve the provided PHP scripts. Moreover a file server must be available and configured to serve
	 "/storage" via WebDAV and NFS.</p>
	<table>
		<tr>
			<td>
				<label for='publicIP'>Public IP of your Kubernetes frontend</label>
			</td>
			<td>
				<input type='text' id='publicIP' title='Public IP' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='privateIP'>Private IP of your Kubernetes frontend</label>
			</td>
			<td>
				<input type='text' id='privateIP' title='Private IP' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='storageDir'>Directory exposed as /storage via WebDAV and NFS (NFSv4.1)</label>
			</td>
			<td>
				<input type='text' id='storageDir' title='Storage directory' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='manifestsURL'>URL of the Github repository holding your manifests</label>
				<p>(https://github.com/some_repo/)</p>
			</td>
			<td>
				<input type='text' id='manifestsURL' title='Github manifests URL' style='width:475px' />
			</td>
		</tr>
		<tr>
			<td>
				<label for='rawManifestsURL'>URL of the Github repository holding your raw manifests</label>
				<p>(https://raw.githubusercontent.com/some_repo/main/)</p>
			</td>
			<td>
				<input type='text' id='rawManifestsURL' title='Github raw manifests URL' style='width:475px' />
			</td>
		</tr>
	</table>
	<br>
	<input type='submit' value='Save' id='podssettingssubmit' title='Store user_pods settings' />
	<label id='kubernetesstatus'></label>
</fieldset>

