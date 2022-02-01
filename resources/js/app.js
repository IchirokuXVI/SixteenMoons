/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('tinymce');
require('tinymce/themes/silver');

window.Cookies = require('js-cookie');
//window.Vue = require('vue');
window.Swal = require('sweetalert2');
window.Cropper = require('cropperjs');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

//Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// const app = new Vue({
//     el: '#app',
// });

// Ajax for the notifications, written here because they will be in every page
// Onload
$(() => {
    $('#notifications').click(function(e) {
        e.stopPropagation();
    });

    if ($('#unseenNotifications').length) {
        $('#navbarDropdownNotifications').click(function() {
            if ($('#unseenNotifications').length) {
                $.ajax({
                    url: 'http://www.iestrassierra.net/alumnado/curso1920/DAW/daw1920a4/SixteenMoons/users/resetNotifications',
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: {
                        _method: 'PUT'
                    },
                    dataType: 'json',
                    success: function() {
                        $('#unseenNotifications').remove();
                    }
                });
            }
        });
    }

    $('.deleteNotification').click(function() {
        let container = $(this).parent().parent();
        $.ajax({
            url: `http://www.iestrassierra.net/alumnado/curso1920/DAW/daw1920a4/SixteenMoons/users/notifications/${$(container).attr('data-id')}`,
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            dataType: 'json',
            success: function() {
                $(container).remove();
                // 1 and not 0 because there is a container fluid inside
                if ($('#notifications').find('.notification').length == 0) {
                    $('#notifications').append(`<div class="text-secondary text-center">${Cookies.get('language') == 'es' ? 'No tienes más notificaciones' : "You don't have any more notifications"}</div>`)
                }
            }
        });
    });
});
