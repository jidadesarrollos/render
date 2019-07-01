<?php
/**
 * Clase para Formularios
 *
 * @internal Renderiza formularios configurados en html visible para el usuario,
 * permite la validación de los mismos y la definición de su estructura.
 *
 * Codigo Error: 7
 *
 * @author   Julio Rodriguez
 * @package
 * @version
 * @category
 */

namespace JidaRender;

use Jida\Manager\Excepcion;
use Jida\Core\Rutas;
use Jida\Medios as Medios;
use Jida\BD\BD as BD;
use Jida\Manager\Estructura;
use JidaRender\Inputs\Input as SelectorInput;

class Formulario extends Selector {

    /**
     * Codigo de excepcion para el objeto
     *
     * @var $_ce ;
     */
    static private $_ce     = 20001;
    public         $name;
    public         $tagPost = true;
    public         $action  = "";
    public         $method  = "POST";
    public         $enctype = "application/x-www-form-urlencoded";
    public         $target  = "";

    /**
     * Determina si los valores del formulario deben ser validados o cambiados a entidades HTML
     *
     * @var $setHtmlEntities
     * @revision
     */
    public $setHtmlEntities = true;
    /**
     * Define si la etiqueta form debe ser integrada
     *
     * @var boolean $tagForm
     * @default true
     */
    public $tagForm = true;
    /**
     * Define si se agrega un boton submit al formulario
     *
     * @var boolean $botonEnvio
     * @default true
     */
    public $botonEnvio = true;
    /**
     * Define si se agregan las propiedades para uso del validador js
     *
     * @var boolean $jidaValidador
     */
    public $jidaValidador = true;
    /**
     * Label a usar en el boton de envio por defecto
     *
     * @var string $_labelBotonEnvio
     */
    public  $_labelBotonEnvio = "Guardar";
    /**
     * Define si el formulario lleva labels o no
     *
     * @internal Si esta definido en TRUE el formulario busca el valor name en el
     * json y lo agrega
     * @var boolean labels
     * @access   public
     */
    public $labels = true;
    /**
     * Arreglo con botones del formulario
     *
     * @var array $_botones
     */
    public $_botones;
    /**
     * Registra el query realizado para obtener la data en modo update
     *
     * @var string $_consultaUpdate
     */
    private $_consultaUpdate = "";
    private $_fieldsets       = [];
    /**
     * @var string $_id id del formulario
     * @access private
     */
    private $_id;
    /**
     * Agrega un titulo al formulario
     *
     * @var Selector $_titulo
     * @see Selector
     */
    private $_titulo = false;
    /**
     * Registra el orden de los campos
     *
     * @internal esta funcion deberia ser provisional para que luego sea
     * reemplazada por una lógica de ordenamiento sobre el arreglo de campos
     * @var array $_arrayOrden
     */
    private $_arrayOrden = [];
    /**
     * Estructura html que se implementa por cada item del formulario
     *
     * @public $_plantillaItem
     * @access private
     */
    private $_plantillaItem    =
        '
	<section class="col-md-{{:cols}}">
		<div class="form-group">
		{{:contenido}}
		</div>
	</section>
	';
    private $_plantillaBotones =
        '<section class="row">
		<div class="col-md-12 {{:cssColumnaBotones}}">
			<div class="{{:cssContenedorBotones}}">
				{{:botones}}
			</div>
		</div>
	</section>';
    /**
     * @var array $_configuracion Configuracion del formulario
     */
    private $_configuracion;
    /**
     * Define el numero de columnas a manejar en el grid
     *
     * @internal
     *
     * La clase Formulario trabaja con un sistema de columnas, por defecto el de bootstrap, el cual se divide en 12
     * columnas, sin embargo estos valores pueden ser modificados por medio de esta variable
     * @var int $_columnasTotal
     */
    private $_columnasTotal = 12;
    private $_css           = [
        'input'             => 'form-control',
        'titulo'            => 'titulo-form',
        'columnaBotones'    => 'col-md-12 text-right',
        'contenedorBotones' => 'btn-group',
        'botonEnvio'        => 'btn btn-primary',
        'botones'           => 'btn btn-default',
        'seccionTitulo'     => 'page-header',

    ];
    /**
     * Expresion regular para validar estructura
     *
     * @var string $_exprEstructura
     */
    private $_exprEstructura = '/^\d+((\[(\d+|,\d+|x\d+)*\])|x\d+|;\d+|,\d+)*$/';
    /**
     * Define la ubicacion del archivo de configuracion del formulario
     *
     * @var $_path
     * @access private
     */
    private $_path;
    /**
     * Arreglo de campos del formulario
     *
     * @var array $_campos
     */
    private $_campos;
    /**
     * Estructura del formulario
     *
     * @internal Registra la estructura agregada en el formulario, ya parseada a las columnas correspondientes.
     *
     * @var mixed $_estructura
     */
    private $_estructura = [];
    /**
     * Numero total de campos en el Formulario
     *
     * @var $_totalCampos ;
     */
    private $_totalCampos;
    /**
     * Define el identificador para buscar data en modo update
     *
     * @param mixed $_idEdicion ;
     *
     * @internal Si su valor es vacio el formulario se armara en modo
     * insert, caso contrario modo update
     *
     */
    private $_idEdicion;
    /**
     * Data obtenida para mostrar en modo update
     *
     * @var array $_dataUpdate ;
     */
    private $_dataUpdate = [];
    /**
     * Guarda el total de registros traidos en la consulta a base de datos
     * para manejarlo en campos de selección multiple
     *
     * @var array $_dataUpdateMultiple
     */
    private $_dataUpdateMultiple = [];
    /**
     * Registra los errores obtenidos en el formulario luego de la validación
     *
     * @var array $_errores ;
     */
    private $_errores = [];

