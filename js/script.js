$(document).ready(function() {
	$('a#pod-create').click(function() {
		$('#newpod').slideToggle();
	});

	$('#newpod #cancel').click(function() {
		$('#newpod').slideToggle();
	});

	$('#newpod #ok').on('click', function() {
		var yaml_file = $('#podinput').val();
		var ssh_key = $('.sshpod').val();
		if( ssh_key != "") {
			$.ajax({url: OC.filePath('kubernetes_app', 'ajax', 'actions.php'),
				data: {pod_image: yaml_file, ssh: ssh_key}, 
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
		}
	});

	$("#podstable td #delete-pod").live('click', function() {
		var status = $(this).closest('tr').attr('id') ;
                var containerSelected = $(this).closest('tr').attr('class') ;
                $( '#dialogalert' ).dialog({ buttons: [ { id:'test','data-test':'data test', text: 'Delete', click: function() {
			// TODO add logic

                $(this).dialog( 'close' ); } },
                { id:'test2','data-test':'data test', text: 'Cancel', click: function() {
                $(this).dialog( 'close' ); } } ] });

        });

});
