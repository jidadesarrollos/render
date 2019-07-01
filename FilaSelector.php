<?php
/**
* Clase FilaSelector
* @author Julio Rodriguez
* @package
* @version
* @category
*/

namespace Render;
use Jida\Medios as Medios;
use Exception;

class FilaSelector extends Selector{

	use \Jida\Core\ObjetoManager;

	var $selector = "TR";
	private $selectorColumnas="TD";
	var $columnas;
	var $dataColumnas;
	private $totalColumnas;
    private $_ce='008';

	function __construct($columnas,$selectorColumnas="TD"){

		parent::__construct();
		#Medios\Debug::imprimir($columnas);
		$this->dataColumnas = $columnas;
		$this->selectorColumnas = $selectorColumnas;
		$this->totalColumnas = count($this->dataColumnas);
		$this->crearColumnas();
	}


	private function crearColumnas(){

        if(!is_array($this->dataColumnas)){
            
            throw new Exception('La data para la columna debe ser un arreglo',$this->_ce."1");
        }else{
            
        }
		foreach ($this->dataColumnas as $key => $col) {

			$this->columnas[$key] = new ColumnaSelector($this->selectorColumnas);

			$this->columnas[$key]->innerHTML($col);
		}


	}

	private function generarContenido(){
		foreach ($this->columnas as $key => $columna) {
			$this->innerHTML.=$columna->render();

		}

		return $this;
	}

	function renderizar(){
		$html = $this->generarContenido()->render();
		return $html;
	}

	function columnas(){
		return $this->columnas;
	}


	/**
	 * Agrega una nueva columna al final de la fila
	 *
	 * El contenido de la columna debe ser especificado por el desarrollador por
	 * medio de una función pasada como parametro. La función recibe el arreglo de columnas
	 * existentes
	 *
	 * @method agregarColumna
	 * @param function $funcion Funcion creada por el usuario. Debe retornar innerHTML.
	 */
	function agregarColumna($contenido){
		$numeroArgs = func_num_args();

		$nueva = new ColumnaSelector();

		$nueva->innerHTML($contenido);
		array_push($this->columnas,$nueva);

	}
	/**
	 * Retorna la columna especificada
	 * @method columna
	 * @param mixed $col Nombre o indice de la columna
	 * @return ColumnaSelector
	 * @since 1.4
	 */
	function columna($col){

		if(array_key_exists($col, $this->columnas)){
			return $this->columnas[$col];
		}
		return false;
	}
	/**
	 * Devuelve la columna correpondiente al indice pasado
	 *
	 * @method columnaIndice
	 * @param int $pos Numero de columna requerida
	 * @return ColumnaSelector
	 * @since 1.4
	 */
	function columnaIndice($pos){
		$filas =array_keys($this->columnas);

		if(	array_key_exists($pos, $filas) and
			array_key_exists($filas[$pos], $this->columnas))
		{
			return $this->columnas[$filas[$pos]];
		}


	}



}
