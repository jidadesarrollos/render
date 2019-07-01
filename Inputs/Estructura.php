<?php
/*
 * Define la estructura a implementar por los inputs del objeto formulario
 * @version 0.0.1
 * @since 0.6
 *
 */

namespace Render\Inputs;

class Estructura {


    static $controlMultiple =
        '<div class="{{:type}} {{:class}}">
	    {{:input}}
	    <label for="{{:label}}">
	        {{:label}}
	    </label>
	  </div>';

    static $controlMultipleInline =
        '<div class="{{:type}} {{:type}}-inline">
	    {{:input}}
	    <label for="{{:label}}">
	        {{:label}}
	    </label>
	  </div>';

    static $cssMultiples = 'col-md-3';

}