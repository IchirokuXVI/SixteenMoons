//let files = []; Declared on the view
$(() => {
    $('#fileUpload').change(function() {
        for (let file of $(this).prop('files')) {
            if (filesName.indexOf(file.name) == -1) {
                counter++;
                let formData = new FormData();
                formData.append('files[]', file);

                $('#files').append(`
                    <div class="row" id="file${counter}">
                        <div class="col-md-5 col-6 fileName">${file.name}</div>
                        <div class="col-md-3 d-md-block d-none">${new Date().toLocaleDateString()}</div>
                        <div class="col-md-3 col-4">${file.size/1024 > 1024 ? (file.size/1024/1024).toFixed(2) + 'MB' : (file.size/1024).toFixed(2) + 'KB'}</div>
                        <div class="col-lg-1 col-2">
                            <i class="fas fa-circle-notch fa-spin loading"></i>
                            <i class="fas fa-times text-danger removeFile" style="display: none;"></i>
                        </div>
                    </div>
                `
                );
                //$(`#file${counter} .loading`).show();

                //Closure to bind the counter value to the ajax function, this way the value won't change even if the success function is called when the values has already changed
                (function(counter) {
                    $.ajax({
                        url: uploadUrl,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        data: formData,
                        dataType: 'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        method: 'POST',
                        success: function(data) {
                            //Remove loading icon and show the remove icon
                            $(`#file${counter} .loading`).remove();
                            $(`#file${counter} .removeFile`).attr('data-fileId', data);
                            $(`#file${counter} .removeFile`).show();
                            console.log(data);
                            filesName.push(file.name);
                            filesId.push(...data);
                            console.log(counter);
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            let response = XMLHttpRequest.responseJSON;
                            if (response) {
                                Swal.fire('Error', response.error)
                            } else if (errorThrown === "Payload Too Large") {
                                Swal.fire('Error', Cookies.get('language') == 'en' ? "File size too large" : "Archivo demasiado grande");
                            };
                            $(`#file${counter}`).remove();
                        },
                    });
                })(counter);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: Cookies.get('language') == 'en' ? `You can't upload multiple files with same file name` : 'No puedes subir varios ficheros con el mismo nombre'
                });
            }
        }

        $(this).val("");
    });

    $('#files').on('click', '.removeFile', function() {
        Swal.fire({
            title: Cookies.get('language') == 'en' ? 'Are you sure ?' : '¿Estás seguro?',
            text: Cookies.get('language') == 'en' ? "You won't be able to revert this!" : 'Estos cambios son irreversibles',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: Cookies.get('language') == 'en' ? 'Yes' : 'Sí',
            cancelButtonText: Cookies.get('language') == 'en' ? 'Cancel' : 'Cancelar'
        }).then((result) => {
            if (result.value) {
                let container = $(this).parent().parent();
                let fileName = $(this).parent().siblings('.fileName').text();
                (function(id) {
                    $.ajax({
                        // Destroy url, it isn't generated through name so it must be manually changed (which is a pain in the ass, should change it)
                        url: `${assetUrl}courses/${courseId}/files/${id}`,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        dataType: 'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        method: 'DELETE',
                        success: function(data) {
                            console.log(data);
                            // Remove file from the filesName array, where all the file names are saved
                            filesName.splice(filesName.indexOf(fileName), 1);
                            //Remove the file id from the filesId array so it is not sent to the controller
                            filesId.splice(filesId.indexOf(parseInt(id)),1);
                            $(container).remove();
                        }
                    });
                })($(this).attr('data-fileId'));
            }
        })
    });

    $('#form').submit(function() {
        for (let fileId of filesId) {
            $(this).append(`<input type="text" style="display: none;" name="filesId[]" value="${fileId}">`);
        }
        return true;
    });
});