    private $_validaciones = [];
    /**
     * @var array Arreglo q que contiene los objetos de cada campo leido del json
     */
    private $_dataCampos = [];

    /**
     *
     * @throws Excepcion
     */
    function __construct($form = "", $dataEdicion = "") {

        $this->_conf = $GLOBALS['JIDA_CONF'];

        if ($form) {
            $this->_cargarFormulario($form, $dataEdicion);
        }
        $this->_idEdicion = $dataEdicion;

        debug_backtrace()[1]['function'];

        $this->action = Estructura::url();
        $this->attr('action', $this->action);

        parent::__construct('form');

    }

    /**
     * Carga el Formulario a mostrar
     *
     * @param string $form Nombre del Formulario
     * @throws Excepcion
     * @internal Verifica si existe un archivo json para el formulario pedido, carga la informacion del mismo y la
     *           procesa.
     *
     * Los formularios deben encontrarse en la carpeta formularios de Aplicacion o Framework, caso contrario arrojara
     * excepcion.
     *
     * @method _cargarFormulario
     */
    private function _cargarFormulario($form, $dataEdicion) {

        if (!strrpos($form, ".json")) {
            $form = ucfirst($form) . ".json";
        }

        $path = $this->_obtRutaFormulario($form);
        if (!Medios\Directorios::validar($path)) {
            $msj = "No se consigue el archivo de configuracion del formulario $path";
            Excepcion::procesar($msj, self::$_ce . 1);
        }
        $this->_path = $path;

        $this
            ->validarJson();
        $this->_configuaricionInicial();

        $this->_procesarUpdate($dataEdicion);
        $this->_instanciarCamposConfiguracion();
        $this->_procesarEstructura();

    }

