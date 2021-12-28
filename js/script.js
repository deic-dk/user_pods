var dockerhub_url = 'https://hub.docker.com/r/';
var kube_public_url = 'https://kube.sciencedata.dk';

function parseUrl( url ) {
  var a = document.createElement('a');
  a.href = url;
  return a;
}

function getRow(container){
	var str = "";
	for(const col in container){
		str += "	<td>"+
		"\n	<div column="+col+">"+
		"\n		<span>"+(col=="url"||col=="ssh_url"?"<a href='"+container[col]+"'>"+container[col]+"</a>":container[col])+"</span>"+
		"\n	</div>"+
		"\n</td>"	};
	str +="\n<td><a href='#' title="+t('user_pods', 'Delete pod')+" class='delete-pod action icon icon-trash-empty'></a></td>";
	str ="	<tr pod_name='"+container['pod_name']+"' container_name='"+container['container_name']+"'>"+str+"\n</tr>";
	return str;
}

function getContainers(podNames, callback){
	$("#loading-text").text(t("user_pods", "Working..."));
	$('#loading').show();
	$.ajax({
		url: OC.filePath('user_pods', 'ajax', 'actions.php'),
		data:  {action: 'get_containers', pod_names: podNames},
		success: function(jsondata) {
			if(jsondata.status == 'error'){
				if(jsondata.data &&jsondata.data.error && jsondata.data.error == 'authentication_error'){
					OC.redirect('/');
				}
			}
			jsondata.data.forEach(function(value, index, array){
				if(index==0){
					if(!$('table#podstable thead tr th').length){
						for(const col in value){
							$('table#podstable thead tr').append("						<th class='column-display'>"+
							"\n							<div class='display sort columntitle' data-sort='public'>"+
							"\n								<span>"+t('user_pods', col)+"</span>"+
							"\n							</div>"+
							"\n						</th>");
						}
						$('table#podstable thead tr').append("n						<th class='column-display'></th>");
					}
				}
				if(callback){
					callback();
				}
				$('tbody#fileList').append(getRow(value));
			});
			if(!podNames){
				$('table#podstable tfoot.summary tr td span.info').remove();
				$('table#podstable tfoot.summary tr td').append("<span class='info' containers='"+jsondata.data.length+"'>"+
						jsondata.data.length+" "+(jsondata.data.length==1?t("user_pods", "container"):t("user_pods", "containers"))+
						"</span");
			}
			$('#loading').hide();
		},
	error: function(){
			$('#loading').hide();
			//OC.dialogs.alert(t("user_pods", "get_containers: Something went wrong..."), t("user_pods", "Error"));
		}
	});
}

function runPod(yaml_file, ssh_key, storage_path, file){
	$("#loading-text").text(t("user_pods", "Creating pod..."));
	$('#loading').show();
	$.ajax({url: OC.filePath('user_pods', 'ajax', 'actions.php'),
		data: {action: 'create_pod', yaml_file: yaml_file, public_key: ssh_key, storage_path: storage_path, file:file}, 
		method: 'post',
		success: function(jsondata) {
			if(jsondata.status == 'error'){
				if(jsondata.data && jsondata.data.error && jsondata.data.error == 'authentication_error'){
					OC.redirect('/');
				}
			}
			// Get pod name from line like
			// pod/ubuntu-focal-kerverous-3 created
			var podName;
			if(jsondata.message){
				podName = jsondata.message.replace(/[\s\S]+\npod\/(.*) created\n[\s\S]+/, '$1');
			}
			if(podName && podName!=jsondata.message){
				$('#loading').show();
				getContainers([podName]);
				var containers_now = parseInt($('table#podstable tfoot.summary tr td span.info').attr('containers'), 10) +1;
				$('table#podstable tfoot.summary tr td span.info').remove();
				$('table#podstable tfoot.summary tr td').append("<span class='info' containers='"+containers_now+"'>"+
						containers_now+" "+(containers_now==1?t("user_pods", "container"):t("user_pods", "containers"))+
						"</span");
				setTimeout(function(){getContainers([podName], function(){$('tr[pod_name="'+podName+'"]').remove();});}, 10000)
				setTimeout(function(){getContainers([podName], function(){$('tr[pod_name="'+podName+'"]').remove();});}, 30000)
				setTimeout(function(){getContainers([podName], function(){$('tr[pod_name="'+podName+'"]').remove();});}, 60000)
			}
			else{
				OC.dialogs.alert(t("user_pods", "run_pod: Something went wrong..."), t("user_pods", "Error"));
			}
			$('#loading').hide();
		}
	});
}

