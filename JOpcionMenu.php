<?php
/**
 * Objeto de opcion de un menu
 *
 * @author Julio Rodriguez
 * @package
 * @version
 * @category
 */

namespace Render;

class JOpcionMenu extends Selector {

    protected $innerHTML;
    private $url;
    private $padre = 0;
    private $hijo = 0;
    private $hijos = [];
    private $nombre;
    private $link;
    private $contentSubmenu;
    private $adicional;
    /**
     * Data para submenu de la opcion
     */
    private $dataSubmenu = [];
    /**
     * Instancia jMenu para el submenu a crear
     */
    private $submenu;

    private $idOpcion;
    private $keyComoId = TRUE;

    private $estructura = [
        'nombre'  => 'nombre_opcion',
        'padre'   => 'padre',
        'hijo'    => 'hijo',
        'link'    => 'link',
        'submenu' => []
    ];

    function __construct($data = [], $selector = "li") {
        parent::__construct($selector);
        $this->link = new Selector('a');
        if (is_array($data) and count($data) > 0) {

            $this->procesarData($data);
        }

        return $this;
    }

    function renderizar() {
        if (count($this->submenu) > 0) {
            $this->agregarSubmenu($this->submenu);
        }

        $a = $this->link->html($this->nombre)->render();

        $this->contenido = $a . $this->contentSubmenu;
        return $this->render();
    }

    private function procesarData($data) {
        if (array_key_exists($this->estructura['nombre'], $data))
            $this->nombre = $data[$this->estructura['nombre']];
        if (array_key_exists($this->estructura['padre'], $data))
            $this->padre = $data[$this->estructura['padre']];

        if (array_key_exists($this->estructura['hijo'], $data))
            $this->hijo = $data[$this->estructura['hijo']];
        if (array_key_exists('idOpcion', $data))
            $this->idOpcion = $data['idOpcion'];
        if (array_key_exists('submenu', $data))
            $this->dataSubmenu = $data['submenu'];
        if (array_key_exists($this->estructura['link'], $data))
            $this->link->attr('href', $data[$this->estructura['link']]);

    }

    function esPadre() {
        if ($this->hijo > 0) return true;
        return false;
    }

    function esSubopcion() {
        if ($this->padre > 0) return true;
        return false;
    }

    function agregarSubmenu($opciones) {
        $submenu = new JMenu();

        $this->submenu = $submenu->addOpciones($opciones, $this->idOpcion);
        //    $submenu->addOpciones($opciones,$this->idOpcion)
        //           ->obtHtml();

    }

    function tieneSubmenu() {

        if (count($this->dataSubmenu) > 0) return true;
        return false;
    }

    function obtId() {
        return $this->idOpcion;
    }

    function obtContenido() {
        if (is_object($this->submenu)) {

            $this->contenido .=
                $this->link->html($this->nombre)->render() .
                $this->submenu->obtHtml();
        }
        else {

            $this->contenido .= $this->nombre;
        }
        return $this;
    }

    function obtSubmenu() {
        return $this->dataSubmenu;
    }

    function link() {
        return $this->link;
    }

}
