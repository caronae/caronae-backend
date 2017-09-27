<?php
$neighborhoodsCount = $entry->neighborhoods()->count();
?>

<td>
    {{ $neighborhoodsCount }} {{ $neighborhoodsCount > 1 ? 'bairros' : 'bairro' }}

    @include('vendor.backpack.crud.buttons.editNeighborhoods')
</td>