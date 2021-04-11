$(document).ready(function () {
    var hostname = $(location).attr('host');;

    var dockerhub_uri = 'https://hub.docker.com/r/';
    var github_uri = 'https://github.com/deic-dk/pod_manifests/blob/main/';

    $('a#pod-create').click(function () {
        $('#newpod').slideToggle();
    });

    $('#newpod #cancel').click(function () {
        $('#newpod').slideToggle();
    });

    $("#podinput").prop("selectedIndex", -1);

    $("#podinput").change(function () {
        var select_value = $(this).val()
        $.post(OC.filePath('kubernetes_app', 'ajax', 'actions.php'), {
            yaml_file: select_value
        }, function (jsondata) {
            if (jsondata.status == 'success') {
                var image_github_uri = github_uri + select_value;
                var image_dockerhub_uri = dockerhub_uri + jsondata.data.included[2];
                var dockerhub_description = jsondata.data.included[3];
                var mount_path = jsondata.data.included[4];
                var mount_path_text = "The folder is mounted in " + mount_path + " inside the container";
                var webdav_link = 'https://' + OC.currentUser + '@' + hostname + '/storage/';
                var webdav_link_ref = '<a href=\'' + webdav_link + '\'target="_blank">' + webdav_link + '</a>';
                var image_info = '<div class="box-left">Read more on <a href=\'' + image_github_uri + '\'target="_blank">GitHub</a>\
				    			<span>and on <a href=\'' + image_dockerhub_uri + '\'target="_blank">DockerHub</a></span></div>';
                $('#links').empty();
                $('#links').append(image_info);

                $('#description').empty();
                $('#description').append(dockerhub_description);

                $('#mount-path').empty();
                $('#mount-path').append(mount_path_text);

                $('.newpod-span').css('display', 'block');

                if (jsondata.data.included[0] == true) {
                    $('div#ssh').css('display', 'block');
                } else {
                    $('div#ssh').css('display', 'none');
                }
                if (jsondata.data.included[1] == true) {
                    $('div#storage').css('display', 'block');
                    $('#webdav').empty();
                    var webdav_text = 'Available at: ' + webdav_link_ref;
                    $('#webdav').append(webdav_text);

                } else {
                    $('div#storage').css('display', 'none');
                }
            }

        });
    });

    $('#newpod #ok').on('click', function () {
        var yaml_file = $('#podinput').val();
        var ssh_key = $('.sshpod').val();
        var storage = $('.storagepath').val();
        $.ajax({
            url: OC.filePath('kubernetes_app', 'ajax', 'actions.php'),
            data: {
                pod_image: yaml_file,
                ssh: ssh_key,
                storage: storage
            },
            method: 'post',
            beforeSend: function () {
                $('#podstable').css("visibility", "hidden");
                $('#pod-create').css("visibility", "hidden");
                $('#table-h').css("visibility", "hidden");
                $('#newpod').slideToggle();
                $('#newpod').val("");
                $('#loading').css("display", "block");
            },
            complete: function () {
                // Hide loading
            },
            success: function (data) {
                location.reload();

            }
        });
    });

    $("#podstable td #delete-pod").live('click', function () {
        var podSelected = $(this).closest('tr').attr('id');
        $('#dialogalert').dialog({
            buttons: [{
                id: 'test',
                'data-test': 'data test',
                text: 'Delete',
                click: function () {
                    $.post(OC.filePath('kubernetes_app', 'ajax', 'actions.php'), {
                        pod_name: podSelected
                    }, function (jsondata) {

                        if (jsondata.status == 'success') {
                            location.reload();

                        }

                    });

                    $.ajax({
                        url: OC.filePath('kubernetes_app', 'ajax', 'actions.php'),
                        data: {
                            pod_name: podSelected
                        },
                        method: 'post',
                        beforeSend: function () {
                            $("#podstable").css("visibility", "hidden");
                            $("#pod-create").css("visibility", "hidden");
                            $("#table-h").css("visibility", "hidden");
                            $("#loading-text").text("Deleting your pod... Please wait");
                            $("#loading").css("display", "block");
                        },
                        complete: function () {

                        },
                        success: function (data) {
                            location.reload();
                        }
                    });

                    $(this).dialog('close');
                }
            },
            {
                id: 'test2',
                'data-test': 'data test',
                text: 'Cancel',
                click: function () {
                    $(this).dialog('close');
                }
            }]
        });

    });

    $("#podstable > tbody > tr").each(function () {
        var value = $(this).find("td span#status").text();
        if (~value.indexOf("Running")) {
            var date = value.substr(value.indexOf(':') + 1);
            var time = new Date(date).toString().slice(0, 25);
            var finalDate = "Running: ".concat(time);
            $(this).find("td span#status").text(finalDate);
        }
    });


    $("#podstable .name").live('click', function () {
        var pod = $(this).closest('td').attr('id');
        var https_port = $(this).closest('tr').find("span#https_port").html();
        var uri = $(this).closest('tr').find('span#uri').html();
        var complete_uri = 'https://kube.sciencedata.dk:' + https_port + '/' + uri;

        var image = $(this).closest('tr').find('span#image').html();
        var image_uri = dockerhub_uri + image;
        var html = '<div><span><h3 class="oc-dialog-title"><span>' + pod + '</span></h3></span><a class="oc-dialog-close close svg"></a>\
                    <div id="meta_data_container" class=\'' + pod + '\'>\
                    <div class="image-title">Original image on Docker Hub:\
                    <div><a href=\'' + image_uri + '\'target="_blank">' + image_uri + '</a></div></div>\
                    <div class="uri-title">\
                    <div id="uri">Access web service:</div>\
                    <div><a href=\'' + complete_uri + '\'target="_blank">' + complete_uri + '</a></div></div>\
                    <div class="logs-panel">\
                    <div>Download the logs of your container:</div><p></p>\
                    <button id="download-logs" class="download btn btn-primary btn-flat">Download</button>&nbsp\
                    </div>\
                    </div>';

        $(html).dialog({
            dialogClass: "oc-dialog",
            resizeable: false,
            draggable: false,
            height: 400,
            width: 600
        });

        $('body').append('<div class="modalOverlay">');

        $('.oc-dialog-close').live('click', function () {
            $(".oc-dialog").hide();
            $('.modalOverlay').remove();
        });

        $('.ui-helper-clearfix').css("display", "none");

        $("#download-logs").live('click', function () {
            OC.redirect(OC.linkTo('kubernetes_app', 'ajax/actions.php') + '?pod=' + pod);
        });
    });

    $(document).click(function (e) {
        if (!$(e.target).parents().filter('.oc-dialog').length && !$(e.target).parents().filter('.name').length) {
            $(".oc-dialog").hide();
            $('.modalOverlay').remove();
        }
    });
});
