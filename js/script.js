$(document).ready(function() {
	$('a#pod-create').click(function() {
		alert("Hello Popup!");
		$('#newpod').slideToggle();
	});
         

	$('#newpod #cancel').click(function() {
		$('#newpod').slideToggle();
	});
         
        var uri_docker = 'https://docker.com/r/';



       	$("#podstable td #delete_pod").live('click', function(){
              var podSelected = $(this).closest('tr').attr('id');
              $('#dialogalert').dialog({ buttons: [ { id:'test', text: 'Delete', click: function() {
                      $.post(OC.filePath('kubernetes', 'ajax', 'actions.php') ,{ pod : podSelected } ,  function (jsondata){

                            if(jsondata.status == 'success'){
                               location.reload();


			    }else{
                                   OC.dialogs.alert(jsondata.data.message , jasondata.data.title);
			    }
		       });


		       $.ajax({
                            type:'post',
		            url:OC.filePath('kubernetes', 'ajax','actions.php'),
			    data: { pod : podSelected },
                            beforeSend: function(){
				   $("#pod-create").css("visibility", "hidden");
				   $("#podstable").css("visibility", "hidden"); 
				   $("#myHead").css("visibility", "hidden");
                                   $("#loading").css("display", "block");
				   $("#loading-text").text("Your pod is being deleted... Please wait!");
			           },
                            complete:function(){
                                   $("#loading").css("display", "none");
			           },
		            success:function(data){
                                   location.reload();
			           }

		       });

                       $(this).dialog( 'close' ); } },
		          {id: 'test2', text: 'Cancel', click: function() {
		          $(this).dialog('close');} }
	            ] });
	});
			
	


	$("#podstable .name").live('click', function(){
              var pod = $(this).closest('td').attr('id');
	      var httpsport = $(this).closest('tr').find("span#https-port").html();
	      var Uri_Jupy = $(this).closest('tr').find('span#Uri_Jupy').html(); 
	      var Uri_Jupy_all = 'https://kube.sciencedata.dk:' + httpsport + '/' + Uri_Jupy;
	      var docker_image = $(this).closest('tr').find('span#docker_image').html();
	      var uri_image = uri_docker + docker_image;
	      var html = '<div><span><h3 class="oc-dialog-title" style="padding-left:25px;">More info for <span>'+ pod+'</span></h3></span><a class="oc-dialog-close close svg"></a>\
			  <div id="meta_data_container" class=\'' + pod +'\'>\
		          <div style="position:absolute; top:80px; left:40px;">\
		          <div id="uri_image">Direct Link to image on Docker Hub:</div><p></p>\
		          <div><a href=\''+uri_image+'\'target="_blank">'+ uri_image +'</a></div></div>\
		          <div style="position:absolute; top:180px; left:40px;">\
		          <div id="Uri_Jupy">Direct Link to Container:</div><p></p>\
		          <div><a href=\''+Uri_Jupy_all+'\'target="_blank">'+ Uri_Jupy_all +'</a></div></div>\
			  <div style="position:absolute; bottom:100px; left:40px;">\
			  <div>Download the logs of the container:</div><p></p>\
			  <button id="download_logs" class="download btn btn-primary btn-flat">Download</button>&nbsp\
                          </div>\
			</div>';

		$(html).dialog({
                              dialogClass: "oc-dialog",
			      resizeable: false,
			      draggable: false,
			      height: 500,
			      width: 600
		});
		
	      

	        

		$('body').append('<div class="modalOverlay">');
                

	        $('.oc-dialog-close').live('click', function(){
                      $(".oc-dialog").hide();
		      $('.modalOverlay').remove();
		});
                
		$('.ui-helper-clearfix').css("display", "none");

	        $("#download_logs").live('click', function() {
                       OC.redirect(OC.linkTo('kubernetes', 'ajax/getLogs.php') + '?pod=' + pod );
				    
			});
			


	});

	$(document).click(function(e){
          if (!$(e.target).parents().filter('.oc-dialog').length && !$(e.target).parents().filter('.name').length){
               $(".oc-dialog").hide();
	       $('.modalOverlay').remove();
	  }
	});
        
});


