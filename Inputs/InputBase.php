<?php

namespace Render\Inputs;

use Render\Selector as Selector;


abstract class InputBase extends Selector {

    use \Jida\Core\ObjetoManager;
    var $name;
    var $type;
    var $id;
    var $label;
    /**
     * @var string Opciones Cadena de opciones pasada en la configuración del formulario
     */
    var $opciones;
    var $value = "";
    var $placeholder = "";
    protected $_ce = '110000';
    /**
     * @var mixed $_valor Valor del selector en formato de edición
     */
    protected $_valor;

    protected $_html;
    protected $_name;
    /**
     * Define el tipo de Selector de formulario
     *
     * @internal El valor por defecto es text
     * @var string $_tipo
     * @access   private
     */
    protected $_tipo = "text";

    /**
     * Atributos pasados en el constructor
     *
     * @var mixed $_attr ;
     * @access protected
     */
    protected $_attr = [];

    /**
     * Objeto padre que instancia al selector
     *
     * @var object $_padre ;
     */
    protected $_padre;
    /**
     * Retorna la estructura a renderizar
     *
     * @param string $estructura Nombre de la estructura
     *
     * @return string Html de la estructura solicitada
     */
    protected function estructura($estructura) {

        if (property_exists('\Render\Inputs\Estructura', $estructura)) {
            return Estructura::$$estructura;
        }

        return false;

    }

    /**
     * Renderiza el contenido en plantillas predeterminadas
     * @method _plantilla
     *
     * @param $plantilla ;
     */
    protected function _plantilla($template, $params) {

        foreach ($params as $key => $value) {
            $template = str_replace("{{:" . $key . "}}", $value, $template);
        }

        return $template;
    }

    /**
     * Asigna un valor al selector instanciado
     * @method valor
     *
     * @param string $valor Valor a mostrar en el input
     */
    abstract function valor($valor);
}