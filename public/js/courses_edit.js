$(() => {
    let cropper, file;
    let hasChangedImage = false;
    let originalRole, isRoleDirty;

    $('#cropperModal').modal({
        show: false,
        backdrop: 'static'
    });

    $(window).resize(function() {
        // A hack for changing the roles row static with the same height as its container
        // #roles div.row has a different view on smaller screens (horizontal instead of vertical) so if the screen is smaller than 991px change the height to auto
        if ($(window).width() > 991) {
            $('#roles div.row').css('position', 'absolute');
            $('#roles').height($('#roleFields').height());
            $('#roles div.row').css('position', 'static');
        } else {
            $('#roles').height('auto');
        }
    });

    // Trigger when the DOM loads to have the correct size in the #roles div.row
    $(window).trigger('resize');

    $('#courseInfoForm').on('change', ':input', function() {
        // Check all inputs and also hasChangedImage because the file input is resetted every time a file is selected so it is always empty
        if (tinymce.activeEditor.isDirty()
            || hasChangedImage
            || $('#courseTitle').val() !== course.title
            || $('#courseDifficulty').val() !== course.difficulty
            || $('#courseTopic').val() !== course.topic) {
            $('#courseInfoSubmit').show();
            $('#courseInfoReset').show();
        } else {
            $('#courseInfoSubmit').hide();
            $('#courseInfoReset').hide();
        }
    });

    $('#courseInfoReset').click(function() {
        // Set all inputs to their original value
        // Course variable comes from the blade view in a script tag
        $('#courseTitle').val(course.title);
        $('#courseImage').attr('src', publicStorage + course.image);
        tinymce.activeEditor.setContent(course.description);
        $('#courseDifficulty').val(course.difficulty);
        $('#courseTopic').val(course.topic);
        $('#courseInfoSubmit').hide();
        $('#courseInfoReset').hide();
        hasChangedImage = false;
    });

    $('#courseInfoForm').submit(function(e) {
        // Prevent form submit
        e.preventDefault();
        // Start loading and call stop when the ajax is finished
        startSwalLoading();
        let formData = new FormData($(this)[0]);
        // Save ajax options in a json because we have 2 ajax calls with the same options
        let ajaxOptions = {
            url: `${assetUrl}courses/${course.id}`,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            method: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (data) {
                stopSwalLoading(Cookies.get('language') == 'en' ? 'Your changes were saved !' : 'Cambios guardados');
                removeErrors($('#courseInfoForm :input'));
                course = data.course;
                $('#courseInfoSubmit').hide();
                $('#courseInfoReset').hide();
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                let response = XMLHttpRequest.responseJSON;
                if (response) {
                    if (response.errors) {
                        if (response.errors.title) {
                            placeError($('#courseTitle'), response.errors.title);
                        } else if (response.errors.image) {
                            placeError($('#courseImageUpload'), response.errors.image);
                        } else if (response.errors.description) {
                            placeError($('#courseDescription'), response.errors.description);
                        } else if (response.errors.difficulty) {
                            placeError($('#courseDifficulty'), response.errors.difficulty);
                        } else if (response.errors.topic) {
                            placeError($('#courseTopic'), response.errors.topic);
                        }
                    } else {
                        Swal.fire('Error', response.error);
                    }
                }
            },
        }
        // Append the _method with PUT to make a put request instead of POST, the ajax request must be also post to work
        formData.append('_method', 'PUT');
        if (file) {
            file.toBlob((blob) => {
                formData.append('image', blob);
                $.ajax(ajaxOptions);
            });
        } else {
            $.ajax(ajaxOptions);
        }
    });

    $('#courseImageUpload').change(function() {
        file = $(this).prop('files')[0];
        $(this).val('');
        let reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function () {
            $('#cropperModal').modal('show');
            $('.cropper-img').attr('src', reader.result);
            $('.previewImg').attr('src', reader.result);
        };
    });

    $('#saveCroppedImg').click(function() {
        file = cropper.getCroppedCanvas({
            width: 1920,
            height: 1080
        });
        $('#courseImage').attr('src', cropper.getCroppedCanvas().toDataURL());
        $('#cropperModal').modal('hide');
        hasChangedImage = true;
        $('#courseInfoSubmit').show();
        $('#courseInfoReset').show();
    });

    $('#cropperModal').on('shown.bs.modal', function () {
        let cropperContainer = $('.cropper-img').parent()[0];

        new Cropper($('.cropper-img')[0], {
            aspectRatio: 16/9,
            viewMode: 3,
            dragMode: 'move',
            toggleDragModeOnDblclick: false,
            crop: function(e) {
                cropper = this.cropper;
                cropper.getCroppedCanvas({
                    width: 1920,
                    height: 1080
                });
                // Code for making a container to preview the cropped image
                // let data = e.detail;
                // let imageData = cropper.getImageData();
                // let previewAspectRatio = data.width / data.height;
                //
                // let previewImage = $('.previewImg')[0];
                // let previewContainer = $('.preview')[0];
                // let previewWidth = previewContainer.offsetWidth;
                // let previewHeight = previewWidth/previewAspectRatio;
                // let imageScaledRatio = data.width / previewWidth;
                //
                // previewContainer.style.height = previewHeight + 'px';
                // previewImage.style.width = imageData.naturalWidth / imageScaledRatio + 'px';
                // previewImage.style.height = imageData.naturalHeight / imageScaledRatio + 'px';
                // previewImage.style.marginLeft = -data.x / imageScaledRatio + 'px';
                // previewImage.style.marginTop = -data.y / imageScaledRatio + 'px';
                // $('.preview').attr('src', cropper.getCroppedCanvas().toDataURL());
            }
        });
    });

    $('#cropperModal').on('hidden.bs.modal', function () {
        cropper.destroy();
    });

    $('#cancelCroppedImg').on('click', function () {
        $('#cropperModal').modal('hide');
    });


    // Roles management

    $('#roles').on('click', '.role', function() {
        // If the dirty flag is true then ask if you want to save changes
        if (isRoleDirty) {
            let button = this;
            Swal.fire({
                title: Cookies.get('language') == 'en' ? 'You have unsaved changes' : 'Tienes cambios sin guardar',
                text: Cookies.get('language') == 'en' ? 'Do you want to save your changes ?' : '¿Quieres guardar los cambios?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: Cookies.get('language') == 'en' ? "Save changes" : 'Guardar cambios',
                cancelButtonText: Cookies.get('language') == 'en' ? 'Discard changes' : 'Descartar cambios',
                allowOutsideClick: false,
            }).then((result) => {
                if (result.value) {
                    $('#rolesForm').trigger('submit');
                } else {
                    isRoleDirty = false;
                }
            }).then(() => {
                showRole(this);
            });
        } else {
            showRole(this);
        }
    });

    // The button click function is not an anonymous function because I also use it when the user has unsaved changes
    function showRole(button) {
        startSwalLoading();
        // Disable the event because I have used the change trigger
        $('#rolesForm').off('change', ':input');
        $('.role').removeClass('selected');
        $(button).addClass('selected');
        // Hiding the roleSearchUsers could be moved to a function since it is used multiple times
        $('#roleSearchUsers').hide();
        $('#roleSearchUsers input').val("");
        $('#roleSearchUsers input').css('borderBottomLeftRadius', '');
        $('#roleSearchUsersResult').empty();
        $('#roleSearchUsersResult').hide();

        $.ajax({
            url: `${assetUrl}courses/${course.id}/roles/${$(button).attr('data-id')}`,
            dataType: 'json',
            success: function(data) {
                Swal.close();
                originalRole = data;
                $('#roleUnsavedChanges').hide();
                $('#deleteRole').hide();
                // Populate inputs with role data
                $('#roleName').val(data.name);
                $('#roleDescription').val(data.description);
                $('#roleBindedTo').val(data.binded_to.id);
                // Force change event to execute
                $('#roleBindedTo').trigger('change');
                $('#rolePrice').val(data.price);
                $('#roleTargetLevel').val(data.target_level);
                $('#role').val(data.target_level);

                let editable = isCourseCreator || isSuperAdmin || $('.ownRole').attr('data-targetLevel') > $('.role.selected').attr('data-targetLevel');
                if (editable) {
                    $('#deleteRole').show();
                    // Remove disabled from the inputs
                    $('#rolesForm input:not(.ungranted):not(#rolePrice), #roleName, #roleDescription, #roleBindedTo, #roleBindedTo option').prop('disabled', false);
                } else {
                    $('#deleteRole').hide();
                    // Keep the disabled property because the user doesn't have enough target level
                    $('#rolesForm input, #roleName, #roleDescription, #roleBindedTo, #roleBindedTo option, #rolePrice').prop('disabled', true);
                }

                // Remove errors
                removeErrors($('#roleName'), $('#roleDescription'), $('#roleBindedTo'), $('#rolePrice'), $('#roleTargetLevel'));

                $('#privileges input[type="checkbox"]').prop('checked',false);
                for (let privilege of data.privileges) {
                    $(`#${privilege.name}Privilege`).prop('checked', true);
                    $(`#${privilege.name}Privilege`).trigger('change');
                }

                $('#rolesForm').on('change', ':input', rolesDirty);
            },
        });
    }

    function rolesDirty() {
        let checkedPrivileges = [];
        let originalPrivileges = [];
        $('#privileges :checked').each(function() {
            checkedPrivileges.push(parseInt($(this).val()));
        });
            for (let privilege of originalRole.privileges) {
                originalPrivileges.push(privilege.id);
            }

        //If something is not equal to its original value then change the dirty flag and show the unsaved changes and viceversa
        if (!privilegesEqual(checkedPrivileges, originalPrivileges)
        || $('#roleName').val() !== originalRole.name
        || $('#roleDescription').val().replace(/\n/gm, '\r\n') !== originalRole.description
        || $('#roleBindedTo').val() != originalRole.binded_to.id
        || $('#rolePrice').val() != originalRole.price
        || $('#roleTargetLevel').val() != originalRole.target_level) {
            $('#roleUnsavedChanges').show();
            isRoleDirty = true;
        } else {
            $('#roleUnsavedChanges').hide();
            isRoleDirty = false;
        }
    };

    $('#privileges').on('change', 'input[type="checkbox"]', function() {
        if ($(this).attr('id') == 'adminPrivilege') {
            $('#privileges input[type="checkbox"]').prop('checked', $(this).prop('checked'));
            if ($('#rolesForm input.ungranted').length) {
                $('#privileges input[type="checkbox"]').prop('disabled', true);
            }
        } else if ($('#adminPrivilege').prop('checked')) {
            $('#adminPrivilege').prop('checked', false);
        } else if (!$('#adminPrivilege').prop('checked')) {
            let check = true;
            for (let checkbox of $('#privileges input[type="checkbox"]')) {
                if (!$(checkbox).prop('checked') && $(checkbox).attr('id') != 'adminPrivilege') {
                    check = false;
                    break;
                }
            }
            if (check) {
                $('#adminPrivilege').prop('checked', true);
            }
        }
    });

    $('#roleBindedTo').change(function() {
        // Show and hide inputs depending on the selected option

        // supporterBindId comes from the blade view in a script tag
        let selectedSupporter = $(this).find('option:selected').val() == supporterBindId;
        $('#rolePriceContainer').toggle(selectedSupporter);
        $('#rolePrice').prop('disabled', !selectedSupporter);

        // specificUserBindId comes from the blade view in a script tag
        let selectedSpecific = $(this).find('option:selected').val() == specificUsersBindId
        $('#roleSearchUsers').toggle(selectedSpecific);
        $('#roleSearchUsers input').prop('disabled', !selectedSpecific);
        $('#roleSearchUsersResult').css('width', $('#roleSearchUsers input').css('width'));
    });

    $('#roleSearchUsers').on('keyup', 'input', function() {
        if ($(this).val().length == 0) {
            $('#roleSearchUsersResult').hide();
            $('#roleSearchUsers input').css('borderBottomLeftRadius', '');
        } else {
            (function(searchTerm) {
                $.ajax({
                    url: usersIndex,
                    dataType: 'json',
                    data: {username: searchTerm, customRole: originalRole.id},
                    success: function (data) {
                        if (data.length) {
                            $('#roleSearchUsers input').css('borderBottomLeftRadius', '0');
                            $('#roleSearchUsersResult').show();
                            $('#roleSearchUsersResult').empty();
                            for (let user of data) {
                                $('#roleSearchUsersResult').append(`<div class="pl-3 pb-1 pt-1 userResult" data-userid="${user.id}">${user.username}#${user.suffixId}</div>`);
                            }
                        } else {
                            $('#roleSearchUsers input').css('borderBottomLeftRadius', '');
                            $('#roleSearchUsersResult').hide();
                            $('#roleSearchUsersResult').empty();
                        }
                    },
                });
            }) ($(this).val())
        }
    });

    $('#roleSearchUsersResult').on('click', '.userResult', function() {
        $('#roleSearchUsers input').val($(this).text());
        $('#roleSearchUsersResult').hide();
        $('#roleSearchUsers input').css('borderBottomLeftRadius', '');
    });

    $('#roleAddUser').click(function() {
        if ($('#roleSearchUsers input').val().length == 0) {
            Swal.fire({
                title: 'Error',
                text: Cookies.get('language') == 'en' ? "Type the username and suffix first" : "Escribe el nombre de usuario y sufijo primero",
                icon: 'warning'
            })
        } else {
            startSwalLoading();
            (function(username) {
                $.ajax({
                    url: `${assetUrl}courses/${course.id}/roles/${originalRole.id}/addUser`,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    method: 'POST',
                    data: {username: username},
                    dataType: 'json',
                    success: function (data) {
                        stopSwalLoading(`User ${username} successfully added to the role`);
                        Swal.fire({
                            text: Cookies.get('language') == 'en' ?
                                `Added user ${data.user.username}#${data.user.suffixId} to role ${originalRole.name}` :
                                `Usuario ${data.user.username}#${data.user.suffixId} añadido al rol ${originalRole.name}`,
                            icon: 'success',
                            timer: 3000,
                        });
                        refreshUsers();
                        $('#roleSearchUsers input').val('');
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        let response = XMLHttpRequest.responseJSON;
                        if (response) {
                            Swal.fire('Error', response.error);
                        }
                    },
                });
            }) ($('#roleSearchUsers input').val())
        }
    });

    $('#privileges').on('change', '#editLessonsPrivilege', function() {
        $('#seeLessonsPrivilege').prop('checked', true);
    });

    $('#privileges').on('change', '#seeLessonsPrivilege', function() {
        if (!$(this).prop('checked')) {
            $('#editLessonsPrivilege').prop('checked', false);
        }
    });

    $('#privileges').on('change', '#editPricesPrivilege', function() {
        $('#editRolesPrivilege').prop('checked', true);
    });

    $('#privileges').on('change', '#editRolesPrivilege', function() {
        if (!$(this).prop('checked')) {
            $('#editPricesPrivilege').prop('checked', false);
        }
    });

    $('#resetRole').on('click', function() {
        showRole($('.role.selected'));
        isRoleDirty = false;
    });

    $('#rolesForm').on('submit', function(e) {
        //Lost like 4 hours with this method
        // Must submit a POST request with a field name '_method' and value 'PUT' or the parameters won't reach the controller
        e.preventDefault();
        startSwalLoading();
        if ($('.role.selected').length) {
            let formData = new FormData($(this)[0]);
            formData.append('_method', 'PUT');
            $.ajax({
                url: `${assetUrl}courses/${course.id}/roles/${$('.role.selected').attr('data-id')}`,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                method: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(data) {
                    originalRole = data.customRole;
                    $(`.role[data-id="${originalRole.id}"]`).text(originalRole.name);
                    isRoleDirty = false;
                    $('#roleUnsavedChanges').hide();
                    removeErrors($('#roleName'), $('#roleDescription'), $('#roleBindedTo'), $('#rolePrice'), $('#roleTargetLevel'));
                    stopSwalLoading(Cookies.get('language') == 'en' ? 'Changes successfully saved !' : 'Cambios guardados');
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    let response = XMLHttpRequest.responseJSON;
                    stopSwalLoading();
                    if (response) {
                        if (response.errors) {
                            if (response.errors.name) {
                                placeError($('#roleName'), response.errors.name);
                            } else if (response.errors.description) {
                                placeError($('#roleDescription'), response.errors.description);
                            } else if(response.errors.bindedTo) {
                                placeError($('#roleBindedTo'), response.errors.bindedTo);
                            } else if (response.errors.price) {
                                placeError($('#rolePrice'), response.errors.price);
                            } else if (response.errors.targetLevel) {
                                placeError($('#roleTargetLevel'), response.errors.targetLevel);
                            }
                        } else {
                            Swal.fire('Error', response.error);
                        }
                    }
                },
            });
        }
    });

    $('#deleteRole').click(function() {
        Swal.fire({
            title: Cookies.get('language') == 'en' ? 'Are you sure?' : '¿Estás seguro?',
            text: Cookies.get('language') == 'en' ? 'All attached users will also lose this role' : 'Todos los usuarios con este rol lo perderán',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: Cookies.get('language') == 'en' ? 'Yes' : 'Sí',
            cancelButtonText: Cookies.get('language') == 'en' ? 'Cancel' : 'Cancelar'
        }).then((result) => {
            if (result.value) {
                let container = $('.role.selected').parent();
                (function(id) {
                    $.ajax({
                        // Destroy url, it isn't generated through name so it must be manually changed (which is a pain in the ass, should change it)
                        url: `${assetUrl}courses/${course.id}/roles/${id}`,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        dataType: 'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        method: 'DELETE',
                        success: function(data) {
                            $(container).remove();
                            $('#rolesForm input, #roleName, #roleDescription, #roleBindedTo, #roleBindedTo option, #rolePrice').prop('disabled', true);
                            $('#rolesPrice').hide();
                            $('#deleteRole').hide();
                            $('#roleUnsavedChanges').hide();
                            $('#rolesForm').trigger('reset');
                            isRoleDirty = false;
                        }
                    });
                })($('.role.selected').attr('data-id'));
            }
        })
    });

    $('#addNewRole').click(function() {
        $.ajax({
            url: `${assetUrl}courses/${course.id}/roles`,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            method: 'POST',
            dataType: 'json',
            success: function(data) {
                $(`#roles>div.row`).append(`
                    <div class="col-lg-12 col-sm-3 col-4 font-weight-bold roleContainer">
                        <button type="button" data-id="${data.id}" data-targetlevel="${data.target_level}" class="btn btn-block p-md-3 p-1 btn-hover text-center text-lg-left role">${data.name}</button>
                        <hr class="w-25 mt-1 mb-1 d-none d-lg-block">
                    </div>
                `);
            }
        });
    });

    $('#usersContainer').on('click', '.userRemoveRole', function() {
        Swal.fire({
            title: Cookies.get('language') == 'en' ? 'Are you sure ?' : '¿Estás seguro?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: Cookies.get('language') == 'en' ? 'Yes' : 'Sí',
            cancelButtonText: Cookies.get('language') == 'en' ? 'Cancel' : 'Cancelar'
        }).then((result) => {
            if (result.value) {
                let rolePill = $(this);
                (function(roleId, userId) {
                    $.ajax({
                        url: `${assetUrl}courses/${course.id}/roles/${roleId}/users/${userId}`,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        dataType: 'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        method: 'DELETE',
                        success: function() {
                            $(rolePill).parent().remove();
                            refreshUsers();
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            let response = XMLHttpRequest.responseJSON;
                            console.log(response.error);
                            if (response) {
                                if (response.error) {
                                    Swal.fire({
                                        title: 'Error',
                                        text: response.error
                                    })
                                }
                            }
                        }
                    });
                })($(this).parent().attr('data-roleid'), $(this).parent().parent().parent().attr('data-userid'))
            }
        });
    });

    $('#usersFilterUsername').on('keyup', function() {
        $.ajax({
            url: `${assetUrl}courses/${course.id}/users`,
            dataType: 'json',
            data: {
                page: 1,
                username: $('#usersFilterUsername').val(),
                role: $('#usersFilterRole').val()
            },
            success: function(data) {
                fillUsers(data);
            }
        });
    });

    $('#usersFilterRole').on('change', function() {
        $.ajax({
            url: `${assetUrl}courses/${course.id}/users`,
            dataType: 'json',
            data: {
                page: 1,
                username: $('#usersFilterUsername').val(),
                role: $('#usersFilterRole').val()
            },
            success: function(data) {
                fillUsers(data);
            }
        });
    });

    $('#usersLinks').on('click', 'a.page-link', function(e) {
        e.preventDefault();
        $.ajax({
            url: `${assetUrl}courses/${course.id}/users`,
            dataType: 'json',
            data: {
                page: $(this).attr('href').substr(-1,1),
                username: $('#usersFilterUsername').val(),
                role: $('#usersFilterRole').val()
            },
            success: function(data) {
                fillUsers(data);
            }
        });
    });
});

