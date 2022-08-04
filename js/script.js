//////////// begin getPods helper functions /////////////
function getRowElementPlain(name, value) {
	return "\n <td>\n  <div column='" + name + "'>\n   <span>" + value + "</span>\n  </div>\n </td>";
}

function getRowElementView(name, pod_data) {
	if (~pod_data['status'].indexOf("Running")) {
		if (pod_data['url'].length) {
			return "\n <td>\n  <div column='" + name + "'>\n   <span><a href='" + pod_data['url'] +
				"'>"+ pod_data['url']+"</a></span>\n  </div>\n </td>";
		} else {
			return getRowElementPlain(name, "none");
		}
	}
	return getRowElementPlain(name, "wait");
}

function getSshRows(pod_data) {
	var str = ""
	if (pod_data['ssh_url'].length) {
		str += "\n <tr><td class='expanded-column-name'>ssh access:</td> <td class='expanded-column-value'><span class='expanded-row-ssh-url'><a href='" + pod_data['ssh_url'] + "'>"+ pod_data['ssh_url'] + "</a></span></td></tr>"
		if (pod_data['ed25519_hostkey'].length) {
			str += "\n <tr><td class='expanded-column-name'>ed25519 hostkey:</td> <td class='expanded-column-value'><span> SHA256: " + pod_data['ed25519_hostkey'] + "</span></td></tr>"
		}
		if (pod_data['rsa_hostkey'].length) {
			str += "\n <tr><td class='expanded-column-name'>rsa hostkey:</td> <td class='expanded-column-value'><span> SHA256: " + pod_data['rsa_hostkey'] + "</span></td></tr>"
		}
	}
	return str
}

function getTokenRows(pod_data) {
	var str = "";
	if (pod_data['tokens']) {
		tokens = Object.keys(pod_data['tokens'])
		for (token of tokens) {
			str += "\n <tr><td class='expanded-column-name'>" + token + ":</td> <td class='expanded-column-value'><span>" + pod_data['tokens'][token] + "</span></td></tr>";
		}
	}
	return str;
}

function getExpandedTable(pod_data) {
	var str = "\n <tr hidden class='expanded-row' pod_name='" + pod_data['pod_name'] + "'> <td colspan='5'>" +
		"\n<table id='expanded-" + pod_data['pod_name'] + "' class='panel expanded-table'>" +
		"\n <tr><td class='expanded-column-name'>container name:</td> <td class='expanded-column-value'><span>" + pod_data['container_name'] + "</span></td></tr>" +
		"\n <tr><td class='expanded-column-name'>image name:</td> <td class='expanded-column-value'><span>" + pod_data['image_name'] + "</span></td></tr>" +
		"\n <tr><td class='expanded-column-name'>pod IP:</td> <td class='expanded-column-value'><span>" + pod_data['pod_ip'] + "</span></td></tr>" +
		"\n <tr><td class='expanded-column-name'>node IP:</td> <td class='expanded-column-value'><span>" + pod_data['node_ip'] + "</span></td></tr>" +
		"\n <tr><td class='expanded-column-name'>owner:</td> <td class='expanded-column-value'><span>" + pod_data['owner'] + "</span></td></tr>" +
		"\n <tr><td class='expanded-column-name'>age:</td> <td class='expanded-column-value'><span>" + pod_data['age'] + "</span></td></tr>" +
		getTokenRows(pod_data) +
		"\n</table>" +
		"\n </td> </tr>";
	return str;
}

function formatStatusRunning(status) {
	if (~status.indexOf("Running")) {
		var date = status.substr(status.indexOf(':') + 1);
		var time = new Date(date).toString().slice(0, 25);
		return "Running: ".concat(time);
	}
	return status;
}

function getRow(pod_data) {
	//visible part
	var str = "  <tr class='simple-row' pod_name='" + pod_data['pod_name'] + "'>" +
		getRowElementPlain('pod_name', pod_data['pod_name']) +
		getRowElementPlain('status', formatStatusRunning(pod_data['status'])) +
		getRowElementView('view', pod_data) +
		"\n<td class='td-button'><a href='#' title=" + t('user_pods', 'Expand') + " class='expand-view permanent action icon icon-down-open'></a></td>" +
		"\n<td class='td-button'><a href='#' title=" + t('user_pods', 'Delete pod') + " class='delete-pod permanent action icon icon-trash-empty'></a></td>" +
		"\n</tr>";
	//expanded information
	str += getExpandedTable(pod_data);
	return str;
}

