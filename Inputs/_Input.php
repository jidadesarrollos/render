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
use Render\CloneSelector as CloneSelector;
use Render\Selector as Selector;

class _Input extends InputBase {

    /**
     * @var mixed $_valorUpdate Registra el valor en modo update para el selector
     */
    private $_valorUpdate;

    /**
     * @var string $labelOpcion Label para cada selector multiple radio o inputs
     */
    var $labelOpcion = "";
    var $inline = TRUE;
    /**
     * Selectores que requieren de multiples instancias
     *
     * @var array $_controlesMultiples ;
     */
    private $_controlesMultiples = ['checkbox', 'radio'];

    /**
     * @var string Html para controles Button
     */
    private $_html = "";
    /**
     *
     * /**
     * Contiene los objetos SelectorInput de cada opcion de un control
     * de seleccion múltiple
     *
     * @param array $_selectoresOpcion
     */
    private $_selectoresOpcion = [];

    /**
     * Bandera interna que determina si el constructor debe o no llamar al metodo crearSelector
     *
     * @var boolean $_crear
     */
    private $_crear = TRUE;


    /**
     * Crea Selectores para un formulario
     *
     * @internal Permite crear y definir selectores HTML para formularios
     *
     * @param string $tipo  Tipo del Selector de Formulario
     * @param array  $attr  Arreglo de atributos
     * @param mixed  $items Valores para los selectores de multiseleccion
     * @method __construct
     *
     * @example  new SelectorInput(Std Class);
     */
    function __construct($params, $attr = FALSE) {

        $this->_inicializar($params, $attr);


    }

    private function _procesarInputSeleccion($params) {


        if ($params->type == 'select') {
            $this->_selector = new Select($params);
        } else {
            $this->_selector = new InputSeleccion($params->type, $params);
        }

    }

    private function _inicializar($params, $type = FALSE) {

        if (property_exists($params, 'data')) {

            if (is_object($params->data)) {
                $params->data = get_object_vars($params->data);
            } else if (is_string($params->data)) {
                $params->data = [];
            }

        }

        $this->establecerAtributos($params, $this);
        $this->_name = $params->name;
        $this->_tipo = $params->type;

        if (property_exists($params, 'html')) {
            $this->_html = $params->html;
        }
        if ($params->type == 'select' or in_array($params->type, ['checkbox', 'radio'])) {
            $this->_procesarInputSeleccion($params);
        } else {
            $this->_crearSelector();
        }

        if (property_exists($params, 'class')) {
            $this->addClass($params->class);
        }

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


    /**
     * Permite acceder a la opcion de un selector
     *
     */
    function opcion($opcion, $valor = "") {

        $key = strtolower($opcion);
        if (array_key_exists(strtolower($key), $this->_selectoresOpcion)) {
            if ($valor) {
                $this->_selectoresOpcion[$key]->attr('value', $valor);
            }

            return $this->_selectoresOpcion[$key];
        }

        return false;
    }

    private function _crearTextArea() {

        $this->_attr = array_merge($this->_attr, ['type' => $this->_tipo, 'name' => $this->_name]);
        parent::__construct($this->_tipo, $this->_attr);

    }

    /**
     * Permite editar las opciones de un selector multiple
     *
     * @internal
     *
     * @method editarOpciones
     * @deprecated 0.6
     */
    function editarOpciones($opciones, $add = FALSE, $valor = "") {

        $this->opciones = $opciones;
        if (!in_array($this->type, $this->_controlesMultiples) and $this->_tipo != 'select') {
            throw new Exception("El selector " . $this->id . " no es un control de seleccion", $this->_ce . '08');
        }

        if (!is_array($opciones)) {
            $this->opciones = $opciones;
            $opciones = $this->obtOpciones();
        }
        if (!$add) {
            $this->_selectoresOpcion = [];
        }
        if ($this->type == 'select') {
            $this->_crearOpcionesSelect($opciones);
        } else {
            $this->_crearOpcionesSelectorMultiple($opciones);
        }


    }

    /**
     * Crea un selector Select
     * @method _crearSelect
     */

    /*    private function _crearSelect() {


            $data = ['type' => $this->_tipo,
                     'name' => $this->_name,
                     'id'   => $this->id];

            $this->_attr = array_merge($this->_attr, $data);
            parent::__construct($this->_tipo, $this->_attr);
            $options = $this->obtOpciones();
            $this->_crearOpcionesSelect($options);
        }
        */

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

    /**
     * Imprime los selectores multiples incluidos en $_controlesMultiples
     * @method renderMultiples
     */
    private function renderMultiples() {

        $tpl = "";
        foreach ($this->_selectoresOpcion as $id => $selector) {
            $input = $selector->render(TRUE);

            $data = [
                'input' => $input,
                'label' => $selector->labelOpcion,
                'type'  => $selector->type,

            ];
            if ($this->inline) {
                $tpl .= $this->_obtTemplate($this->estructura('MultiplesInline'), $data);
            } else {
                $data['class'] = Estructura::$cssMultiples;
                $tpl .= $this->_obtTemplate($this->estructura('controlMultiple'), $data);
            }


        }

        return $tpl;
    }

    /**
     * @internal Renderiza un control de tipo select
     * @return mixed|string
     */
    private function renderSelect() {

        $options = "";
        foreach ($this->_selectoresOpcion as $key => $option) {
            $options .= $option->render();
        }
        $this->innerHTML($options);

        return $this->render(TRUE);

    }

    /**
     *
     * @param bool $parent
     *
     * @return mixed|string
     */
    function render($parent = FALSE) {


        if (!$parent and in_array($this->_tipo, $this->_controlesMultiples)) {
            return $this->renderMultiples();
        } elseif (!$parent and $this->_tipo == 'select') {
            return $this->renderSelect();
        } else {

            return parent::render();
        }
    }

    function _crearIdentificacion() {

        $select = new CloneSelector($this);

        $select->type = 'select';
        $select = new SelectorInput($select);

    }

    /**
     * Asigna un valor al selector instanciado
     * @method valor
     *
     * @param string $valor Valor a mostrar en el input
     */
    function valor($valor) {

        $this->_valorUpdate = $valor;

        if (in_array($this->_tipo, $this->_controlesMultiples)) {

            foreach ($this->_selectoresOpcion as $key => $selector) {
                if ($selector->attr('value') == $valor) {
                    $selector->attr('checked', 'checked');
                }
            }

        } else
            if ($this->type == 'select') {
                foreach ($this->_selectoresOpcion as $key => $selector) {
                    if ($selector->attr('value') == $valor) {
                        $selector->attr('selected', 'selected');
                    }
                }

            } elseif ($this->type == "textarea") {
                $this->innerHTML($valor);
            } else {
                $this->attr('value', $valor);
            }

    }

    /**
     * Renderiza el contenido en plantillas predeterminadas
     * @method _obtTemplate
     *
     * @param $plantilla ;
     */
    private function _obtTemplate($template, $params) {

        foreach ($params as $key => $value) {
            $template = str_replace("{{:" . $key . "}}", $value, $template);
        }

        return $template;
    }


}
