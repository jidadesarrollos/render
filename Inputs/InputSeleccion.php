<?php
/**
 * Clase para SelectorInput
 *
 * @author Julio Rodriguez
 * @package
 * @version
 * @category
 */

namespace JidaRender\Inputs;

use Jida\BD\BD as BD;
use Jida\Medios as Medios;
use Exception as Excepcion;
use JidaRender\Selector;


class InputSeleccion extends InputBase implements SeleccionInterface {

    use Seleccion;
    var $type;
    var $inline;


    function __construct($data = "", array $attr = []) {

        $this->establecerAtributos($data, $this);
        if (array_key_exists('padre', $attr)) {
            $this->_padre = $attr['padre'];

        }
        if (!$data->opciones) {
            throw new \Exception("Debe agregar las opciones al campo de seleccion creado.", $this->_ce . '0000009');
        }
        $this->_obtOpciones($data->opciones);
        $this->_procesarArregloOpciones();

    }

    private function _procesarArregloOpciones() {

        $opciones = $this->_opciones;
        foreach ($opciones as $key => $opcion) {
            $name = ($this->type == 'checkbox') ? $this->name . "[]" : $this->name;
            $configuracion = [
                'name'  => $name,
                'class' => $this->class,
                'type'  => $this->type,
                'value' => $key
            ];
            $selector = new Selector('input', $configuracion);
            $selector->label = $opcion;
            $this->_selectoresOpcion[$key] = $selector;
        }

    }


    function render() {

        $salida = "";

        foreach ($this->_selectoresOpcion as $key => $selector) {

            $data = [
                'input' => $selector->render(TRUE),
                'label' => $selector->label,
                'type'  => $this->type,

            ];
            $estructura = ($this->inline) ? 'controlMultipleInline' : 'controlMultiple';
            $salida .= $this->_plantilla($this->estructura($estructura), $data);

        }

        return $salida;

    }

    /**
     *  Marca la opción con el valor pasado como seleccionada
     *
     * @param string $valor
     *
     * @return $this
     */
    function valor($valor) {

        if (array_key_exists($valor, $this->_selectoresOpcion)) {
            $this->_selectoresOpcion[$valor]->attr('checked', 'checked');
        }

        return $this;
    }

}