$(document).ready(function() {
	$('a#pod-create').click(function() {
		$('#newpod').slideToggle();
	});

	$('#newpod #cancel').click(function() {
		$('#newpod').slideToggle();
	});

	$('#newpod #ok').on('click', function() {

		var yaml_file = $('#podinput').val();
		alert(yaml_file);
		$.post(OC.filePath('kubernetes_app', 'ajax', 'actions.php'), { pod_image : yaml_file } , function ( jsondata ){
				if(jsondata.status == 'success' ) {
					$('#newpod').slideToggle();
					$('#newpod').val("");
					location.reload();
				}else{
					OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
				}
		});
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
