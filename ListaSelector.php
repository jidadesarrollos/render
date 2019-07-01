<?php
/**
 *  Genera selectores tipo UL
 */

namespace JidaRender;

class ListaSelector extends Selector {

    protected $selector = "UL";
    protected $selectorItems = "LI";

    protected $items = [];

    function __construct($numeroItems = 0, $attr = []) {

        parent::__construct($this->selector, $attr);
    }

    /**
     * Agrega un item a la lista
     *
     * El item agregado sera un objeto de Tipo Selector con valor de
     * $selectorItem
     * @method addItem
     *
     * @see $selectorItems
     * @see Selector
     */
    function addItem($contenido) {

        $item = new Selector($this->selectorItems);
        $item->innerHTML($contenido);

        $this->items[] = $item;

        return end($this->items);

    }

    function render() {

        foreach ($this->items as $key => $item) {
            $this->addFinal($item->render());
        }

        return parent::render();
    }

}