    /**
     * Define la ruta en la cual buscar el archivo de configuracion del formulario
     *
     * @param string $form Ruta del archivo json a buscar
     * @return string Ruta fisica donde debe ubicarse el formulario solicitado
     */
    private function _obtRutaFormulario($form) {

        $partes = explode("/", $form);

        if (count($partes) === 1) {
            return Estructura::$directorio . "/Aplicacion/Formularios/$form";
        }

        $jida = array_shift($partes);
        $ruta = Estructura::$rutaJida;

        if (strtolower($jida) != 'jida') {
            if (strtolower($jida) === 'jadmin')
                $ruta = Estructura::$directorio . '/Jadmin';
            else {
                $ruta = Estructura::$directorio . '/Aplicacion';
                array_unshift($partes, $jida);
            }
        }

        if (count($partes) > 1) {
            $ruta .= '/Modulos/' . ucfirst(array_shift($partes));
        }

        $ruta .= '/Formularios/' . ucfirst(array_shift($partes));

        return $ruta;

    }

    private function validarJson() {

        $contenido = file_get_contents($this->_path);
        $this->_configuracion = json_decode($contenido);

        if (json_last_error() != JSON_ERROR_NONE) {
            $msj = "El formulario  {$this->_path} no esta estructurado correctamente";
            Excepcion::procesar($msj, self::$_ce . 2);

        }

        return $this;
    }

    private function _configuaricionInicial() {

        $this->_id = $this->_configuracion->identificador;

        $this->attr([
            'id'      => 'form' . $this->_id,
            'method'  => 'POST',
            'name'    => 'form' . $this->_id,
            'role'    => 'form',
            'class'   => $this->css('form'),
            'target'  => $this->target,
            'enctype' => $this->enctype
        ]);

        $this->_botonEnvio();
    }

    /**
     * Get y Set para css de los componentes del formulario
     * @method css
     *
     * @param string $elemento Elemento al que acceder
     * @param string $css [opcional] Si es pasado, sera asignado como clase css a $elemento
     *
     * @return mixed Si el metodo es usado como setter retornara el mismo objeto form,
     * si es usado como getter retornara la clase del elemento si es conseguido, caso contrario
     * retornara un string vacio
     */
    function css($elemento, $css = "") {

        if (!empty($css)) {
            $this->_css[$elemento] = $css;

            return $this;

        }
        else {
            if (array_key_exists($elemento, $this->_css)) {
                return $this->_css[$elemento];
            }

        }

        return "";
    }

    /**
     * Genera el boton de envio si es requerido
     */
    private function _botonEnvio() {

        if ($this->botonEnvio) {
            $id = 'btn' . $this->_id;

            $btn = new Selector('input');

            $btn->attr([
                'id'    => $id,
                'name'  => $id,
                'type'  => 'submit',
                'value' => 'Guardar'
            ])->addClass($this->css('botonEnvio'));

            if ($this->jidaValidador) {
                $btn->data('jida', 'validador');
            }
            $this->_botones['principal'] = $btn;

        }

    }

    /**
     * Procesa la informacion para renderizar el formulario en modo update
     *
     * @param mixed $dataEdicion Arreglo de datos en modo update o consulta a ejecutar en base de datos
     */
    private function _procesarUpdate($dataEdicion) {

        if (isset($_POST['btn' . $this->_id])) {
            $dataEdicion = $_POST;
        }

        if (empty($dataEdicion)) {
            return;
        }

        if (is_array($dataEdicion)) {

            $this->_dataUpdate = $dataEdicion;
            $this->_dataUpdateMultiple = $dataEdicion;

        }
        else {

            $this->_idEdicion = $dataEdicion;
            $this->_obtenerDataUpdate();
        }

    }

    private function _obtenerDataUpdate() {

        if (!is_object($this->_idEdicion)) {

            $query = $this->_configuracion->query . ' where ' . $this->_configuracion->clave_primaria . "='" . $this->_idEdicion . "'";

            $data = BD::query($query);
            $this->_consultaUpdate = $query;

            if (count($data) > 0) {
                $this->_dataUpdate = $data[0];

                $this->_dataUpdateMultiple = $data;
            }

        }

    }

