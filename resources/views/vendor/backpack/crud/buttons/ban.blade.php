<?php
$banned = Caronae\Models\User::withTrashed()->find($entry->getKey())->banned;
?>
<a href="{{ url($crud->route.'/'.$entry->getKey()) }}/{{ $banned ? 'unban' : 'ban' }}" class="btn btn-xs btn-default" data-button-type="{{ $banned ? 'unban' : 'ban' }}">
	<i class="fa fa-ban"></i> {{ $banned ? 'Desbanir' : 'Banir' }}
</a>