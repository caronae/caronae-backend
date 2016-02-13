<?php
    $formClass = (!empty($formClass) ? $formClass : '');
    $formMethod = (!empty($formMethod) ? $formMethod : '');
    $formAction = (!empty($formAction) ? $formAction : '');
?>
<form class="form-inline pull-right {{ $formClass }}" method="{{ $formMethod }}" action="{{ $formAction }}">
    <span>De:</span>
    <input
            type="text"
            class="form-control period-start"
            name="start"
            value="{{ $defaultStart }}"
            placeholder="esse dia"
            data-provide="datepicker"
    >
    <span>Até:</span>
    <input
            type="text"
            class="form-control period-end"
            name="end"
            value="{{ $defaultEnd }}"
            placeholder="esse dia"
            data-provide="datepicker"
    >

    <button class="btn btn-primary">
        <span class="glyphicon glyphicon-search"></span>
        Mostrar
    </button>

</form>