function updateContainerCount() {
	var count_shown = $('table#podstable tbody#fileList').children('tr.simple-row').length;
	$('table#podstable tfoot.summary tr td span.info').remove();
	$('table#podstable tfoot.summary tr td').append("<span class='info' pods='" + count_shown + "'>" +
		count_shown + " " + (count_shown === 1 ? t("user_pods", "pod") : t("user_pods", "pods")) +
		"</span>");
}

//////////// begin other helper functions /////////////
function formatEnvVarName(name) {
	return name[0].toUpperCase() + name.slice(1).toLowerCase().replace('_', ' ');
}

function addContainerSettings(container_info) {
	var main_id = 'container_' + container_info.name;
	$('#container_settings').append('<div id="' + main_id + '" container_name="' + container_info.name +
		'" class="container_header"></div>');
	$('#container_settings #' + main_id).append('<span><strong>' + container_info.name +
		'</strong> container settings:</span>');
	for (var env_var in container_info.env) {
		var req = container_info.env[env_var][1];
		$('#container_settings #' + main_id).append('<div class="container_setting">\n' +
			'<span>' + formatEnvVarName(env_var) + (req ? '*' : '') + ':</span>\n' +
			'<input type="text" value="' + container_info.env[env_var][0] + '" ' +
			'placeholder="' + container_info.env[env_var][0] + '" ' +
			(req ? 'required' : '') +
			' env_name="' + env_var + '"></input></div>');
	}
}

function getContainerSettingsInput() {
	var input = {};
	if ($('#container_settings').children().length) { // if there are any containers with settings
		$('#container_settings').children('div').each(function(index) { // then for each container,
			var container_settings = {};
			$(this).children('div.container_setting').each(function(index) { // make an object of settings,
				if (!container_settings === false) { // if there haven't been any missing required settings,
					var value = $(this).children('input').val();
					if (value === "" && $(this).children('input').prop('required')) { // if a required setting is missing,
						container_settings = false;
						return false;
					}
					container_settings[$(this).children('input').attr('env_name')] = value;
				}
			});
			if (!container_settings === false) { // if no required settings were missing
				input[$(this).attr('container_name')] = container_settings;
			} else {
				input = false;
				return input;
			}
		});
	}
	return input;
}

//////////// begin core api functions /////////////
function getPods(callback) {
	$.ajax({
		url: OC.filePath('user_pods', 'ajax', 'actions.php'),
		data: {
			action: 'get_pods',
			pod_names: ''
		},
		beforeSend: function(xhr) {
			ajaxBefore(xhr, "Retrieving table data...");
		},
		success: function(jsondata) {
			if (jsondata.status == 'success') {
				var expanded_views = [];
				// make an array of the podnames whose views are expanded
				$('#podstable #fileList tr.simple-row td a.icon-up-open').closest('tr').each(function() {
					expanded_views.push($(this).attr('pod_name'));
				});
				$('#podstable #fileList tr').remove();
				// remove all of the table rows, and clear any remaining tooltips
				$('body > div.tipsy').remove();
				if (jsondata.data) {
					jsondata.data.forEach(function(value, index, array) {
						$('tbody#fileList').append(getRow(value));
					});
				}
				updateContainerCount();
				$('table#podstable #fileList tr.simple-row').each(function() {
					if ($.inArray($(this).attr("pod_name"), expanded_views) !== -1) {
						toggleExpanded($(this).find('td a.expand-view'));
					}
				});
				if (callback) {
					callback();
				}
			} else if (jsondata.status == 'error') {
				if (jsondata.data && jsondata.data.error && jsondata.data.error == 'authentication_error') {
					OC.redirect('/');
				} else {
					OC.dialogs.alert(t("user_pods", "get_pods: Something went wrong..."), t("user_pods", "Error"));
				}
			}
		},
		complete: function(xhr) {
			ajaxCompleted(xhr);
		}
	});
}

