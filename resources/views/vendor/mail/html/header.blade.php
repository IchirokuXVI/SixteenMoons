<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@else
<img src="http://www.iestrassierra.net/alumnado/curso1920/DAW/daw1920a4/SixteenMoons/favicon.ico" class="logo" alt="SixteenMoons Logo">
<br>
{{ $slot }}
@endif
</a>
</td>
</tr>
