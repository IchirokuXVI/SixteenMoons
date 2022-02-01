let avatar = $('#avatar').attr('src');
let cropper, file;
$(()=> {
    $('#cropperModal').modal({
        show: false,
        backdrop: 'static'
    });

    $('#editUser').click(function() {
        $(this).hide();
        $('#username').hide();
        $('#suffixId').hide();
        $('#email').hide();
        $('#usernameInput').show();
        $('#emailInput').show();
        $('#saveUser').show();
        $('#cancelUser').show();
        $('#currentPasswordContainer').show();
        $('#changePasswordButtonContainer').show();

        //Change disabled property so when clicking the avatar image the file manager will open
        $('#avatar_upload').prop('disabled', false);
        $('#avatar_background').show();
    });

    $('#changePasswordButtonContainer').click(function() {
        $(this).hide();
        $('#changePasswordInputs').show();
    });

    $('#cancelUser').click(function() {
        $(this).hide();
        $('#saveUser').hide();
        $('#currentPasswordContainer').hide();
        $('#changePasswordButtonContainer').hide();
        $('#changePasswordInputs').hide();
        $('#usernameInput').hide();
        $('#emailInput').hide();

        //Reset input values
        $('#usernameInput').val($('#username').text());
        $('#emailInput').val($('#email').text());

        $('#editUser').show();
        $('#username').show();
        $('#suffixId').show();
        $('#email').show();

        //Change disabled property so when clicking the avatar image the file manager will open
        $('#avatar').attr('src', avatar);
        $('#avatar_upload').prop('disabled', true);
        $('#avatar_background').hide();
    });

    $('#profileForm').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        // Create the json that will be used with the ajax, formData will be modified after creating the json but it doesn't matter because it is an object so the json will be modified
        let ajaxOptions = {
            // Destroy url, it isn't generated through name so it must be manually changed (which is a pain in the ass, should change it)
            url: `${assetUrl}/users/${userId}`,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            data: formData,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            success: function(data) {
                removeErrors($('#avatar'), $('#usernameInput'), $('#emailInput'), $('#current_password'), $('#password'));

                $('#email').text(data.user.email);
                $('#username').show();
                $('#username').text(data.user.username);
                $('#email').show();
                $('#editUser').show();
                $('#suffixId').show();

                $('#cancelUser').hide();
                $('#saveUser').hide();
                $('#currentPasswordContainer').hide();
                $('#changePasswordButtonContainer').hide();
                $('#changePasswordInputs').hide();
                $('#password').val('');
                $('#current_password').val('');
                $('#password_confirmation').val('');

                $('#usernameInput').hide();
                $('#emailInput').hide();

                $('#avatar_upload').prop('disabled', true);
                $('#avatar_background').hide();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                let response = XMLHttpRequest.responseJSON;
                if (response) {
                    if (response.errors) {
                        if (response.errors.avatar) {
                            placeError($('#avatar'), response.errors.avatar);
                        } else if (response.errors.username) {
                            placeError($('#usernameInput'), response.errors.username);
                        } else if(response.errors.email) {
                            placeError($('#emailInput'), response.errors.email);
                        } else if (response.errors.current_password) {
                            placeError($('#current_password'), response.errors.current_password);
                        } else if (response.errors.password) {
                            placeError($('#password'), response.errors.password);
                        }
                    } else {
                        Swal.fire('Error', response.error);
                    }
                }
            },
        };
        formData.append('_method', 'PUT');
        if (file) {
            file.toBlob((blob) => {
                formData.append('avatar', blob);
                $.ajax(ajaxOptions);
            });
        } else {
            $.ajax(ajaxOptions);
        }
    });

    $('#avatar_upload').change(function() {
        file = $('#avatar_upload').prop('files')[0];
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
            width: 480,
            height: 480
        });
        $('#avatar').attr('src', cropper.getCroppedCanvas().toDataURL());
        $('#cropperModal').modal('hide');
    });

    $('#cropperModal').on('shown.bs.modal', function () {
        let cropperContainer = $('.cropper-img').parent()[0];

        new Cropper($('.cropper-img')[0], {
            aspectRatio: 1,
            viewMode: 3,
            dragMode: 'move',
            toggleDragModeOnDblclick: false,

            crop: function(e) {
                cropper = this.cropper;
                cropper.getCroppedCanvas({
                    width: 480,
                    height: 480
                });
                // Make a container to preview the selected area
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
});

function placeError(input, errorMsg) {
    removeErrors(input);

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