function loadYaml(yaml_file){
	 $('#public_key').val('');
	$("#loading-text").text(t("user_pods", "Working..."));
	$('#loading').show();
	var select_value = yaml_file || $('#yaml_file').val();
	if(!select_value) {
		$('div#storage').hide();
		$('div#ssh').hide();
		$('#webdav').empty();
	}
	if(!select_value){
		$('#loading').hide();
		return;
	}
	$.post(OC.filePath('user_pods', 'ajax', 'actions.php') , { action: 'check_manifest', yaml_file : select_value } ,  function (jsondata){
		$('#loading').hide();
		if(jsondata.status == 'success'){
			var yaml_url = jsondata.data['manifest_url'].replace(/^https:\/\/raw\.githubusercontent\.com\/deic-dk\/pod_manifests\/main\//,
					'https://github.com/deic-dk/pod_manifests/blob/main/');
			var link = '<span><a href=\''+yaml_url+'\'target="_blank">YAML source</a></span>';
			$('#links').empty();
			$('#links').append(link);
			$('#description').empty();
			$('#description').append(marked(jsondata.data['manifest_info']));
			if (jsondata.data['pod_accepts_public_key']==true) {
				$('div#ssh').show();
			}
			else {
				$('div#ssh').hide();
			}
			if (jsondata.data['pod_accepts_file']==true) {
				$('div#file').show();
			}
			else {
				$('div#file').hide();
			}
			if (jsondata.data['pod_mount_path'] &&(jsondata.data['pod_mount_path']['sciencedata'] ||  jsondata.data['pod_mount_path']['local'])) {
				var mount_src = jsondata.data['pod_mount_src'] ;
				var storage_input = "";
				for (var containerIndex in jsondata.data['container_infos']) {
					var container = jsondata.data['container_infos'][containerIndex];
					for (var name in container['mount_paths']) {
						// Notice that local mounts are specified in the yaml - to avoid rogue mounting
						if(name=='sciencedata'){
							var mountPath = container['mount_paths'][name];
							var mountName = new String(mountPath).substring(mountPath.lastIndexOf('/') + 1); 
							if(mountPath && mountName){
								storage_input = storage_input+
								'<input image_name="'+container['image_name']+'" type="text" placeholder="'+
								t('user_pods', 'Storage path')+'" image="'+container['image_name']+'" mountPath="'+mountPath+'" title="'+
								t('user_pods', 'Directory under')+' '+
								'<a href=\'https://'+encodeURIComponent($('head').attr('data-user'))+'@'+
								location.hostname+'/storage/\' target=\'_blank\'>/storage/</a> '+t('user_pods', 'to mount on')
								+' <b>'+mountPath+'</b> '+t('user_pods', 'inside')+' '+container['image_name']+', '+t('user_pods', 'and serve via https.')+
								'"></input>'+
								"\n";
								// Although they yaml can, in principle have different containers with different mounts, or multiple mounts in one container,
								// run_pod only supports one nfs_storage_path
								break;
							}
						}
					}
				}
				$('div#storage').show();
				$('#storage').empty();
				$('#storage').append(storage_input);
				$('#storage input').tipsy({html: true, hoverable: true});
			}
			else {
				$('div#storage').hide();
			}
		}
		else if(jsondata.status == 'error'){
			if(jsondata.data &&jsondata.data.error && jsondata.data.error == 'authentication_error'){
				OC.redirect('/');
			}
		}
	});
}