function refreshUsers() {
    $.ajax({
        url: `${assetUrl}courses/${course.id}/users`,
        dataType: 'json',
        data: {
            page: 1,
            username: $('#usersFilterUsername').val(),
            role: $('#usersFilterRole').val()
        },
        success: function(data) {
            fillUsers(data);
        }
    });
}

function fillUsers(data) {
    $('#usersContainer').empty();
    if (data.users.length === 0) {
        $('#usersContainer').append(
            `<div class="col-12 text-center">${Cookies.get('language') == 'en' ? "There aren't results with your current filters" : 'No hay resultados con los filtros actuales'}</div>`
        );
    }
    for (let user of data.users) {
        $('#usersContainer').append(
            `<div class="row user" data-userid="${user.id}">
                <div class="col-xl-1 col-lg-2 col-md-3 col-4">
                    <img src="${publicStorage + user.avatar}" class="img-fluid rounded-circle">
                </div>
                <div class="col-xl-2 col-lg-3 col-md-9 col-8">
                    <div class="row">
                        <div class="col-12">
                            <span>${user.username}#${user.suffixId}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <span class="text-muted">${(user.follow_courses[0] ? new Date(user.follow_courses[0].created_at).toISOString().split('T')[0] : (Cookies.get('language') == 'en' ? "Not following" : "No seguidor"))}</span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-7 col-12 mt-3 mt-lg-0 userRolesList">
                </div>
            </div>
            <hr class="w-75">`
        );
        for (let customRole of user.custom_roles) {
            $(`.user[data-userid="${user.id}"] .userRolesList`).append(
                `<span class="border rounded-pill pl-2 pr-2 pt-1 pb-1" data-roleid="${customRole.id}">
                    ${customRole.name}
                    ${customRole.pivot.supporter == 0 || isSuperAdmin ? '<i class="fas fa-times text-danger userRemoveRole"></i>' : ''}
                </span>`
            );
        }
    }
    $('#usersLinks').empty();
    $('#usersLinks').append(data.links);
    $('#usersContainer hr:last').remove();
}

