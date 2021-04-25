<div id="app-content">
    <div id="app-content-kubernetes" class="viewcontainer">
        <span id="dockerhub_uri" value=<?php p(isset($_['dockerhub']) ? $_['dockerhub'] : ''); ?>></span>
        <span id="github_uri" value=<?php p(isset($_['github']) ? $_['github'] : ''); ?>></span>
        <div id="controls">
            <div class="row">
                <div class="text-right button-right">
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
                <span class="spanpanel"> Choose service to run:
                    <select id="podinput" name="yaml" data-placeholder="YAML file">
                        <?php
                        $github_uri = 'https://github.com' . (isset($_['github']) ? $_['github'] : '');
                        $images = OC_Kubernetes_Util::getImages($github_uri);
                        foreach ($images as $image) {
                            echo "<option value=\"$image\">$image</option>";
                        }
                        ?>
                    </select>
                </span>
                <div id="description"></div>
                <span id="links"></span>
                <div id="ssh" class="box-left">
                    <h4> Public SSH key</h4>
                    <div><input class="sshpod box" id="sshpod" type="text" placeholder="Paste your public SSH key here..."></div>
                </div>
                <div id="storage" class="box-left">
                    <h4>Volume mount</h4><input class="storagepath box" id="storagepath" type="text" placeholder="Folder under /storage to mount in the pod/container...">
                    <span id="webdav"></span>
                    <div id="mount-path"></div>
                </div>
                <span class="newpod-span">
                    <div id="ok" class="btn-pod" original-title="">
                        <a class="btn btn-default btn-flat" href="#">Add</a>
                    </div>
                    <div id="cancel" class="btn-pod" original-title="">
                        <a class="btn btn-default btn-flat" href="#">Cancel</a>
                    </div>
                </span>

            </div>
        </div>
    </div>
    <div id="loading">
        <div id="loading-text">
            Creating your pod... Please wait
        </div>
        <div class="loader"></div>
    </div>
    <h2 id="table-h">Running pods</h2>
    <table id="podstable" class="panel">
        <thead class="panel-heading">
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
                <th id="headerDisplay" class="column-display">
                    <div class="display sort columntitle" data-sort="public">
                        <span>Container name</span>
                    </div>
                </th>
                <th id="headerDisplay" class="column-display">
                    <div class="size sort columntitle" data-sort="size">
                        <span>HTTPS port</span>
                    </div>
                </th>
                <th id="headerDisplay" class="column-display">
                    <div class="size sort columntitle" data-sort="size">
                        <span>SSH port</span>
                    </div>
                </th>
                <th id="headerDisplay" class="column-display">
                    <div class="size sort columntitle" data-sort="size">
                        <span>Status</span>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody id='fileList'>
            <?php
            $containers = OC_Kubernetes_Util::getUserPods(OC_User::getUser());
            foreach ($containers as $container) {
                $podname = array("pod_name", $container["pod_name"]);
                $containername = array("container_name", $container["container_name"]);
                $status = array("status", $container["status"]);
                $sshport = array("ssh_port", $container["ssh_port"]);
                $httpsport = array("https_port", $container["https_port"]);
                if (array_key_exists("uri", $container)) {
                    $token = $container["uri"];
                } else {
                    $token = "";
                }
                $image = $container["image_name"];

                if (strpos($token, 'token') == true) {
                    $uri = 'https://kube.sciencedata.dk:' . $httpsport[1] . '/' . $token;
                    $uri_text = 'Jupyter Notebook';
                } else {
                    $uri = 'https://kube.sciencedata.dk:' . $httpsport[1];
                    $uri_text = '';
                }

                echo "<tr id=\"$podname[1]\" class='container-row'>
				<td id=\"$podname[1]\">
				<div class='row'>
					<div class='col-xs-1 text-right '></div>
					<a class='name'>
		       			<span class='nametext'>$podname[1]</span>
					</a>
				</div>
				</td>";
                OC_Kubernetes_Util::addCell($containername[0], $containername[1]);
                OC_Kubernetes_Util::addCell($httpsport[0], $httpsport[1]);
                OC_Kubernetes_Util::addCell($sshport[0], $sshport[1]);
                OC_Kubernetes_Util::addCell($status[0], $status[1]);
                echo  "<td class='uri'>
                            <div><a href=$uri target='_blank'>
                                <span id='uri'>" . $uri_text . "</span></a>
                            </div>
                        </td>
                        <td class='image'>
                            <div><a href=$image target='_blank'>
                                <span id='image'>" . $image . "</span></a>
                        </td>
                        <td><a href='#' original-title='Delete pod' id='delete-pod' class='action icon icon-trash-empty'></a>
                        </td>
                        </tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr class="summary text-sm">
                <td>

                    <span class="info">
                        <?php
                        $all_cont = count($containers);
                        echo $all_cont . " Container" . ($all_cont > 1 ? "s" : "");
                        ?>
                    </span>
                </td>
            </tr>

        </tfoot>


    </table>
</div>
</div>
<div id='dialogalert' title='Delete Confirmation'>
    <p>Are you sure you want to delete this container?</p>
</div>