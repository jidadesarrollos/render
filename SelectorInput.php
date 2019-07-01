<?php
/**
 * Clase para SelectorInput
 *
 * @author Julio Rodriguez
 * @package
 * @version
 * @category
 */

namespace Render;

use Jida\BD\BD as BD;
use Jida\Medios as Medios;
use Exception as Excepcion;

class SelectorInput extends Selector {

    use \Jida\Core\ObjetoManager;
    var $name;
    var $type;
    var $id;
    var $label;
    var $opciones;
    var $value = "";
    var $placeholder = "";
    /**
     * @var mixed $_valorUpdate Registra el valor en modo update para el selector
     */
    private $_valorUpdate;

    /**
     * @var string $labelOpcion Label para cada selector multiple radio o inputs
     */
    var $labelOpcion = "";
    var $inline = TRUE;
    private $_ce = '00101';
    /**
     * Selectores que requieren de multiples instancias
     *
     * @var array $_controlesMultiples ;
     */
    private $_controlesMultiples = ['checkbox',
        'radio'
    ];

    /**
     * @var string Html para controles Button
     */
    private $_html = "";
    /**
     * Define el tipo de Selector de formulario
     *
     * @internal El valor por defecto es text
     * @var string $_tipo
     * @access   private
     */
    private $_tipo = "text";
    /**
     * Opciones del selector
     *
     * @internal Posee las opciones a agregar a un control
     * de selección multiple
     * @var array $_opciones
     */
    private $_opciones;
    /**
     * Atributos pasados en el constructor
     *
     * @var mixed $_attr ;
     * @access private
     */
    private $_attr = [];
    /**
     * Contiene los objetos SelectorInput de cada opcion de un control
     * de seleccion múltiple
     *
     * @param array $_selectoresOpcion
     */
    private $_selectoresOpcion = [];

    public $classMultiples = 'col-md-3';
    private $_tplMultiples = '<div class="{{:type}} {{:class}}">
	    {{:input}}
	    <label for="{{:label}}">
	        {{:label}}
	    </label>
	  </div>';

    private $_tplMultiplesInline = '<div class="{{:type}} {{:type}}-inline">
	    {{:input}}
	    <label for="{{:label}}">
	        {{:label}}
	    </label>
	  </div>';
    private $_tplControlMultiple = '
  		<div class="control-multiple">
  		{{:selector1}}{{:selector2}}
  		</div>
  	';

    /**
     * Bandera interna que determina si el constructor debe o no llamar al metodo crearSelector
     *
     * @var boolean $_crear
     */
    private $_crear = TRUE;
    /**
     * Items u opciones para agregar en campos pasados por el usuario
     *
     * @var mixed $_items
     * @access private
     */
    private $_items;
    /**
     * Determina si la clase ha sido extendida o no.
     *
     * @var boolean $_claseExtentida
     */
    private $_claseExtendida = FALSE;
    /**
     * Objeto que extiende las funcionalidades
     *
     * @property object $_extension;
     */
    private $_extension;

    /**
     * Crea Selectores para un formulario
     *
     * @internal Permite crear y definir selectores HTML para formularios
     *
     * @param string $tipo Tipo del Selector de Formulario
     * @param array $attr Arreglo de atributos
     * @param mixed $items Valores para los selectores de multiseleccion
     * @method __construct
     *
     * @example  new SelectorInput($name,$tipo="text",$attr=[],$items="")
     * @example  new SelectorInput(Std Class);
     */
    function __construct() {

        $numero = func_num_args();
        if ($numero == 1 or ($numero == 2 and in_array(func_get_arg(1), $this->_controlesMultiples))) {
            if ($numero > 1)
                $this->__constructorObject(func_get_arg(0), func_get_arg(1));
            else
                $this->__constructorObject(func_get_arg(0));
        }
        else {
            call_user_func_array([$this,
                '__constructorParametros'
            ], func_get_args());
        }
        $this->_checkClaseExtendida();
        if ($this->_crear)
            $this->_crearSelector();

    }

    private function _checkClaseExtendida() {

        if (class_exists('\App\Config\SelectorInput')) {
            $this->_extension = new \App\Config\SelectorInput();

        }
    }

    private function __constructorObject($params, $type = FALSE) {

        if (property_exists($params, 'data')) {

            if (is_object($params->data))
                $params->data = get_object_vars($params->data);
            elseif (is_string($params->data))
                $params->data = [];

        }

        $this->establecerAtributos($params, $this);
        $this->_name = $params->name;
        $this->_tipo = $params->type;
        if (property_exists($params, 'html'))
            $this->_html = $params->html;

        if (!$type and in_array($params->type, ['checkbox',
                'radio'
            ])) {

            $this->opciones = $params->opciones;
            $this->_tipo = $params->type;
            $opciones = $this->obtOpciones();
            $this->_crearOpcionesSelectorMultiple($opciones);
            $this->_crear = FALSE;

        }
        if (property_exists($params, 'class')) {
            $this->addClass($params->class);
        }

    }

    private function __constructorParametros($name, $tipo = "text", $attr = [], $items = "") {

        $this->_name = $name;
        $this->_tipo = $tipo;
        $this->_attr = is_array($attr) ? $attr : [];
    }