    /**
     * Instancia los campos configurados del formulairo
     *
     * @internal gestiona los campos del formulario realizando una instancia
     * del objeto SelectorInput sobre cada campo para su posterior renderizacion
     * @method _instanciarCamposConfiguracion
     * @see      \JidaRender\SelectorInput
     * @use      self::labels
     */
    private function _instanciarCamposConfiguracion() {

        $this->_totalCampos = count((array)$this->_configuracion->campos);
        if ($this->_totalCampos < 1) {
            Excepcion::procesar("El formulario " . $this->_formulario . " no tiene campos registrados",
                self::$_ce . 6);
        }

        foreach ($this->_configuracion->campos as $id => $campo) {

            if (!is_object($campo)) {
                continue;
            }

            if (property_exists($campo, 'eventos')) {
                $this->_validaciones[$campo->name] = $campo->eventos;
                $this->_dataCampos[$campo->name] = (array)$campo;
            }

            if (!property_exists($campo, 'type')) {
                $campo->type = "text";
            }

            if (property_exists($campo, 'orden')) {
                $orden = $campo->orden;
            }
            else {
                $orden = 0;
            }
            if (!array_key_exists($orden, $this->_arrayOrden)) {
                $this->_arrayOrden[$orden] = $campo->id;
            }
            else {
                $this->_arrayOrden[] = $campo->id;
            }

            $this->_campos[$campo->id] = $this->_instanciarCampo($campo);

        }//fin foreach
        ksort($this->_arrayOrden);

        $this->_arrayOrden;

    }

    /**
     * Genera la instancia de un SelectorInput
     *
     * @throws \Exception
     * @since 0.6
     *
     */
    private function _instanciarCampo($_campo) {

        $selectorInput = $this->_obtSelector($_campo);

        if ($this->labels and $_campo->type != 'hidden') {

            $label = new Selector('label', ['for' => $_campo->id]);
            $label->innerHTML((property_exists($selectorInput, 'label') ? $_campo->label : $_campo->name));
            $selectorInput->label = $label;

        }

        if (array_key_exists($selectorInput->name, $this->_dataUpdate)) {
            $selectorInput->valor($this->_dataUpdate[$selectorInput->name]);
        }

        if (property_exists($_campo, 'eventos') and !empty($_campo->eventos)) {
            $selectorInput->data('validacion', json_encode((array)$_campo->eventos));
        }

        $selectorInput->configuracion = $selectorInput;

        return $selectorInput;
    }

    /**
     * Define el objeto SelectorInput a retornar
     * @method _obtSelector
     *
     * @param $_campo
     * @return SelectorInput|Inputs\InputSeleccion|Inputs\Select
     * @throws \Exception
     * @since 0.6
     */
    private function _obtSelector($_campo) {

        if (array_key_exists($_campo->name, $this->_dataUpdate)) {
            $_campo->value = $this->_dataUpdate[$_campo->name];
        }

        switch ($_campo->type) {
            case 'select':
                $selector = new Inputs\Select($_campo, ['padre' => $this]);
                break;
            case 'checkbox':
            case 'radio':
                $selector = new Inputs\InputSeleccion($_campo, ['padre' => $this]);
                break;
            default:
                $namespace = '\App\Config\Formularios\\';
                $claseUpper = $namespace . Medios\Cadenas::upperCamelCase($_campo->type);
                if (class_exists($claseUpper)) {
                    $selector = new $claseUpper($_campo, ['padre' => $this]);
                }
                else {
                    $selector = new SelectorInput($_campo, ['padre' => $this]);
                }
                break;
        }

        return $selector;

    }

