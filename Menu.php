<?php

/**
 * Objeto Renderizador de Menu
 *
 * @author Rosmy Rodriguez
 * @package JidaFramework
 * @version 1.0
 * @since 1.6
 * @category Render
 */

namespace Render;

use App\Config\Configuracion;
use Jida\Configuracion\Config;
use Jida\Manager\Estructura;
use Jida\Manager\Excepcion;
use Jida\Medios as Medios;

class Menu extends Selector {

    /**
     * Codigo de excepcion para el objeto
     *
     * @var $_ce ;
     */
    static private $_ce = 100;

    /**
     * Define la ubicacion del archivo de configuracion del menu
     *
     * @var $_path
     * @access private
     */
    private $_path;

    /**
     * @var $menu Configuracion del menu
     */
    private $menu;

    /**
     * Contenido del menu en HTML
     *
     * @var $html
     */
    private $html;

    /**
     * Contenedor del menu, puede ser un <div> o <ul> etc. Por defecto es <ul>
     *
     * @var $contenedor
     */
    public $selectorMenu = "ul";

    /**
     * Selector de elementos del menu, puede ser <div> o <li> etc. Por defecto es <li>
     *
     * @var $selector
     */
    public $selectorItem = "li";

    /**
     * Funcion constructora
     * @method __construct
     */
    function __construct($menu = "", $path = false) {

        $this->_conf = Config::obtener();

        if ($menu) $this->cargarMenu($menu, $path);

        parent::__construct($menu);
    }

    /**
     * Carga el Menu a mostrar
     *
     * @param string $menu Nombre del Menu
     * @internal Verifica si existe un archivo json para el menu pedido, carga la informacion del mismo y la
     * procesa.
     *
     * Los menus deben encontrarse en la carpeta Menus de Aplicacion o Framework, caso contrario arrojara
     * excepcion.
     *
     * @method cargarMenu
     */
    private function cargarMenu($menu, $path) {

        try {

            if (!strrpos($menu, ".json")) {
                $menu = $menu . ".json";
            }

            $path = $this->_obtenerDirectorio($menu, $path);

            if (!Medios\Directorios::validar($path)) {
                $msj = "No se consigue el archivo de configuracion del menu $path";
                //throw new \Exception($msj, self::$_ce . 1);
                Excepcion::procesar($msj, self::$_ce . 1);
            }

            $this->_path = $path;
            $this->validarJson();
        }
        catch (\Exception $e) {
            Medios\Debug::imprimir($e->getMessage() . $e->getCode(), $e->getTrace());
        }

    }

    private function _obtenerDirectorio($menu, $path) {

        if ($path) {

            $directorio = $path . DS . $menu;
            if (!Medios\Directorios::validar($directorio)) {
                Excepcion::procesar("No existe el menu $menu", self::$_ce . 1);
            }

            return $path . DS . $menu;

        }

        $menu = strtolower($menu);
        $partes = array_filter(explode("/", $menu));

        if (count($partes) === 1) {
            return Estructura::$rutaAplicacion . '/Menus/' . $menu;
        }

        $modulo = array_shift($partes);
        if (strtolower($modulo) === 'jadmin') {

            $nombre = implode("/", $partes);

            if (Medios\Directorios::validar(Estructura::$rutaAplicacion . "/Jadmin/$nombre")) {
                return Estructura::$rutaAplicacion . "/Jadmin/$nombre";
            }

            return Estructura::$rutaJida . "/Jadmin/" . $nombre;
        }

        if (count($partes) > 1) {

            $ruta = Estructura::$ruta . '/Menus/';
            $configuracion = Config::obtener();
            $modulos = $configuracion::$modulos;
            if (!in_array($modulo, $modulos)) {
                Excepcion::procesar("No existe el modulo pasado", self::$_ce . 2);
            }
            if ($modulo !== 'app') {
                $ruta = Estructura::$rutaAplicacion . '/Modulos/' . ucfirst($modulo) . " / ";
            }

            return $ruta = $ruta . implode(" / ", $partes);
        }
    }

