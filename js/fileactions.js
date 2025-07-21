function showNbViewer(dir, file, id, owner){
	if(typeof FileList !== 'undefined'){
		FileList.showMask();
	}
	var nbviewerSrc = OC.webroot+'/apps/user_pods/nbviewer.php?dir='+dir+'&file='+file+'&id='+id+'&owner='+owner;
	var path = dir+'/'+file;
	var iframe = $('<iframe id="nbframe" path="'+path+'" src="'+nbviewerSrc+'" sandbox="allow-scripts allow-same-origin" />');
	if($('#app-content').length){
		$('#app-content').append(iframe);
	}
	else if($('#app-content-public').length){
		$('#app-content-public').append(iframe);
	}
	else{
		return false;
	}
	$('iframe#nbframe').load(function(){
		var jupyter_yaml_file = $(this).contents().find('head').find('meta[name="Jupyter_YAML_File"]').attr('content');
		var link = OC.linkTo('user_pods', 'index.php') + '?yaml_file=' + jupyter_yaml_file + '&file=' + path;
		var view;
		if(typeof FileList!=='undefined'){
			view = FileList.getGetParam('view');
		}
		var ref = OC.linkTo('files', 'index.php') + '?dir=' + dir + '&file=' + file + (view&&view!=''?'&view='+view:'');
		var dirref = OC.linkTo('files', 'index.php') + '?dir=' + dir + (view&&view!=''?'&view='+view:'');
		if(view=='' || view=='files'|| view=='sharingin'){
			window.history.pushState( {service: 'files', yaml_file: jupyter_yaml_file, file: path}, '', ref);
		}
		$(this).contents().find('head').append('<link rel="stylesheet" id="nbstyle" type="text/css" href="'+OC.webroot+'/apps/user_pods/css/nbviewer.css" />');
		$(this).contents().find('body').prepend('<div id="nbbar">'+(!$('#app-content-public').length&&!$('#app-content-sharingin:visible').length&&!$('.crumb a[data-id=sharing_in]:visible').length?
				'<a id="run" href="'+link+'">'+t('user_pods', 'Run')+'</a>':'')+
				'<a id="close" title="'+t('user_pods', 'Close')+'">&#10006;</a></div>');
		$(this).contents().find('#close').click(function(){
			$('iframe#nbframe').remove();
			$('#nbbar').remove();
			var view;
			if(typeof FileList!=='undefined'){
				view = FileList.getGetParam('view');
			}
			if(view=='' || view=='files'|| view=='sharingin'){
				$('#app-content-files.viewcontainer.inuse').removeClass('hidden').removeClass('inuse');
				$('#app-content-sharingin.viewcontainer.inuse').removeClass('hidden').removeClass('inuse');
				window.history.pushState( {service: 'files',  dir: dir}, '', dirref);
				//window.history.back();
			}
			$('#app-content-public #preview').removeClass('hidden');
		});
		$(this).contents().find('#run').click(function(){
			OC.redirect(link);
		});
		if(typeof FileList !== 'undefined'){
			FileList.hideMask();
		}
		$('#app-content-files.viewcontainer:visible').addClass('hidden').addClass('inuse');
		$('#app-content-sharingin.viewcontainer:visible').addClass('hidden').addClass('inuse');
		$('#app-content-public #preview').addClass('hidden');
	});
}

$(document).ready(function() {
	if (OCA.Files) {
		/*FileActions.register('application/x-ipynb+json', 'Open', OC.PERMISSION_READ, '', function (filename, context) {
			showNbViewer(context.dir, filename, context.id, context.owner);
		});*/
		OCA.Files.fileActions.register('application/x-ipynb+json', 'View', OC.PERMISSION_READ, '', function (filename, context) {
			showNbViewer(context.dir, filename, context.id, context.owner);
		});
		/*OCA.Files.fileActions.register('application/x-ipynb+json', 'Open', OC.PERMISSION_READ, '', function (filename, context) {
			showNbViewer(context.dir, filename, context.id, context.owner);
		});*/
		OCA.Files.fileActions.setDefault('application/x-ipynb+json', 'View');
		//OCA.Files.fileActions.setDefault('application/x-ipynb+json', 'Open');
	}
	$('#app-content-public #imgframe img.publicpreview.ipynb').click(function(){
		showNbViewer($('#dir').val(), $('#filename').val(), $('#fileid').val(), $('#owner').val());
	});
	$(window).on('popstate', function() {
		$('iframe#nbframe').remove();
		$('#nbbar').remove();
		$('#app-content-files.viewcontainer.inuse').removeClass('hidden').removeClass('inuse');
		$('#app-content-sharingin.viewcontainer.inuse').removeClass('hidden').removeClass('inuse');
		$('#app-content-public #preview').removeClass('hidden');
  });
});

