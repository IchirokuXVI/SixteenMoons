$(() => {
    let custom_filters = Cookies.get('custom_filters') ? JSON.parse(Cookies.get('custom_filters')) : [];
    $('#custom_filter_add').click(function() {
        if($('#custom_filter_input').val() && custom_filters.indexOf($('#custom_filter_input').val()) == -1) {
            let topic = $('#custom_filter_input').val();
            $('#custom_filters').append(
                `<div class="custom-control custom-checkbox mt-1">\n` +
                    `<input type="checkbox" class="custom-control-input" id="topic_${topic}" name="topic[]" value="${topic}" checked>\n` +
                    `<label class="custom-control-label unselectable" for="topic_${topic}">${topic}</label>\n` +
                    `<i class="fas fa-trash custom_filter_remove" data-topic="${topic}"></i>` +
                `</div>`
            );
            custom_filters.push(topic);
            Cookies.set('custom_filters', JSON.stringify(custom_filters), { expires: 365 });

            $('#custom_filter_input').val('');
        }
    });

    $('#custom_filters').on('click', '.custom_filter_remove', function() {
        custom_filters.splice(custom_filters.indexOf($(this).attr('data-topic')), 1);
        Cookies.set('custom_filters', JSON.stringify(custom_filters), { expires: 365 });

        $(this).parent().remove();
    });
});