$('#deleteCourse').click(function(e) {
    e.preventDefault();
    Swal.fire({
        title: Cookies.get('language') == 'en' ? 'Delete course' : 'Eliminar curso',
        text: Cookies.get('language') == 'en' ? "Are you sure ? This cannot be undone" : "¿Estás seguro? Esto es irreversible",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: Cookies.get('language') == 'en' ? 'Yes' : 'Sí',
        cancelButtonText: Cookies.get('language') == 'en' ? 'Cancel' : 'Cancelar'
    }).then((result) => {
        if (result.value) {
            $('#deleteCourseForm').submit();
        }
    });
});

function startSwalLoading() {
    Swal.fire({
        title: `<div><span>${Cookies.get('language') == 'en' ? 'Please wait' : 'Por favor espera'}</span><i class="fas fa-circle-notch fa-spin loading fa-sm text-center p-0 m-0 ml-2"></i></div>`,
        showConfirmButton: false,
        allowOutsideClick: false,
    });
}

function stopSwalLoading(msg = '') {
    Swal.close();
    Swal.fire({
        title: Cookies.get('lang') == 'en' ? 'Done !' : '¡Hecho!',
        text: msg,
        showConfirmButton: false,
        timer: 1500,
    });
}

function privilegesEqual(a, b) {
    if (a === b) return true;
    if (a == null || b == null) return false;

    let adminId = parseInt($('#adminPrivilege').val());
    if (a.indexOf(adminId) != -1 && b.indexOf(adminId) != -1) return true;

    if (a.length != b.length) return false;


    a.sort(function(a, b) {return a-b});
    b.sort(function(a, b) {return a-b});
    for (var i = 0; i < a.length; ++i) {
        if (a[i] !== b[i]) return false;
    }
    return true;
}

function placeError(input, errorMsg) {
    removeErrors(input);

    Swal.fire({
        title: 'Error',
        text: errorMsg
    })
    $(input).addClass('is-invalid');
    $(input).after(`
        <span class="invalid-feedback" role="alert">
            <strong>${errorMsg}</strong>
        </span>
    `);
}

function removeErrors(...inputs) {
    for (let input of inputs) {
        $(input).removeClass('is-invalid');
        $(input).siblings('span.invalid-feedback').remove();
    }
}
