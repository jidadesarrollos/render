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

use Jida\Medios as Medios;

class Input extends InputBase {

    function __construct(\stdClass $params, $attr = false) {

        if (property_exists($params, 'data')) {

            if (is_object($params->data)) {
                $params->data = get_object_vars($params->data);
            }
            else if (is_string($params->data)) {
                $params->data = [];
            }

        }

        $this->establecerAtributos($params, $this);
        $this->_name = $params->name;
        $this->_tipo = $params->type;

        if (property_exists($params, 'html')) {
            $this->_html = $params->html;
        }

        $this->_crearSelector();
        if (property_exists($params, 'class')) {
            $this->addClass($params->class);
        }
        #Medios\Debug::imprimir($params);

    }

    private function _crearSelector() {

        switch ($this->_tipo) {

            case 'textarea':
                $this->_crearTextArea();
                break;
            case 'button':
                $this->_crearBoton();
                break;

            default:
                $this->_crearInput();
                break;
        }

    }

    private function _crearTextArea() {

        $this->_attr = array_merge($this->_attr,
            [
                'type' => $this->_tipo,
                'name' => $this->_name
            ]);
        parent::__construct($this->_tipo, $this->_attr);

    }

    function _crearBoton() {

        $this->_attr = array_merge($this->_attr,
            [
                'type' => $this->_tipo,
                'name' => $this->_name,
                'id'   => $this->id,

            ]
        );

        parent::__construct('button', $this->_attr);

        $this->innerHTML($this->_html);
    }

    function _crearInput() {

        $this->_attr = array_merge($this->_attr,
            [
                'type'        => $this->_tipo,
                'name'        => $this->_name,
                'id'          => $this->id,
                'value'       => $this->value,
                'placeholder' => $this->placeholder
            ]
        );

        parent::__construct('input', $this->_attr);

    }

    function valor($valor) {

        if ($this->type == 'textarea') {
            $this->innerHTML($valor);
        }
        else {
            $this->attr('value', $valor);
        }

    }
}
