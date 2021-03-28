$(document).ready(function() {
	var docker_hub = 'https://hub.docker.com/r/';
	$('a#pod-create').click(function() {
		$('#newpod').slideToggle();
	});

	$('#newpod #cancel').click(function() {
		$('#newpod').slideToggle();
	});

	$("#podinput").prop("selectedIndex", -1);

	$("#podinput").change(function () {
        	var select_value = $(this).val()
		$.post(OC.filePath('kubernetes_app', 'ajax', 'actions.php') ,{ yaml_file : select_value } ,  function (jsondata){
                            if(jsondata.status == 'success'){
				var github_uri = 'https://raw.githubusercontent.com/deic-dk/pod_manifests/main/' + select_value;
				var dockerhub_uri = docker_hub + jsondata.data.included[2];
				var image_info = '<span style="padding-left:1%"><a href=\''+github_uri+'\'target="_blank">GitHub page</a></span>\
				    			<span style="padding-left:1%"><a href=\''+dockerhub_uri+'\'target="_blank">DockerHub page</a></span>';
				$('#links').empty();
				$('#links').append(image_info);
				if (jsondata.data.included[0]==true) {
					$('div#ssh').css('visibility', 'visible');
				}
				else {
					$('div#ssh').css('visibility', 'hidden');
				}
				if (jsondata.data.included[1]==true) {
					$('div#storage').css('visibility', 'visible');
				}
				else {
					$('div#storage').css('visibility', 'hidden');
				}
			    }

		});
    	});

	$('#newpod #ok').on('click', function() {
		var yaml_file = $('#podinput').val();
		var ssh_key = $('.sshpod').val();
		var storage = $('.storagepath').val();
		$.ajax({url: OC.filePath('kubernetes_app', 'ajax', 'actions.php'),
			data: {pod_image: yaml_file, ssh: ssh_key, storage: storage}, 
			method: 'post',
			beforeSend: function() {
				$('#podstable').css("visibility", "hidden");
				$('#pod-create').css("visibility", "hidden");
				$('#newpod').slideToggle();
				$('#newpod').val("");
				$('#loading').css("display", "block");
    			},
    			complete: function() {
        			// Hide loading
    			},
    			success: function(data) {
				location.reload();

			}				
		});
	});

	$("#podstable td #delete-pod").live('click', function() {
		var podSelected = $(this).closest('tr').attr('id');
                $( '#dialogalert' ).dialog({ buttons: [ { id:'test','data-test':'data test', text: 'Delete', click: function() {
			$.post(OC.filePath('kubernetes_app', 'ajax', 'actions.php') ,{ pod_name : podSelected } ,  function (jsondata){ 

                            if(jsondata.status == 'success'){ 
                             	location.reload();

			    } 
			    

		      });
			
			$.ajax({url: OC.filePath('kubernetes_app', 'ajax', 'actions.php'),
				data: {pod_name: podSelected},
				method: 'post',
				beforeSend: function() {
					$('#podstable').css("visibility", "hidden");
					$('#pod-create').css("visibility", "hidden");
					$("#loading-text").text("Deleting your pod... Please wait");
					$('#loading').css("display", "block");
				},
				complete: function() {
					
				},
				success: function(data) {
					location.reload();
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
		var uri = $(this).closest('tr').find('span#uri').html();
		var complete_uri = 'https://kube.sciencedata.dk:' + https_port + '/' + uri;

		var image = $(this).closest('tr').find('span#image').html();
		var image_uri = docker_hub + image;
		var html = '<div><span><h3 class="oc-dialog-title" style="padding-left:25px;"><span>'+ pod+'</span></h3></span><a class="oc-dialog-close close svg"></a>\
			<div id="meta_data_container" class=\''+ pod +'\'>\
			<div style="position:absolute; left:40px; top:80px;">Original image on Docker Hub:\
		        <div><a href=\''+image_uri+'\'target="_blank">'+ image_uri + '</a></div></div>\
			<div style="position:absolute; left:40px; top:140px;">\
			<div id="uri">Access web service:</div>\
			<div><a href=\''+complete_uri+'\'target="_blank">'+ complete_uri +'</a></div></div>\
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
			OC.redirect(OC.linkTo('kubernetes_app', 'ajax/getLogs.php') + '?file=' + pod);
		});
	});

	$(document).click(function(e){
          if (!$(e.target).parents().filter('.oc-dialog').length && !$(e.target).parents().filter('.name').length ) {
                $(".oc-dialog").hide();
		$('.modalOverlay').remove();
           }
        });
});
