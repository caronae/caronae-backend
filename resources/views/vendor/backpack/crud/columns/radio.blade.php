@php
	$keyName = isset($column['key']) ? $column['key'] : $column['name'];
	$entryValue = $entry['attributes'][$keyName];
	$displayValue = isset($column['options'][$entryValue]) ? $column['options'][$entryValue] : '';
@endphp

<td>{{ $displayValue }}</td>
