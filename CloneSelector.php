<?php

namespace JidaRender;

class CloneSelector extends Selector {
    use \Jida\Core\ObjetoManager;

    function __construct($objeto) {
        $this->copiarAtributos($objeto, $this);

    }
}
