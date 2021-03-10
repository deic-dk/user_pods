$(document).ready(function() {
	$('a#pod-create').click(function() {
		alert("Hello Popup!");
		$('#newpod').slideToggle();
	});

	$('#newpod #cancel').click(function() {
		$('#newpod').slideToggle();
	});

        $("#podstable td #delete_pod").live('click', function(){
              var role = $(this).closest('tr').attr('id');
	      var podSelected = $(this).closest('tr').attr('class');
              $('#dialogalert').dialog({ buttons: [ { id:'test', 'data-test':'data test', text: 'Delete', click: function() {
                      $.post(OC.filePath('kubernetes', 'ajax', 'actions.php') ,{ pod : podSelected } ,  function (jsondata){ 

                            if(jsondata.status == 'success'){ 
                             location.reload();

			    }else{ 
                                   OC.dialogs.alert(jsondata.data.message , jasondata.data.title);
			    }
		      });
		  

                  $(this).dialog( 'close' ); } },
		      {id: 'test2', 'data-test':'data test', text: 'Cancel', click: function() {
		      $(this).dialog('close');} }
	       ] });
	});




});