function createPod(yaml_file, settings_input) {
	$.ajax({
		url: OC.filePath('user_pods', 'ajax', 'actions.php'),
		data: {
			action: 'create_pod',
			yaml_file: yaml_file,
			input: settings_input
		},
		method: 'post',
		beforeSend: function(xhr) {
			ajaxBefore(xhr, "Creating pod...");
		},
		success: function(jsondata) {
			if (jsondata.status == 'success') {
				if (jsondata.pod_name) {
					getPods();
					// if a previous run_pod call has outstanding timeouts, clear them
					$.createPodTimeouts.forEach(function(timeout) {
						clearTimeout(timeout);
					});
					$.createPodTimeouts = [];
					$.createPodTimeouts.push(setTimeout(function() {
						getPods();
					}, 10000));
					$.createPodTimeouts.push(setTimeout(function() {
						getPods();
					}, 30000));
					$.createPodTimeouts.push(setTimeout(function() {
						getPods();
					}, 60000));
				} else {
					OC.dialogs.alert(t("user_pods", "create_pod: Something went wrong..."), t("user_pods", "Error"));
				}
			} else if (jsondata.status == 'error') {
				if (jsondata.data && jsondata.data.error && jsondata.data.error == 'authentication_error') {
					OC.redirect('/');
				} else if (jsondata.message) {
					OC.dialogs.alert(t("user_pods", "run_pod: " + jsondata.message), t("user_pods", "Error"));
				}
			}
		},
		complete: function(xhr) {
			ajaxCompleted(xhr);
		}
	});
}

function deletePod(podName) {
	$.ajax({
		url: OC.filePath('user_pods', 'ajax', 'actions.php'),
		data: {
			action: "delete_pod",
			pod_name: podName
		},
		method: 'post',
		beforeSend: function(xhr) {
			ajaxBefore(xhr, "Deleting your pod...");
			$('#podstable tr[pod_name="' + podName + '"] td a.delete-pod').hide();
			$('#podstable tr[pod_name="' + podName + '"] td div[column=status] span').text('Deleting');
		},
		complete: function(xhr) {
			ajaxCompleted(xhr);
		},
		success: function(data) {
			if (data.status == 'success') {
				$('tr[pod_name="' + data.pod_name + '"]').remove();
				// if a tooltip is shown when the element is removed, then there is no mouseover event to get rid of it.
				$('body > div.tipsy').remove();
				updateContainerCount();
			} else if (data.status == 'error') {
				if (data.data && data.data.error && data.data.error == 'authentication_error') {
					OC.redirect('/');
				} else {
					OC.dialogs.alert(t("user_pods", "delete_pod: Something went wrong..."), t("user_pods", "Error"));
					$('#podstable tr[pod_name="' + podName + '"] td a.delete-pod').show();
					$('#podstable tr[pod_name="' + podName + '"] td div[column=status] span').text('Delete failed');
				}
			}
		}
	});
}

//////////// begin page interaction functions /////////////
function toggleExpanded(expander) {
	if (expander.attr("class").search("icon-up-open") === -1) {
		expander.closest('tr').next().show();
		expander.removeClass("icon-down-open").addClass("icon-up-open");
	} else {
		expander.closest('tr').next().hide();
		expander.removeClass("icon-up-open").addClass("icon-down-open");
	}
}

function loadYaml(yaml_file) {
	$('#public_key').val('');
	var select_value = yaml_file || $('#yaml_file').val();
	if (!select_value) {
		$('div#storage').hide();
		$('div#ssh').hide();
		$('#webdav').empty();
	}
	if (!select_value) {
		return;
	}
	$.ajax({
		url: OC.filePath('user_pods', 'ajax', 'actions.php'),
		method: 'post',
		data: {
			action: 'check_manifest',
			yaml_file: select_value
		},
		beforeSend: function(xhr) {
			ajaxBefore(xhr, "Retrieving YAML...");
		},
		complete: function(xhr) {
			ajaxCompleted(xhr);
		},
		success: function(jsondata) {
			if (jsondata.status == 'success') {
				var yaml_url = jsondata.data['manifest_url'].replace(/^https:\/\/raw\.githubusercontent\.com\/deic-dk\/pod_manifests\/main\//,
					'https://github.com/deic-dk/pod_manifests/blob/main/');
				var link = '<span><a href=\'' + yaml_url + '\'target="_blank">YAML source</a></span>';
				$('#links').empty();
				$('#links').append(link);
				$('#description').empty();
				$('#description').append(marked(jsondata.data['manifest_info']));
				$('#container_settings').empty();
				$('#container_settings').hide();
				jsondata.data.container_infos.forEach(container => {
					if (Object.entries(container.env).length) {
						$('#container_settings').show();
						addContainerSettings(container);
					}
				});
			} else if (jsondata.status == 'error') {
				if (jsondata.data && jsondata.data.error && jsondata.data.error == 'authentication_error') {
					OC.redirect('/');
				}
			}
		}
	});
}