$(document).ready(function() {

	var hostname = $(location).attr('host');

	$('a#pod-create').click(function() {
		$('#newpod').slideToggle();
	});

	$('#newpod #cancel').click(function() {
		$('#newpod').slideToggle();
	});

	$("#yaml_file").prop("selectedIndex", -1);

	$("#yaml_file").change(function () {
		loadYaml();
	});

	$('#newpod #ok').on('click', function() {
		var yaml_file = $('#yaml_file').val();
		var ssh_key = $('#public_key').val();
		var file = $('#file_input').val();
		var storage_path = "";
		if(!$('#storage input').length){
			runPod(yaml_file, ssh_key, storage_path, file);
			if($('#public_key:visible').length && (!ssh_key ||  ssh_key== "")) {
				OC.dialogs.alert(t("user_pods", "Please fill in a public SSH key"), t("user_pods", "Missing SSH key"));
			}
			return false;
		}
		 $('#storage input').each(function(el){
			 if($(this).attr('image_name') ){
				storage_path = $(this).val();
				// Although the yaml can, in principle have different containers with different mounts, or multiple mounts in one container,
				// run_pod only supports one storage_path
				if(!storage_path ||  storage_path== "") {
					OC.dialogs.alert(t("user_pods", "Please fill in the directory to mount from your home server"), t("user_pods", "Missing storage path"));
				}
				else{
					runPod(yaml_file, ssh_key, storage_path, file);
				}
				return false;
			 }
		 });
	});

	$("#podstable td .delete-pod").live('click', function() {
		var podSelected = $(this).closest('tr').find('td div[column="pod_name"] span').text().trim();
		$( '#dialogalert' ).html("<div>"+t("user_pods","Are you sure you want to delete the pod")+" "+podSelected+"?</div>");
		$( '#dialogalert' ).dialog({buttons: [ { id:'test','data-test':'data test', text: 'Delete', click: function(el) {
			$.ajax({url: OC.filePath('user_pods', 'ajax', 'actions.php'),
				data: {action: "delete_pod", pod_name: podSelected},
				method: 'post',
				beforeSend: function() {
					$("#loading-text").text(t("user_pods", "Deleting your pod..."));
					$('#loading').show();
				},
				complete: function() {
				},
				success: function(data) {
					if(data.status == 'success'){
						$('tr[pod_name="'+podSelected+'"]').remove();
						var containers_now = parseInt($('table#podstable tfoot.summary tr td span.info').attr('containers'), 10) -1;
						$('table#podstable tfoot.summary tr td span.info').remove();
						$('table#podstable tfoot.summary tr td').append("<span class='info' containers='"+containers_now+"'>"+
								containers_now+" "+(containers_now==1?t("user_pods", "container"):t("user_pods", "containers"))+
								"</span");
						$('#loading').hide();
					}
					else if(data.status == 'error'){
						if(data.data.error && data.data && data.data.error == 'authentication_error'){
							OC.redirect('/');
						}
					}
				}
			});
			$(this).dialog( 'close' ); } },
			{ id:'test2','data-test':'data test', text: 'Cancel', click: function() {
				$(this).dialog( 'close' ); } } ] });
	});

	$("#podstable > tbody > tr").each(function() {
  		var value = $(this).find("td span#status").text();
		if (~value.indexOf("Running")) {
			var date = value.substr(value.indexOf(':')+1);
			var time = new Date(date).toString().slice(0,25);
			var finalDate = "Running: ".concat(time);
			$(this).find("td span#status").text(finalDate);
		}
	});


	$("#podstable .name").live('click', function() {
		var pod = $(this).closest('td').attr('id') ;
		var https_port = $(this).closest('tr').find("span#https_port").html();
		var url = $(this).closest('tr').find('span#url').html();
		var complete_url = kube_public_url+':' + https_port + '/' + url;
		var image = $(this).closest('tr').find('span#image').html();
		var image_url = dockerhub_url + image;
		var html = '<div><span><h3 class="oc-dialog-title" style="padding-left:25px;"><span>'+ pod+'</span></h3></span><a class="oc-dialog-close close svg"></a>\
			<div id="meta_data_container" class=\''+ pod +'\'>\
			<div style="position:absolute; left:40px; top:80px;">Original image on Docker Hub:\
			<div><a href=\''+image_url+'\'target="_blank">'+ image_url + '</a></div></div>\
			<div style="position:absolute; left:40px; top:140px;">\
			<div id="url">Access web service:</div>\
			<div><a href=\''+complete_url+'\'target="_blank">'+ complete_url +'</a></div></div>\
			<div style="position:absolute; bottom:40px; left:40px;" >\
			<div>Download the logs of your container:</div><p></p>\
			<button id="download-logs" class="download btn btn-primary btn-flat">Download</button>&nbsp\
			</div>\
</div>';

		$(html).dialog({
			  dialogClass: "oc-dialog",
			  resizeable: false,
			  draggable: false,
			  height: 400,
			  width: 600
			});

		$('body').append('<div class="modalOverlay">');

		$('.oc-dialog-close').live('click', function() {
			$(".oc-dialog").hide();
			$('.modalOverlay').remove();
		});

		$('.ui-helper-clearfix').css("display", "none");

		$("#download-logs").live('click', function() {
			OC.redirect(OC.linkTo('user_pods', 'ajax/getLogs.php') + '?file=' + pod);
		});
	});

	$(document).click(function(e){
		if (!$(e.target).parents().filter('.oc-dialog').length && !$(e.target).parents().filter('.name').length ) {
			$(".oc-dialog").hide();
			$('.modalOverlay').remove();
			}
		});
	
	$('#pods_refresh').click(function(e){
		$('#podstable #fileList tr').remove();
		$('table#podstable tfoot.summary tr td span.info').remove();
		getContainers();
	})
	
	getContainers([], function(){
		if(typeof getGetParam !== 'undefined' && getGetParam('file') && getGetParam('yaml_file')){
			var yaml_file = getGetParam('yaml_file');
			var file = getGetParam('file');
			$('#newpod').show();
			$.when(loadYaml(yaml_file)).then(function(){
				$('#yaml_file').val(yaml_file);
				$('#file_input').val(file);
			});
		}
	});

});