    /**
     * Procesa la estructura del formulario
     *
     * @method _procesarEstructura
     * @access private
     */
    private function _procesarEstructura() {

        if (!property_exists($this->_configuracion, 'estructura')) {
            $estructura = '1x' . $this->_totalCampos;
        }
        else {

            $estructura = $this->_configuracion->estructura;
            if (empty($estructura)) {
                $estructura = '1x' . $this->_totalCampos;
            }

        }

        if (!preg_match($this->_exprEstructura, $estructura))
            Excepcion::procesar("La estructura pasada no es válida", self::$_ce . '3');

        $estructura = explode(";", $estructura);
        for ($i = 0; $i <= count($estructura) - 1; ++$i):

            if (strpos($estructura[$i], "[")) {

                $partes = explode("[", $estructura[$i]);
                $columnas = array_shift($partes);
                $partes = explode("]", implode($partes));
                $distribucion = array_shift($partes);
                $partes = array_filter($partes);
                $repeticiones = str_replace("x", "", implode($partes));

                if (empty($repeticiones)) {
                    $repeticiones = 1;
                }

                if (strpos($distribucion, "x")) {

                    $partesEstructura = explode(",", $distribucion);
                    $estructuraFinal = [];

                    foreach ($partesEstructura as $key => $columna) {

                        $segmentos = explode("x", $columna);

                        if (count($segmentos) > 1) {
                            for ($ji = 0; $ji < $segmentos[1]; $ji++) {
                                array_push($estructuraFinal, $segmentos[0]);
                            }
                        }
                        else {
                            array_push($estructuraFinal, $segmentos[0]);
                        }
                    }

                    $distribucion = implode(",", $estructuraFinal);
                }

            }
            else {

                if (strpos($estructura[$i], "x") !== false) {

                    $partes = explode("x", $estructura[$i]);
                    $columnas = array_shift($partes);
                    $repeticiones = array_shift($partes);

                }
                else {
                    $columnas = $estructura[$i];
                    $repeticiones = 1;
                }

                $columnasGrid = $this->_columnasTotal / $columnas;
                $pivote = 0;
                $distribucion = [];

                while ($pivote < $this->_columnasTotal) {

                    array_push($distribucion, $columnasGrid);
                    $pivote += $columnasGrid;

                }

                $distribucion = implode(",", $distribucion);

            }

            if (count(explode(",", $distribucion)) < $columnas) {
                $msj = "La estructura no esta armada correctamente. La distribución es menor a la cantidad de columnas $distribucion";
                Excepcion::procesar($msj, self::$_ce . '4');
            }

            for ($je = 0; $je < $repeticiones; $je++) {
                $this->_estructura = array_merge($this->_estructura, explode(",", $distribucion));
            }

        endfor;

        $camposEstructura = count($this->_estructura);

        if ($camposEstructura > $this->_totalCampos) {
            $msj = "La estructura tiene mayor cantidad de campos que el formulario. 
                    Campos estructura: {$camposEstructura} 
                    Form: {$this->_totalCampos} {$this->_configuracion->nombre}";
            Excepcion::procesar($msj, self::$_ce . '5');
        }

    }

    /**
     * Remueve la etiqueta FORM del formulario
     *
     * Esta funcion puede llamarse cuando se deseen integrar multiples formularios
     * en una misma pantalla
     * @method removerTagForm
     *
     * @param string $class Clase CSS que se desee agregar al div
     *
     * @return void
     */
    function removerTagForm($class = "form-alone") {

        $this->selector = 'DIV';
        $this->attr = [];
        $this->addClass($class);
    }

    /**
     * Permite agregar un titulo al formulario
     * @method titulo
     *
     * @param mixed $titulo Contenido del titulo.
     * @param string $selector Selector del titulo. por defecto es un h2
     * @param string $class Clase del Titulo
     *
     * @return object $this
     */
    function titulo($titulo, $selector = "h2", $class = "page-header") {

        if (empty($class))
            $class = $this->css('titulo');

        $this->_titulo = new Selector($selector, ['class' => $class]);
        $this->_titulo->innerHTML($titulo);

        return $this;
    }

    /**
     * Retorna los campos del formularo en un arreglo
     * @method enArreglo
     */
    function enArreglo() {

        foreach ($this->_campos as $key => $campo) {

            if ($campo->type != 'button') {
                $this->_campos[$key]->addClass($this->css('input'));
            }

        }

        return $this->_campos;
    }