function toggleNewpod() {
	$('#newpod').slideToggle();
	$('#pod-create').toggleClass('btn-primary');
	$('#pod-create').toggleClass('btn-default');
	$('#newpod #ok a').toggleClass('btn-default');
	$('#newpod #ok a').toggleClass('btn-primary');
}

// Before each ajax call, display the loading gif, and add the ajax request to the array $.xhrPool,
// so that it can keep other completed calls from removing the loading gif
function ajaxBefore(xhr, loadingString) {
	$.xhrPool.push(xhr);
	$("#loading-text").text(t("user_pods", loadingString));
	$('#loading').show();
}

function ajaxCompleted(xhr) {
	var index = $.xhrPool.indexOf(xhr);
	if (index > -1) {
		$.xhrPool.splice(index, 1);
	}
	if (!$.xhrPool.length) {
		$('#loading').hide();
	}
}

$(document).ready(function() {

	var hostname = $(location).attr('host');

	$.createPodTimeouts = [];
	$.xhrPool = [];

	$('a#pod-create').click(function() {
		toggleNewpod()
	});

	$('#newpod #cancel').click(function() {
		toggleNewpod()
	});

	$("#yaml_file").prop("selectedIndex", -1);

	$("#yaml_file").change(function() {
		loadYaml();
	});

	$('#newpod #ok').on('click', function() {
		var yaml_file = $('#yaml_file').val();
		var settings_input = getContainerSettingsInput();
		if (settings_input) {
			$('#container_settings').children('div').children('div.container_setting').children('input').each(function(index) {
				$(this).removeClass("alert");
			});
			createPod(yaml_file, settings_input);
		} else {
			OC.dialogs.alert("Please fill in missing settings", "Apply");
			$('#container_settings').children('div').children('div.container_setting').children('input').each(function(index) {
				if ($(this).prop('required') && $(this).val() === "") {
					$(this).addClass("alert");
				}
			});
		}
	});

	$("#podstable td .expand-view").live('click', function() {
		toggleExpanded($(this));
	});

	$("#podstable td .delete-pod").live('click', function() {
		var podSelected = $(this).closest('tr').find('td div[column="pod_name"] span').text().trim();
		$('#dialogalert').html("<div>" + t("user_pods", "Are you sure you want to delete the pod") + " " +
			podSelected + "?</div>");
		$('#dialogalert').dialog({
			buttons: [{
					text: 'Delete',
					click: function() {
						deletePod(podSelected);
						$(this).dialog('close');
					}
				},
				{
					text: 'Cancel',
					click: function() {
						$(this).dialog('close');
					}
				}
			]
		});
	});

	// Removed old unused function, need to reimplement a call somewhere that does:
	//		$("#download-logs").live('click', function() {
	//			OC.redirect(OC.linkTo('user_pods', 'ajax/getLogs.php') + '?file=' + pod);
	//		});

	$('#pods_refresh').click(function(e) {
		$('table#podstable tfoot.summary tr td span.info').remove();
		getPods();
	})

	getPods(function() {
		if (typeof getGetParam !== 'undefined' && getGetParam('file') && getGetParam('yaml_file')) {
			var yaml_file = decodeURIComponent(getGetParam('yaml_file'));
			var file = decodeURIComponent(getGetParam('file'));
			$('#newpod').show();
			$('#pod-create').removeClass('btn-primary');
			$('#pod-create').addClass('btn-default');
			$.when(loadYaml(yaml_file)).then(function() {
				$('#yaml_file').val(yaml_file);
				$('#file_input').val(file);
				$('#newpod #ok a').removeClass('btn-default');
				$('#newpod #ok a').addClass('btn-primary');
			});
		}
	});

});
