<div id="app-content" style="transition: all 0.3s ease 0s;">
<div id="app-content-kubernetes" class="viewcontainer">
<div id="controls">
  <div class="row">
    <div class="text-right" style="margin-right: 19px;">
      <div class="actions creatable">
        <div id="create" original-title="">
		  <a id="pod-create" class="btn btn-default btn-flat" href="#">
	      Create new pod
          
          </a>
     </div>
    </div>
      </div>
  </div>
  <div id="newpod" class="apanel">
     <span class="spanpanel" >
	<div>
	  	<input class="edit" id="newpod" type="text" placeholder="New pod">
	</div> 
		<div class = "left">marina left</div>
		<div class ="right"> mehran right</div>
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
<div class="myHead">
  <h3 style="
      padding-bottom: 28px;
      margin-left: 10px;
      margin-top: 10px;
">
Your running pods:</h3>
</div>
<div id="loading">
  <div id="loading-text">Your pod is being deleted.. Please wait!</div>
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
     <span class="text-semibold">Container name</span>
    </div>
  </th>
  <th id="headerDisplay" class="column-display" style="padding-right:3%; width:1%">
    <div class="size sort columntitle" data-sort="size">
      <span class="text-semibold">HTTPS port</span>
    </div>
  </th>
  <th id="headerDisplay" class="column-display" style="padding-right:3%; width:1%">
    <div class="size sort columntitle" data-sort="size">
      <span class="text-semibold">SSH port</span>
    </div>
  </th> 
  <th id="headerDisplay" class="column-display" style="padding-right:3%; width:1%">
    <div class="size sort columntitle" data-sort="size">
      <span class="text-semibold">Status</span>
    </div>
   </th>
</tr>
</thead>
<tbody id='fileList'>
	<?php

                $containers = OC_Kubernetes::getUserPods(OC_User::getUser ()) ;	
		foreach ($containers as $container) {
			$podname = $container["pod_name"];
		        $containername = $container["container_name"];
			$status = $container["status"];
			$sshport = $container["ssh_port"];
			$httpsport = $container["https_port"];
			####$owner = $container["owner"];
			$docker_image = $container["docker_image"];	
			$Jupy = $container["Uri_Jupy"];
			$Uri_Jupy = 'https://kube.sciencedata.dk:'. $httpsport . '/' . $Jupy;
			 if (strpos($Jupy, 'token') == true){
				 $Uri_Jupy = 'https://kube.sciencedata.dk:' . $httpsport . '/'.$Jupy;
				 $word = "Jupyter Notebook";
			 } else {
				 $Uri_Jupy = 'https://kube.sciencedata.dk:' . $httpsport;
				 $word = "";
			 }
                         


			echo "<tr id=\"$podname\" class='container-row'>
				<td id=\"$podname\" class=\"$podname\" name=\"$podname\" data-group=\"$podname\" style='height:34px' >
			<div class='row'>
					<div class='col-xs-1 text-right '></div>
					<a class='name'>
                                        <span class='nametext'>$podname</span></a>
                                </div>
				</td>
			     <td id='container-name' class=\"$containername\">
				<div class='container'>
				<span id='container'>$containername</span>
                		</div>
			     </td>
		             <td id='https-port' class=\"$httpsport\">
				<div class='https-port'>
				<span id='https-port'>$httpsport</span>
                		</div>
		 	      </td>
		  	      <td id='ssh-port' class=\"$sshport\">
                                <div class='ssh-port'>
                                <span id='ssh-port'>$sshport</span>
                                </div>
                              </td>
			      <td id='status' class=\"$status\">
                                <div class='status'>
                                <span id='status'>$status</span>
				</div>
			      </td>

			        <td  class=\"$Uri_Jupy\">
				<div><a  href=\"$Uri_Jupy\" style='display:none'  target='_blank'>
				<span id='Uri_Jupy'>$word</span></a>
				</div>
			      </td> 
			      <td id='docker_image' class=\"$docker_image\">
				<div class='docker_image'><a class='image' href=\"$docker_image\" style='display:none'  target='_blank'>
				<span id='docker_image'>$docker_image</span></a>
                                </div>
                              </td>
			      <td><a href='#' original-title='Delete Pod' id='delete_pod' class='action icon icon-trash-empty' style='text-decoration:none;color:#c5c5c5;font-size:16px;background-image:none'></a>
				</td>
			     </tr>";
		}	

   ?>
</tbody> 
<tfoot>
	<tr class="summary text-sm">
		<td>
			
		<span class="info" style="margin-left: 15px;"><?php
		$all_cont = count($containers);
                echo $all_cont." Container".($all_cont>1?"s":"");  ?></span>
		</td>
	</tr>

    
</tfoot>

</table>
</div>
</div>

<?php 
			echo "<div id='dialogalert' title='Delete confirmation' style='display:none;'><p>Are you sure you want to delete this pod?<p></div>";  ?>
