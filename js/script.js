$(document).ready(function() {
	$('a#pod-create').click(function() {
		$('#newpod').slideToggle();
	});

	$('#newpod #cancel').click(function() {
		$('#newpod').slideToggle();
	});
});
