# Formularios

Permite renderizar formularios personalizados y realiza la integración
con validación y data obtenida de base de datos.

Sinopsis
---
```php
class \Render\Formulario extends Selector{
 ....
}
```
Instancia
---
```php

$form = new \Render\Formularios($ruta[, $update]);
```

Parametros
---
- **$ruta**: (string) Ruta del formulario en estructura de directorio. La ruta debe hacer uso de "/" para la separación de Paths. Si se desea implementar un formulario del framework se debe usar como ruta "jida/".
- **$update** (array| string) Arreglo de Data update o consulta para obtener la data..


Metodos
--
- render: Renderiza el HTML del Formulario
- campo
- enArreglo
- boton
- validar : Realiza la validación del formulario.