    /**
     * Agrega Fielsets y legends a la estructua del formulario
     *
     */
    function fieldsets($fieldsets) {

        if (is_array($fieldsets) and count($fieldsets) > 0) {
            foreach ($fieldsets as $key => $value) {
                $id = "";
                if (!Medios\Numeros::validarInt($value)) {
                    $id = $key;
                    $fieldset = new Selector('fieldset', ['id' => 'field' . ucwords($this->_id) . '-' . $key]);

                    $legend = new Selector('legend');
                    $legend->innerHTML($value);
                    $fieldset->legend = $legend;

                }
                else {
                    $id = $value;
                    $fieldset = new Selector('fieldset', ['id' => $this->_id . '-' . $value]);
                }
                $this->_fieldsets[$id] = $fieldset;

            }

        }

    }

    /**
     * Renderiza un formulario
     *
     * @param array $titulos
     *
     * @internal Genera el HTML de un formulario creado en el Framework, con toda la personalizacion
     * creada
     * @method armarFormulario
     *
     * @example  $titulos = [0=>['limite'=>10,'titulo'=>'Titulo del fieldset']]
     */
    function render() {

        $i = 0;
        $actualFieldset = false;
        $columnas = 0;
        $contenedor = new Selector('article', ['id' => 'container' . ucwords($this->_id)]);
        $fields = (count($this->_fieldsets) > 0) ? true : false;

        if ($this->_titulo)
            $contenedor->addInicio($this->_titulo->render());

        if (Medios\Sesion::obt('__msjForm')) {
            $this->addFinal(Medios\Sesion::obt('__msjForm'));
            Medios\Sesion::destruir('__msjForm');
        }

        foreach ($this->_arrayOrden as $id => $position) {

            $content = "";
            $campo = $this->_campos[$position];
            if ($columnas == 0) {
                $filaPivote = new Selector('section', ['class' => 'row']);

            }
            $columna = $this->_estructura[$i];
            $columnas += $columna;

            if ($campo->type != 'button') {
                $campo->addClass($this->css('input'));
            }

            $content .= $campo->render();
            $html = str_replace("{{:cols}}", $columna, $this->_plantillaItem);

            if (is_object($campo->label)) {
                $content = $campo->label->render() . $content;
            }

            $html = str_replace("{{:contenido}}", $content, $html);
            $filaPivote->addFinal($html);
            if ($fields and array_key_exists($i, $this->_fieldsets)) {
                if ($actualFieldset) {

                    if ($this->tagForm)
                        $this->addFinal($actualFieldset->render());
                    else {
                        $contenedor->addFinal($actualFieldset->render());
                    }
                }
                $actualFieldset = $this->_fieldsets[$i];
                $actualFieldset->addFinal($actualFieldset->legend->render());
            }

            if ($columnas >= 12) {
                $columnas = 0;
                if ($fields) {
                    if ($actualFieldset) {
                        $actualFieldset->addFinal($filaPivote->render());
                    }

                }
                else {
                    if ($this->tagForm)
                        $this->addFinal($filaPivote->render());
                    else {
                        $contenedor->addFinal($filaPivote->render());
                    }
                }

            }
            ++$i;
        }

        if ($actualFieldset) {
            $this->addFinal($actualFieldset->render());
        }

        if ($this->tagForm) {

            if ($this->botonEnvio) {
                $this->addFinal($this->imprimirBotones());
            }
            $contenedor->addFinal(parent::render());

        }

        return $contenedor->render();

    }

