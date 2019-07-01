<?PHP
/**
 * Clase para Selector
 *
 * @author Julio Rodriguez <jrodriguez@jidadesarrollos.com>
 * @package
 * @category
 * @version
 */

namespace Render;

use Exception as Excepcion;
use Jida\Medios\Debug;
use MongoDB\BSON\ObjectId;

class Selector {

    /**
     * Define el selector a crear
     *
     * @var $selector
     * @access public
     */
    protected $selector = "";
    protected $id = "";
    protected $atributos = [];
    protected $envoltorio = "";

    /**
     * Atributos data para el selector
     *
     * @var object $data
     */
    var $data;
    var $class = "";
    var $style = "";

    /**
     * Arreglo para agregar atributos adicionales al selector
     *
     * @var array $attr
     */
    var $attr = [];
    /**
     * Contenido del selector, que puede incluir el innerHTML
     * y otros selectores
     *
     * @var mixed $contenido
     */
    var $contenido = "";
    /**
     * Define si es el selector contenedor del InnerHTML
     * o no
     *
     * @var boolean $padreInner
     * @unuse
     */
    var $padreInner = false;
    /**
     * Nodo de selectores hijos del selector
     * var array $nodo;
     */
    private $nodos = [];
    /**
     * Contiene el HTML que se genera al crear el selector
     *
     * @var string $selectorCreado ;
     *
     */
    private $selectorCreado;
    /**
     * Contenido especifico a agregar
     */
    protected $innerHTML;
    /**
     * Permite agregar propiedades adicionales al selector
     */
    private $propiedades = [];
    private $_ce = '100120';
    protected $noCierre = [
        'hr',
        'br',
        'img',
        'input'
    ];

    function __construct($selector = "", $attr = []) {

        if (!empty($selector))
            $this->selector = $selector;

        if (is_array($attr) and count($attr) > 0) {
            $this->attr($attr);
        }

    }

    /**
     * Genera el HTML del selector instanciado
     * @method getSelector
     *
     * @access public
     *
     * @param int $tabs Numero de tabulaciones ha imprimir
     */
    function getSelector($tabs = 0) {

        $s =& $this->selectorCreado;
        $tabulaciones = self::addTabs($tabs);
        $s = "\n" . $tabulaciones;
        $s .= "<" . $this->selector;

        if (!empty($this->id)) {
            $s .= " id=\"" . $this->id . "\"";
        }
        if (!empty($this->class)) {
            $s .= " class=\"" . $this->class . "\"";
        }
        if (!empty($this->style)) {

            $s .= " style=\"" . $this->style . "\"";
        }
        $this->getElementosData();
        $this->getAttr();
        $s .= ">\n" . $tabulaciones . "\t" . $this->contenido . "\n" . $tabulaciones . "</$this->selector>";

        return $this->selectorCreado;

    }//fin funcion

    /**
     * Verifica si existen elementos datas que deban ser agregados al selector
     * y los agrega
     * @method getElementosData
     *
     * @access private;
     */
    private function getElementosData() {

        if (is_array($this->data) and count($this->data) > 0) {

            if ($this->selector == 'TABLE') {
                #Debug::mostrarArray($this->data);
            }
            foreach ($this->data as $key => $value) {

                $this->selectorCreado .= " $key='" . $value . "'";
            }
        }
    }//fin funcion

    /**
     * Verifica si existen elementos datas que deban ser agregados al selector
     * y los agrega
     * @method getElementosData
     *
     * @access private;
     * @deprecta
     */
    private function getAttr() {

        if (is_array($this->attr) and count($this->attr) > 0) {
            foreach ($this->attr as $key => $value) {
                $this->selectorCreado .= " $key=\"$value\"";
            }
        }
    }//fin funcion

    /**
     * Genera un selector HTML
     * @method crear
     *
     * @param string $selector Nombre Etiqueta HTML a crear
     * @param array $atributos Arreglo de atributos para el selector
     * @param string $content Contenido del selector
     */
    public static function crear($selector, $atributos = [], $content = "", $tabs = 0) {

        $selector = explode("#", $selector);
        if (is_array($selector) and count($selector) > 1) {
            $atributos['id'] = $selector[1];
            $selector = $selector[0];
        }
        else {
            $selector = $selector[0];
        }
        $clases = explode(".", $selector);
        if (is_array($clases) and count($clases) > 1) {
            $selector = $clases[0];
            unset($clases[0]);
            $atributos['class'] = implode(" ", $clases);
        }

        $tabulaciones = self::addTabs($tabs);
        $selectorHTML = "" . $tabulaciones;
        $selectorHTML .= "<$selector";

        if (is_array($atributos)) {

            foreach ($atributos as $key => $value) {
                if (is_array($value)) {
                    throw new Excepcion("se ha pasado un arreglo para el key " . $key, 1);

                }
                $selectorHTML .= " $key=\"$value\"";
            }
        }
        if (!in_array($selector,
            [
                'img',
                'hr',
                'br',
                'link',
                'meta'
            ])) {

            if (!empty($content)) {
                $selectorHTML .= ">\n" . $tabulaciones . "$content";
                $selectorHTML .= "\n" . $tabulaciones . "</$selector>";
            }
            else {
                $selectorHTML .= ">$content</$selector>";
            }

        }
        else {
            $selectorHTML .= " />";
        }

        return $selectorHTML . "\n";
    }

