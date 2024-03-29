$(document).ready(function() {

	// catch clicks on our Submit button
	$('#podssettingssubmit').click(function() {
		var publicIP = $('#publicIP').val();
		var privateIP = $('#privateIP').val();
		var storageDir = $('#storageDir').val();
		var manifestsURL = $('#manifestsURL').val();
		var rawManifestsURL = $('#rawManifestsURL').val();
		var nbViewerPrivateURL = $('#nbViewerPrivateURL').val();
		var nbViewerPublicURL = $('#nbViewerPublicURL').val();
		var jupyterYamlFile = $('#jupyterYamlFile').val();
		var getContainersPassword = $('#getContainersPassword').val();
		var getContainersURL = $('#getContainersURL').val();
		var trustedUser = $('#trustedUser').val();
		$.ajax(OC.linkTo('user_pods', 'ajax/set_settings.php'), {
			type: "POST",
			data: {
				publicIP: publicIP,
				privateIP: privateIP,
				storageDir: storageDir,
				manifestsURL: manifestsURL,
				rawManifestsURL: rawManifestsURL,
				nbViewerPrivateURL: nbViewerPrivateURL,
				nbViewerPublicURL: nbViewerPublicURL,
				jupyterYamlFile: jupyterYamlFile,
				getContainersPassword: getContainersPassword,
				getContainersURL: getContainersURL,
				trustedUser: trustedUser
			},
			dataType: 'json',
			success: function(s) {
				 OC.msg.finishedSaving('#kubernetesstatus', {status: 'success', data: {message: "Settings stored."}});
					$('#kubernetesstatus').css("color", "green");
			}
		});
	});

	// retrieve our stored token values (if any)
	$.ajax(OC.linkTo('user_pods', 'ajax/get_settings.php'), {
		type: "GET",
		dataType: 'json',
		success: function(s) {
			$('#publicIP').val(s['publicIP']);
			$('#privateIP').val( s['privateIP']);
			$('#storageDir').val( s['storageDir']);
			$('#manifestsURL').val( s['manifestsURL']);
			$('#rawManifestsURL').val( s['rawManifestsURL']);
			$('#nbViewerPrivateURL').val( s['nbViewerPrivateURL']);
			$('#nbViewerPublicURL').val( s['nbViewerPublicURL']);
			$('#jupyterYamlFile').val( s['jupyterYamlFile']);
			$('#getContainersPassword').val( s['getContainersPassword']);
			$('#getContainersURL').val( s['getContainersURL']);
			$('#trustedUser').val( s['trustedUser']);
		}
	});

});