    private function _crearSelector() {

        switch ($this->_tipo) {
            case 'select':
                $this->_crearSelect();
                break;
            // case 'identificacion';
            // $this->_crearIdentificacion();
            // break;
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
     * Crea los objetos selector para cada opcion de un selector multiple
     * @method crearOpcionesSelectorMultiple
     */
    private function _crearOpcionesSelectorMultiple($opciones) {

        for ($i = 0; $i < count($opciones); ++$i) {

            $class = new \stdClass();
            $class->value = array_shift($opciones[$i]);
            $class->labelOpcion = array_shift($opciones[$i]);

            $class->name = ($this->type == 'checkbox') ? $this->name . "[]" : $this->name;
            $class->type = $this->type;
            $class->_tipo = $this->type;
            $class->_identif = 'objectSelectorInputInterno';
            $class->id = $this->id . "_" . ($i + 1);

            $selector = new SelectorInput($class, $this->type);
            if ($class->value == $this->_valorUpdate) {
                $selector->attr('checked', 'checked');
            }
            array_push($this->_selectoresOpcion, $selector);
        }
    }

    /**
     * Genera los objeto selector para las opciones de un select
     * @method crearOpcionesSelect
     */
    private function _crearOpcionesSelect($options) {

        foreach ($options as $key => $data) {

            if ($this->name == 'control') {
                //Medios\Debug::imprimir($key, $data);
            }

            if (is_array($data)) {

                $key = array_keys($data);
                if ($this->name == 'control') {
                    //Medios\Debug::imprimir($key, $data);
                }

                $opcion = new Selector('option', ['value' => $data[$key[0]]]);
                if ($data[$key[0]] == $this->_valorUpdate) {
                    $opcion->attr('selected', 'selected');
                }

                $this->_selectoresOpcion[$data[$key[0]]] = $opcion;
                $opcion->innerHTML($data[$key[1]]);

            }
            else {
                $opcion = new Selector('option', ['value' => $key]);
                $opcion->innerHTML($data);
                $this->_selectoresOpcion[$key] = $opcion;
            }

        }
    }

    /**
     * Procesa los item a agregar en controles de seleccion
     *
     */
    private function obtOpciones() {

        $revisiones = explode(";", $this->opciones);

        $opciones = [];
        foreach ($revisiones as $key => $opcion) {
            if (stripos($opcion, 'select ') !== FALSE) {
                $opciones = array_merge($opciones, BD::query($opcion));

            }
            elseif (stripos($opcion, 'externo') !== FALSE) {
                continue;
            }
            else {
                $opciones[] = explode("=", $opcion);
            }
        }

        return $opciones;

    }

    /**
     * Agrega opciones a un selector de seleccion
     *
     * @since   0.6
     * @method addOpciones
     * @example $selectorImput->addOpciones([1=>'Valor 1', 2=>'Valor 2']);
     */
    function addOpciones($opciones, $add = FALSE) {

        if (!is_array($opciones)) {
            throw new Excepcion('Las opciones no se han pasado correctamente', $this->_ce . '000008');
        }
        if ($this->type == 'select') {
            $this->_crearOpcionesSelect($opciones);
        }
        else {
            $this->_crearOpcionesSelectorMultiple($opciones);
        }

        return $this;

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

        return FALSE;
    }

    private function _crearTextArea() {

        $this->_attr = array_merge($this->_attr, ['type' => $this->_tipo,
                                                  'name' => $this->_name
        ]);
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
        }
        else {
            $this->_crearOpcionesSelectorMultiple($opciones);
        }

    }

    /**
     * Crea un selector Select
     * @method _crearSelect
     */
    private function _crearSelect() {

        //$this->_attr= array_merge($this->_attr,['name'=>$this->_name]);

        $this->_attr = array_merge($this->_attr, ['type' => $this->_tipo,
                                                  'name' => $this->_name,
                                                  'id'   => $this->id
        ]);
        parent::__construct($this->_tipo, $this->_attr);
        $options = $this->obtOpciones();
        $this->_crearOpcionesSelect($options);
    }

    function _crearBoton() {

        $this->_attr = array_merge($this->_attr, ['type' => $this->_tipo,
                                                  'name' => $this->_name,
                                                  'id'   => $this->id,

        ]);

        parent::__construct('button', $this->_attr);

        $this->innerHTML($this->_html);
    }

    function _crearInput() {

        $this->_attr = array_merge($this->_attr, ['type'        => $this->_tipo,
                                                  'name'        => $this->_name,
                                                  'id'          => $this->id,
                                                  'value'       => $this->value,
                                                  'placeholder' => $this->placeholder
        ]);
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

            $data = ['input' => $input,
                     'label' => $selector->labelOpcion,
                     'type'  => $selector->type,

            ];
            if ($this->inline) {
                $tpl .= $this->_obtTemplate($this->_tplMultiplesInline, $data);
            }
            else {
                $data['class'] = $this->classMultiples;
                $tpl .= $this->_obtTemplate($this->_tplMultiples, $data);
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
        }
        elseif (!$parent and $this->_tipo == 'select') {
            return $this->renderSelect();
        }
        else {

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

        }
        else if ($this->type == 'select') {
            foreach ($this->_selectoresOpcion as $key => $selector) {
                if ($selector->attr('value') == $valor) {
                    $selector->attr('selected', 'selected');
                }
            }

        }
        elseif ($this->type == "textarea") {
            $this->innerHTML($valor);
        }
        else {
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