    /**
     * Crea una lista OL con estilo bootstrap de breadcrumb
     *
     * @param array $data
     *
     * @return string $html Código HTML generado
     */
    public static function crearBreadCrumb($data, $config = []) {

        $default = [
            "keyLink" => "link",
            "keyHTML" => "html",
            "attrLI"  => [],
            "attrUL"  => ["class" => "breadcrumb"]
        ];
        $config = array_merge($default, $config);

        $lista = "";
        foreach ($data as $key => $value) {
            $data = array_merge(["href" => $value[$config['keyLink']]], $config['attrLI']);
            $link = self::crear('a', $data, $value[$config['keyHTML']]);
            $lista .= self::crear('li', null, $link);
        }
        $html = self::crear('ol', $config['attrUL'], $lista);

        return $html;
    }

    /**
     * Genera el codigo HTML de una Lista ul
     *
     * @param $css  Estilo css desado para selector ul
     * @param array Arreglo de contenido de la lista, debe contener al menos una clave "content"
     *
     * @example array('content'=>array(uno,dos,tres,cuatro))
     * @example array('selectorInterno'=>'img','content'=>array(...))
     */

    public static function crearLista($css, $content) {

        $lista = "";
        if (is_array($content)) {
            if (array_key_exists('content', $content)) {
                foreach ($content['content'] as $key => $content) {
                    if (array_key_exists('selector', $content)) {
                        $selector = $content['selector'];
                        $lista .= self::crear($selector['label'], $se);
                    }
                }
            }
            else {
                throw new Excepcion("No se ha definido el arreglo de contenido para la lista", 1);

            }

        }
    }

    static function crearUL($content, $attrUL = [], $attrLi = []) {

        $li = "";

        foreach ($content as $key => $item) {

            $li .= self::crear("li", $attrLi, $item);
        }

        return self::crear("UL", $attrUL, $li, 2);

    }

    /**
     * Crea Un boton Input
     *
     * El valor por defecto es un submit, permite modificar los atributos
     * del control a crear por medio de un arreglo asociativo con los
     * datos que se desean.
     *
     * @param string $value
     *          Va a ser el valor mostrado en el "value" del boton
     * @param array $valores
     *          arreglo de atributos personalizados.
     *
     */
    public static function crearInput($value, $valores = "") {

        $valoresXDefecto = [
            'type'  => 'submit',
            'name'  => "btn" . ucwords(str_replace(" ", "", $value)),
            'id'    => "btn" . ucwords(str_replace(" ", "", $value)),
            'value' => $value
        ];
        $arrAtributos = (is_array($valores)) ? array_merge($valoresXDefecto, $valores) : $valoresXDefecto;

        $control = "<input";
        foreach ($arrAtributos as $atributo => $valorAtributo) {
            $control .= " $atributo=\"$valorAtributo\"";
        }
        $control .= ">";

        return $control;
    }

    protected function establecerAtributos($arr, $clase = "") {

        if (empty($clase)) {
            $clase = __CLASS__;
        }

        $metodos = get_class_vars($clase);
        foreach ($metodos as $k => $valor) {

            if (isset($arr[$k])) {
                $this->$k = $arr[$k];
            }
        }

    }

    static function addTabs($nums) {

        $tabs = "";
        for ($i = 0; $i < $nums; ++$i):
            $tabs .= "\t";
        endfor;

        return $tabs;
    }

    /**
     * Genera una instancia selector y la retorna
     */
    static function obt($selector) {

        $tag = new Selector($selector);

        return $tag;
    }

    function render() {

        $html = "";

        if (!$this->selectorCierre()) {
            if (!empty($this->selector)) {
                $html = "<" . $this->selector . " " . $this->renderAttr() . " />\n";
            }
        }
        else {
            if (!empty($this->selector)) {
                if ($this->selector == 'textarea') {
                    $html = "\n<" . $this->selector . "" . $this->renderAttr() . ">";
                    $html .= trim($this->renderContenido()) . "</" . $this->selector . ">";
                }
                else {
                    $html = "\n<" . $this->selector . "" . $this->renderAttr() . ">\n\t";
                    $html .= $this->renderContenido() . "\n</" . $this->selector . ">";
                }

            }

        }

        return $html;
    }