    /**
     * Renderiza el HTML de los botones agregados al formulario
     * @method imprimirBotones
     *
     * @param boolean $plantilla true;
     */
    function imprimirBotones() {

        $botones = "";
        foreach (array_reverse($this->_botones) as $id => $boton) {

            if ($boton->attr('class') == "")
                $boton->addClass($this->css('botones'));
            $botones .= $boton->render();

        }

        return $this->_obtTemplate($this->_plantillaBotones,
            [
                'botones'              => $botones,
                'cssContenedorBotones' => $this->css('contenedorBotones'),
                'cssColumnaBotones'    => $this->css('columnaBotones')
            ]);
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

    /**
     * Realiza lo mismo que la funcion armarFormulario
     *
     * @internal   Se mantiene la funcion para poder realizar la transicion de formularios
     * usados con la clase Formulario, sin embargo el funcionamiento es el mismo ahora que
     * el de armarFormulario, por tanto no se aconseja su uso.
     * @deprecated 1.4
     */
    function armarFormularioEstructura() {

        $this->armarFormulario();
    }

    /**
     * Permite configurar botones para el formulario
     *
     * @param string $boton identificador del Boton.
     * @param string $label [opcional] Si es pasado sera agregado como label del boton
     *
     * @return object $selector Objeto Selector
     * @internal Permite acceder a la clase Selector del boton pedido para configurarlo
     *
     * @see      Selector
     * @method boton
     *
     *
     */
    function boton($boton, $label = "", $selector = "button") {

        if (isset($this->_botones[$boton])) {

            if (!empty($label)) {
                $btn = $this->_botones[$boton];
                if ($btn->obtSelector() == 'input') {
                    $this->_botones[$boton]->attr('value', $label);
                }
                else {
                    $this->_botones[$boton]->innerHTML($label);
                }

            }

        }
        else {

            $btn = new Selector($selector, ['type' => "submit", "name" => $boton, "id" => $boton]);
            $btn->innerHTML($label);
            $this->_botones[$boton] = $btn;

        }
        return $this->_botones[$boton];
    }

    /**
     * Valida un formulario
     *
     * @param array $data Arreglo de data a validar, generalmente corresponde a la data post.
     * @internal Verifica que la data pasada cumpla con las validaciones registradas en el formulario
     *
     * @method validar
     */
    function validar(&$data = "") {

        if (empty($data)) {
            $data =& $_POST;
        }

        foreach ($this->_validaciones as $campo => $validaciones) {

            if (array_key_exists($campo, $data)) {

                $valorCampo = $data[$campo];

                $validador = new ValidadorJida($this->_dataCampos[$campo], $validaciones);
                $resultado = $validador->validarCampo($data[$campo]);

                if ($resultado['validacion'] !== true) {
                    $this->_errores[$campo] = $resultado['validacion'];
                    $this->msj('error', $resultado['validacion']);
                }
                else {
                    $valorCampo = $resultado['campo'];
                }

                $datos[$campo] = (!is_array($data[$campo])) ? htmlspecialchars($valorCampo) : $valorCampo;
            }

        }

        if ($this->_errores) {
            Medios\Sesion::editar('__erroresForm', $this->_errores);
            Medios\Sesion::editar('_dataPostForm', $datos);
            Medios\Sesion::editar('__dataPostForm', 'id_form', $this->_idEdicion);

            return false;

        }

        return true;

    }

    /**
     * Crea un mensaje a mostrar en un grid u objeto Tipo Vista
     *
     * Define valores para las variables de sesion __msjVista e __idVista
     * @method msjVista
     *
     * @param string $type Tipo de mensaje, puede ser: success,error,alert,info
     * @param string $msj Contenido del mensaje
     * @param mixed $redirect Por defecto es false, si se desea redireccionar se pasa la url
     */
    static function msj($type, $msj, $redirect = false) {

        $msj = Medios\Mensajes::crear($type, $msj, true);
        Medios\Sesion::editar('__msjForm', $msj);
        if ($redirect) {
            \Jida\Medios\Rutas::redireccionar($redirect);
        }
    }

    /**
     * Permite acceder al objeto selector de un campo
     * @method campo
     *
     * @param string $id Identificador del campo
     *
     * @return object SelectorInput
     */
    function campo($id) {

        if (array_key_exists($id, $this->_campos)) {

            return $this->_campos[$id];
        }
        else {
            Excepcion::procesar("No existe el campo solicitado", self::$_ce . '7');

        }
    }

    function obtConsultaUpdate() {

        return $this->_consultaUpdate;
    }

    function obtErrores() {

        return $this->_errores;
    }

}