    private function validarJson() {

        $contenido = file_get_contents($this->_path);
        $this->menu = json_decode($contenido);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $msj = "El menu  " . $this->_path . " no esta estructurado correctamente";
            Excepcion::procesar($msj, self::$_ce . "1");
        }
        return $this;
    }

    /**
     * Funcion recursiva que genera el HTML del selector
     * @method _obtHtml
     *
     * @param array $menu Arreglo con la configuracion del menu
     */
    private function _obtHtml($menu) {

        $this->html .= "\n";
        $this->html .= "<" . (!empty($menu->selectorMenu) ? $menu->selectorMenu : $this->selectorMenu);

        if (!empty($menu->id)) {
            $this->html .= " id = \"" . $menu->id . "\"";
        }
        if (!empty($menu->class)) {
            $this->html .= " class=\"" . $menu->class . "\"";
        }
        if (!empty($menu->style)) {
            $this->html .= " style=\"" . $menu->style . "\"";
        }
        if (!empty($menu->attrs)) {
            foreach ($menu->attrs as $key => $value) {
                $this->html .= " " . $key . "=\"" . $value . "\"";
            }
        }
        $this->html .= ">\n";

        if (!empty($menu->items)) {

            foreach ($menu->items as $key => $item) {

                $this->html .= "<" . (!empty($menu->selectorItem) ? $menu->selectorItem : $this->selectorItem);

                if (empty($item->attrs)) {
                    $item->attrs = new \stdClass();
                }
                if (!empty($item->id)) {
                    $item->attrs->id = $item->id;
                }
                if (!empty($item->class)) {

                    $item->attrs->class = $item->class;
                }
                if (!empty($item->style)) {
                    $item->attrs->style = $item->style;
                }
                if (!empty($menu->itemAttrs)) {

                    $class1 = !empty($menu->itemAttrs->class) ? $menu->itemAttrs->class : '';
                    $class2 = !empty($item->attrs->class) ? $item->attrs->class : '';
                    foreach ($menu->itemAttrs as $key => $value) {

                        //TODO: Esta funcion se modifico, originalmente era
                        //$item->attrs->{$key} = !empty($item->attrs->{$key}) ? $value : $item->attrs->{$key};
                        $item->attrs->{$key} = $value;
                    }

                    $item->attrs->class = $class1 . " " . $class2;
                }

                foreach ($item->attrs as $key => $value) {
                    $this->html .= " " . $key . "=\"" . $value . "\"";
                }

                $this->html .= ">";
                if (!empty($item->preHtml)) {
                    $this->html .= $item->preHtml;
                }
                if (property_exists($item, 'url')) {

                    $url = (strpos($item->url, 'http') !== false) ? $item->url : "//" . Estructura::$urlBase . $item->url;
                    $attrs = ['href' => $url];
                    if (!empty($item->{"a-class"}))
                        $attrs['class'] = $item->{"a-class"};
                    $link = new Selector('a', $attrs);
                    $label = !empty($item->encode_html) ? htmlentities($item->label) : $item->label;
                    $link->addFinal($label);
                    $this->html .= $link->render() . "\n";
                }

                if (!empty($item->submenu)) {
                    $this->_obtHtml($item->submenu);
                }
                if (!empty($item->postHtml)) {
                    $this->html .= $item->postHtml;
                }
                $this->html .= "</" . (!empty($menu->selectorItem) ? $menu->selectorItem : $this->selectorItem) . ">\n";
            }
        }

        $this->html .= "</" . (!empty($menu->selectorMenu) ? $menu->selectorMenu : $this->selectorMenu) . ">\n";

        return $this->html;
    }

    /**
     * Renderiza el menu
     *
     * @internal Genera el HTML de un menu creado en el Framework, con toda la personalizacion creada
     * @method render
     */
    public function render() {

        $menu = $this->_obtHtml($this->menu);

        return $menu;
    }

}