    protected function renderContenido() {

        if ($this->contenido instanceOf Selector) {
            $this->contenido->innerHTML($this->innerHTML);

            return $this->contenido->render();
        }
        else {

            return $this->innerHTML;
        }

    }

    private function selectorCierre() {

        if (in_array($this->selector, $this->noCierre)) {
            return false;
        }
        else {
            return true;
        }
    }

    protected function renderAttr() {

        $atribs = "";
        $i = 0;

        if ((is_array($this->attr) or (is_object($this->attr) and $this->attr instanceof \Countable))
            and count($this->attr) > 0) {

            foreach ($this->attr as $attr => $value) {
                $atribs .= " ";
                if (strpos($attr, "data-") !== false) {
                    $atribs .= $attr . "='" . $value . "'";
                }
                else {

                    if (is_array($attr) or is_array($value) or is_object($attr) or is_object($value)) {

                        throw new Excepcion("Debe ser un string el valor pasado", $this->_ce . "001");
                    }
                    $atribs .= $attr . "=\"" . $value . "\"";
                }

                ++$i;
            }
        }

        if (is_object($this->data)) $this->data = (array)$this->data;

        if ((is_array($this->data) and count($this->data) > 0)) {

            foreach ($this->data as $data => $value) {

                if ($i > 0) $atribs .= " ";
                if (is_array($value)) $value = json_encode($value);

                $atribs .= "data-{$data}='{$value}'";

                ++$i;
            }

        }

        return $atribs;

    }

    protected function obtClases() {

        $this->class = $this->attr['class'];

        return $this->attr['class'];
    }

    function addClass($clase) {

        if (!empty($this->attr['class']))
            $this->attr['class'] .= " " . $clase;
        else {
            $this->attr['class'] = $clase;
        }
    }

    function removerClass() {

        $clases = explode(",", $this->attr['class']);
        if (in_array($clase, $clases)) {
            unset($clases[$clase]);
        }
    }

    function data($data = "", $valor = "") {

        if (empty($data)) {
            return $this->data;
        }
        if (!is_object($this->data)) {
            $this->data = new \stdClass();
        }

        if (!empty($valor)) {
            $this->data->{$data} = $valor;

            return $this;
        }

        if (is_array($data)) {
            foreach ($data as $id => $valor) {
                $this->data->{$id} = $valor;
            }

            return $this;
        }

        if (property_exists($this->data, $data)) {
            return $this->data->{$data};
        }

    }

    /**
     * Manejo de atributos del Selector
     *
     * Permite obtener o asignar valor a un selector.
     * @method attr
     *
     * @param mixed $attr Si es string, puede ser el nombre del atributo que se desea obtener o asignar valor. Si es
     *                     un arreglo será tomado para asignar un conjunto de atributos
     * @param       $valor [opcional] Valor a asignar al string $attr
     *
     *
     */
    function attr($attr, $valor = "") {

        if (!empty($valor)) {

            $this->attr[$attr] = $valor;

            return $this;
        }
        else {
            if (is_array($attr)) {
                $this->attr = array_merge($this->attr, $attr);

                return $this;
            }
            else
                if (array_key_exists($attr, $this->attr)) {
                    return $this->attr[$attr];
                }

            return false;
        }
    }

    /**
     * Define el contenido inner del Selector
     * @method innerHTML
     *
     */
    function innerHTML($innerHTML = "") {

        if (empty($innerHTML)) {
            return $this->innerHTML;
        }
        else {
            $this->innerHTML = $innerHTML;

            return $this;
        }

    }

    /**
     * Agrega contenido al principio del innerHTML
     * @method addInicio
     *
     * @param string html a insertar
     */
    function addInicio($html) {

        $this->innerHTML($html . $this->innerHTML);

        return $this;

    }

    /**
     * Agrega contenido al final del innerHTML
     * @method addFinal
     */
    function addFinal($html) {

        $this->innerHTML($this->innerHTML() . "\n" . $html);

        return $this;

    }

    /**
     * Envuelve el innerHTML del Selector creado en otro selector
     *
     * El nuevo selector creado se convertirá en el innerHTML.
     * @method envolver
     *
     * @param $selector
     */
    function envolver($selector, $attr = []) {

        $envoltorio = new Selector($selector);
        $envoltorio->attr($attr);
        $this->contenido = $envoltorio;

        return $this;
    }

    /**
     *  Ejecuta una funcion del programador sobre el selector
     *
     * @method ejecutarFuncion
     */
    function ejecutarFuncion($funcion) {

        $numeroArgs = func_num_args();

        if ($numeroArgs > 1) {
            $args = func_get_args();
            $args[0] = $this;

            call_user_func_array($funcion, $args);

        }
        else {
            $funcion($this);
        }

        return $this;
    }

    /**
     * Retorna el tipo de selector
     *
     * @since 0.5
     */
    function obtSelector() {

        return $this->selector;

    }

}


