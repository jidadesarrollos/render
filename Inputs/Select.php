<?php
/**
 * Clase para SelectorInput
 *
 * @author Julio Rodriguez
 * @package
 * @version
 * @category
 */

namespace Render\Inputs;

use Jida\BD\BD as BD;
use Jida\Medios as Medios;
use Exception as Excepcion;
use Render\Selector as Selector;

class Select extends InputBase implements SeleccionInterface {

    use Seleccion;

    function __construct($data = "", array $attr = []) {

        $atributos = [
            'name' => $data->name,
            'id'   => $data->id,
        ];
        $attr = array_merge($atributos, $attr);
        $this->establecerAtributos($data, $this);

        if (array_key_exists('padre', $attr)) {
            $this->_padre = $attr['padre'];
            unset($attr['padre']);
        }
        parent::__construct($data->type, $attr);

        if (property_exists($data, 'opciones')) {
            $this->_obtOpciones($data->opciones);
            $this->_procesarArregloOpciones();
        }

    }


    function render() {

        $options = "";
        foreach ($this->_selectoresOpcion as $key => $option) {
            $options .= $option->render();
        }
        $this->innerHTML($options);

        return parent::render(TRUE);
    }

    /**
     * Retorna el objeto Selector de una opción solicitada
     * @method opcion
     *
     * @param string opcion Valor o label de la opción requerida
     *
     * @return Object Selector
     * @see \Render\Selector
     */
    function opcion($opcion) {

        $salida = FALSE;

        if (array_key_exists($opcion, $this->_opciones)) {
            $salida = $this->_selectoresOpcion[$opcion];
        } else if (in_array($opcion, $this->_opciones)) {


        } else {
            throw new \Exception('La opción solicitada no existe', $this->_ce . '000001');
        }

        #Medios\Debug::imprimir($opcion, $salida, true);
        return $salida;

    }

    private function _procesarArregloOpciones() {

        $opciones = $this->_opciones;

        foreach ($opciones as $key => $data) {

            $opcion = new Selector('option', ['value' => $key]);
            $opcion->innerHTML($data);
            if (!empty($key) and ($key == $this->_valor or $key == $this->value)) {
                $opcion->attr('selected', 'selected');
            }
            $this->_selectoresOpcion[$key] = $opcion;

        }
    }

    function valor($valor) {

        $this->value = $valor;
        $busqueda = array_key_exists($valor, $this->_opciones);

        if ($busqueda) {
            $this->_selectoresOpcion[$valor]->attr('selected', 'selected');
        }

    }


}