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

        
       // $(document).click(function(e){
         //  if (!$(e.target).parents().filter('.oc-dialog').length && !$(e.target).parents().filter('.name').length ) {
           //     $(".oc-dialog").hide();
	//	$('.modalOverlay').remove();
   
//	});


	$("#podstable .nametext").live('click', function(){
              var pod = $(this).closest('tr').attr('id');
	      var https_port =$("td[class='"+https_port+"']").find("span#https_port").html();
	      var uri =  
	      var jupi = 'https://kube.sciencedata.dk' + https_port;
	      var html = '<div><span><h3 class="oc-dialog-title" style="padding-left:25px;">Logs of <span>'+ pod+'</span></h3></span><a class="oc-dialog-close close svg"></a>\
			  <div id="meta_data_container" class=\'' + pod +'\'>\
		          <div style="position:absolute; bottom:30px; left:40px;">\
		          <div id="uri">Direct Link:<
                          <div><p>Download the logs of the container</p></div>\
		          <div style="position:absolute; bottom:50px; left:60px;">\
			  <button id="download_logs" class="download btn btn-primary btn-flat">Download</button>&nbsp\
                          </div>\
			</div>';

		$(html).dialog({
                              dialogClass: "oc-dialog",
			      resizeable: false,
			      draggable: false,
			      height: 600,
			      width: 720
		});
		
	      

	        

		$('body').append('<div class="modalOverlay">');
                

	        $('.oc-dialog-close').live('click', function(){
                      $(".oc-dialog").hide();
		      $('.modalOverlay').remove();
		});
                
		$('.ui-helper-clearfix').css("display", "none");

	
	});

	$(document).click(function(e){
          if (!$(e.target).parents().filter('.oc-dialog').length && !$(e.target).parents().filter('.name').length){
               $(".oc-dialog").hide();
	       $('.modalOverlay').remove();
	  }
	});
        
});
