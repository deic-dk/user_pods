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
		var podSelected = $(this).closest('tr').attr('id');
                $( '#dialogalert' ).dialog({ buttons: [ { id:'test','data-test':'data test', text: 'Delete', click: function() {
			// TODO add logic
			$.post(OC.filePath('kubernetes_app', 'ajax', 'actions.php') ,{ pod_name : podSelected } ,  function (jsondata){ 

                            if(jsondata.status == 'success'){ 
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
});
