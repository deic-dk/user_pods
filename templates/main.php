<div id="app-content" style="transition: all 0.3s ease 0s;">
<div id="app-content-kubernetes" class="viewcontainer">
<div id="controls">
  <div class="row">
    <div class="text-right" style="margin-right: 19px;">
      <div class="actions creatable">
        <div id="create" original-title="">
		  <a id="pod-create" class="btn btn-primary btn-flat" href="#">
              Create new pod 
          </a>
     </div>
    </div>
      </div>
  </div>
  <div id="newpod" class="apanel">
     <span class="spanpanel" > Select a YAML file:
	<select id="podinput" name="yaml" data-placeholder="YAML file">
        	<?php
                	$images = OC_Kubernetes_Util::getImages();
                	foreach ($images as $image) {
                        	echo "<option value=\"$image\">$image</option>";
                	}
        	?>
  	</select>	  
	<div id="ssh"><input class="sshpod" id="sshpod" type="text" placeholder="Paste your public SSH key here..."></div>
	    <span class="newpod-span">	
		  <div id="ok" class="btn-pod" original-title="">
		    <a class="btn btn-default btn-flat" href="#">Add</a>
          	  </div>
          	  <div id="cancel" class="btn-pod" original-title="">
		    <a class="btn btn-default btn-flat" href="#">Cancel</a>
          	  </div>
	    </span>
      </span>
  </div>
 </div> 
</div>
<div id="loading" style="display: none;">
	<div style="font-size:large; text-align:center;">
		Creating your pod... Please wait
	</div>
	<div class="loader"></div>
</div>
<table id="podstable" class="panel">
<thead class="panel-heading" >
<tr>
  <th id="headerName" class="column-name">
    <div id="headerName-container" class="row">
      <div class="col-xs-4 col-sm-1"></div>
      <div class="col-xs-3 col-sm-6">	  
        <div class="name sort columntitle" data-sort="descr">
		  <span class="text-semibold">Pod name</span>         
        </div>
      </div>
    </div>
  </th>
  <th id="headerDisplay" class="column-display" style="padding-right:3%; width:1%">
    <div class="display sort columntitle" data-sort="public">
      <span>Container name</span>
    </div>
  </th>
  <th id="headerDisplay" class="column-display" style="padding-right:3%; width:1%">
    <div class="size sort columntitle" data-sort="size">
      <span>HTTPS port</span>
    </div>
  </th>
  <th id="headerDisplay" class="column-display" style="padding-right:3%; width:1%">
    <div class="size sort columntitle" data-sort="size">
      <span>SSH port</span>
    </div>
  </th> 
  <th id="headerDisplay" class="column-display" style="padding-right:3%; width:1%">
    <div class="size sort columntitle" data-sort="size">
      <span>Status</span>
    </div>
  </th>
  <th id="headerDisplay" class="column-display" style="padding-right:3%; width:1%">
    <div class="size sort columntitle" data-sort="size">
      <span>Link</span>
    </div>
  </th>
</tr>
</thead>
<tbody id='fileList'>
	<?php
		$containers = OC_Kubernetes_Util::getUserPods(OC_User::getUser ()) ;	
		foreach ($containers as $container) {
			$podname = array("pod_name", $container["pod_name"]);
		        $containername = array("container_name", $container["container_name"]);
			$status = array("status", $container["status"]);
			$sshport = array("ssh_port", $container["ssh_port"]);
			$httpsport = array("https_port", $container["https_port"]);	
			$token = $container["uri"];
			if (strpos($token, 'token') == true) {
				$uri = 'https://kube.sciencedata.dk:'.$httpsport[1].'/'.$token;
				$uri_text = 'Jupyter Notebook';
			}	
			else {
				$uri = 'https://kube.sciencedata.dk:'.$httpsport[1];
				$uri_text = '';
			}

			echo "<tr class=\"$podname[1]\">
				<td id=\"$podname[1]\" class=\"$podname[0]\" name=\"$podname[1]\" data-group=\"$podname[1]\" style='height:34px' >
				<div class='row'>
					<div class='col-xs-1 text-right '></div>
		       			<span class='nametext'>$podname[1]</span>
				</div>
				</td>";
			OC_Kubernetes_Util::addRow($containername[0], $containername[1]);
			OC_Kubernetes_Util::addRow($httpsport[0], $httpsport[1]);
			OC_Kubernetes_Util::addRow($sshport[0], $sshport[1]);
			OC_Kubernetes_Util::addRow($status[0], $status[1]);
			echo  "<td id='uri'  class=\"$uri\">
				<div class='uri'><a class='uri' href=$uri target='_blank'>
				<span id='uri'>".$uri_text."</span></a>
				</div>
			      </td>
			      <td><a href='#' original-title='Delete pod' id='delete-pod' class='action icon icon-trash-empty' style='text-decoration:none;color:#c5c5c5;font-size:16px;background-image:none'></a>
				</td>
			     </tr>";
		}	
	?>
</tbody> 
<tfoot
	<tr class="summary text-sm">
		<td>
			
		       <span class="info">
				<?php 
				echo count($containers)." Containers"; ?> 			</span>
		</td>
	</tr>
    
</tfoot>


</table>
</div>
</div>
<div id='dialogalert' title='Delete Confirmation' style='display:none;' ><p>Are you sure you want to delete this container?</p></div>

