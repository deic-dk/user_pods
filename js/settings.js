$(document).ready(function() {

	// catch clicks on our Submit button
	$('#podssettingssubmit').click(function() {
		var publicIP = $('#publicIP').val();
		var privateIP = $('#privateIP').val();
		var storageDir = $('#storageDir').val();
		var manifestsURL = $('#manifestsURL').val();
		var rawManifestsURL = $('#rawManifestsURL').val();
		$.ajax(OC.linkTo('user_pods', 'ajax/set_settings.php'), {
			type: "POST",
			data: {
				publicIP: publicIP,
				privateIP: privateIP,
				storageDir: storageDir,
				manifestsURL: manifestsURL,
				rawManifestsURL: rawManifestsURL
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
		}
	});

});
