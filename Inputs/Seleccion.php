<?php

namespace JidaRender\Inputs;

use Jida\BD\BD as BD;
use Jida\Medios as Medios;
use Exception as Excepcion;
use JidaRender\CloneSelector as CloneSelector;
use JidaRender\Selector as Selector;

trait Seleccion {


    /**
     * Contiene los objetos SelectorInput de cada opcion de un control
     * de seleccion múltiple
     *
     * @param array $_selectoresOpcion
     */
    protected $_selectoresOpcion = [];

    protected $_opciones = [];

    /**
     * Verifica las opciones disponibles para el selector
     *
     * Verifica las opciones pasadas para la funcionalidad del selector y las estructura
     * para que sean usadas, las opciones a armar pueden ser pasadas de tres formas:
     *  - En un orden clave=valor
     *  - Con una consulta a base de datos
     *  - Pasando un key externo para hacer referencia a que serán seteadas de forma dinamica.
     *
     * @param $opciones
     */
    protected function _obtOpciones() {

        $revisiones = explode(";", $this->opciones);
        $opciones = [];

        $valorPadre = $this->_valorPadre();

        foreach ($revisiones as $key => $opcion) {

            if (stripos($opcion, 'select ') !== FALSE) {

                if ($valorPadre) {
                    $padre = ($this->data('campo')) ? $this->data('campo') : $this->data('dependiente');
                    $opcion .= " where $padre='$valorPadre'";
                }
                $data = BD::query($opcion);

                foreach ($data as $key => $opcion) {

                    if (!is_array($opcion)) {
                        $data = explode("=", $opcion);
                        $opciones[$data[0]] = trim($data[1]);
                    } else {
                        $keys = array_keys($opcion);
                        $valor = $opcion[$keys[0]];

                        $opciones[$valor] = $opcion[$keys[1]];
                    }

                }

            } elseif (stripos($opcion, 'externo') !== FALSE) {
                continue;

            } else {
                $data = explode("=", $opcion);
                $opciones[$data[0]] = trim($data[1]);
            }
        }

        $this->_opciones = array_filter($opciones);

    }

    /**
     * Agrega opciones al Selector de selecion
     * @method agregarOpciones
     *
     * @param      $opciones
     * @param bool $adicion Si es pasada en true y el selector tenia opciones las opciones
     *                      pasadas son agregadas, si es false se reemplazarán.
     *
     * @return $this
     * @throws \Exception
     */
    function agregarOpciones($opciones, $adicion = FALSE) {

        if (!is_array($opciones)) {
            throw new Excepcion('Las opciones no se han pasado correctamente', $this->_ce . '000008');
        }

        if ($adicion) {
            $this->_opciones = array_merge($this->_opciones, $opciones);
        } else {
            $this->_opciones = $opciones;
        }
        $this->_procesarArregloOpciones();

        return $this;

    }

    protected function _valorPadre() {

        if (!$this->data('dependiente')) {
            return;
        }

        $padre = $this->data('dependiente');
        if (!$this->_padre->campo($padre)) {
            return;
        }

        $campo = $this->_padre->campo($padre);

        return $campo->value;

    }


}