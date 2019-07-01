<?php
/**
 * Clase para TablaSelector
 */

namespace Render;

use Exception;

class TablaSelector extends Selector {
    use \Jida\Core\ObjetoManager;
    private $filas = [];
    private $totalFilas;
    private $htmlFilas;
    private $htmlCols;
    private $tHead;
    private $tBody;
    private $tFoot;
    private $dataTabla;
    private $dataThead;
    private $dataTfoot;
    var $selector = "TABLE";

    function __construct($data = [], $dataThead = [], $dataTfoot = "") {
        parent::__construct();
        if (count($data) > 1) {

            $this->inicializarTabla($data, $dataThead, $dataTfoot);
        }

    }

    function inicializarTabla($data, $dataTHead = [], $dataTfoot = []) {
        $this->totalFilas = count($data);
        $this->dataTabla = $data;
        $this->dataThead = $dataTHead;
        $this->dataTfoot = $dataTfoot;

        if (count($dataTHead) > 0) {

            if (count($dataTHead) != $this->obtTotalColumnas())
                throw new Exception("Los titulos de la tabla no coinciden con el contenido", 1);
            $this->validarTHead();
        }

        $this->crearFilas();
    }

    /**
     * Crea un selector thead dentro de la tabla
     * @method validarTHead
     *
     * @param array $data Arreglo de Titulos a ser pasados al objeto
     * FilaSelector
     * @see Render\FilaSelector
     */
    function crearTHead($data) {
        $this->dataThead = $data;
        $this->tHead = new Selector('THEAD');
        $this->tHead->Fila = new FilaSelector($this->dataThead, 'TH');

    }

    function thead() {
        return $this->tHead;
    }

    function tbody() {
        return $this->tBody;
    }

    function tfoot() {
        return $this->tFoot;
    }

    function obtTotalColumnas() {
        return count($this->dataTabla[0]);
    }

    /**
     * Crea las filas de la tabla
     * @method
     */
    private function crearFilas() {

        foreach ($this->dataTabla as $idFila => $ColumnasFila) {
            $this->filas[$idFila] = new FilaSelector($ColumnasFila);
        }
    }

    function generar() {
        $this->renderTHead();
        $this->renderTBody();
        $this->renderTFoot();

        return parent::render();
    }

    function render() {
        return $this->generar();
    }

    private function renderTHead() {
        if (is_object($this->tHead)) {

            $this->innerHTML(
                $this->tHead->innerHTML($this->tHead->Fila->renderizar())->render()
            );
        }

    }

    private function renderTBody() {
        foreach ($this->filas as $key => $fila) {
            $this->innerHTML .= $fila->renderizar();
        }

        return $this;
    }

    private function renderTFoot() {

    }

    /**
     * Ejecuta una funcion sobre toda una fila
     * @method funcionFila
     *
     * @param mixed $evalucion Index de la fila o arreglo de validacion donde el key
     * sea la columna a evaluar y el value el valor a comparar
     * @incompleto
     */
    function funcionFila($evaluacion, $funcion = "") {

    }

    /**
     * Ejecuta una funcion sobre una columna de la tabla
     * @method funcionColumna
     */
    function funcionColumna($columna, $funcion = "", $data = []) {

        foreach ($this->filas as $key => $fila) {
            $keys = array_keys($fila->columnas);
            if (!array_key_exists($columna, $keys)) throw new Exception("La columna indicada no existe en la vista", 4);

            $fila->columnas[$keys[$columna]]->ejecutarFuncion($funcion, $data);
        }
        return $this;
    }
    //function espe

    /**
     * Inserta una columna al final de la tabla
     * @method insertarColumna
     */
    function insertarColumna($funcion) {
        $numeroArgs = func_num_args();
        $args = func_get_args();;

        foreach ($this->filas as $key => $fila) {

            if ($numeroArgs > 1) {

                $args[$numeroArgs] = $fila;
                $contenido = call_user_func_array($funcion, $args);
            }
            else {
                $contenido = $funcion($this, $fila);
            }

            $fila->agregarColumna($contenido);
        }

        return $this;
    }

    function columna($columna, $fila) {

    }

    function fila($numeroFila) {
        if (array_key_exists($numeroFila, $this->filas))
            return $this->filas[$numeroFila];
    }

}
