<?php
namespace JidaRender\Inputs;

interface SeleccionInterface {

    function __construct($selector = "", array $attr = []);

    function agregarOpciones($opciones);


}