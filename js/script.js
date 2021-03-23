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


	$("#podstable .name").live('click', function(){
              var pod = $(this).closest('td').attr('id');
	      var httpsport = $(this).closest('tr').find("span#https-port").html();
	      var Uri_Jupy = $(this).closest('tr').find('span#Uri_Jupy').html(); 
	      var Uri_Jupy_all = 'https://kube.sciencedata.dk:' + httpsport + '/' + Uri_Jupy;
	      var html = '<div><span><h3 class="oc-dialog-title" style="padding-left:25px;">More info for <span>'+ pod+'</span></h3></span><a class="oc-dialog-close close svg"></a>\
			  <div id="meta_data_container" class=\'' + pod +'\'>\
		          <div style="position:absolute; top:80px; left:40px;">\
		          <div id="Uri_Jupy">Direct Link:</div><p></p>\
		          <div><a href=\''+Uri_Jupy_all+'\'target="_blank">'+ Uri_Jupy_all +'</a></div></div>\
			  <div style="position:absolute; bottom:100px; left:60px;">\
			  <div>Download the logs of the container:</div><p></p>\
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
