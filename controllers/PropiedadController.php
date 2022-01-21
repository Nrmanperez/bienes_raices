<?php

namespace Controllers;
use MVC\Router;
use Model\Propiedad;
use Model\Vendedor;
use Intervention\Image\ImageManagerStatic as Image;

class PropiedadController {
    public static function index(Router $router) {

        $propiedades = Propiedad::all();
        $vendedores = Vendedor::all();

        // Muestra mensaje condicional
        $registrado = $_GET['registrado'] ?? null;      // si no encuentra el valor se le asigna null

        $router->render('propiedades/admin', [
            'propiedades' => $propiedades,
            'registrado' => $registrado,
            'vendedores' => $vendedores
        ]);
    }

    public static function crear(Router $router) {

        $propiedad = new Propiedad;
        $vendedores = Vendedor::all();

        // Arreglos con mensajes de errores
        $errores = Propiedad::getErrores();

        if($_SERVER["REQUEST_METHOD"] === 'POST') {
             /* CREA UNA NUEVA INSTANCIA */
            $propiedad = new Propiedad($_POST['propiedad']);

            /* SUBIDA DE ARCHIVOS */

            // Generar un nombre unico
            $nombreImagen = md5( uniqid( rand(), true) ) . ".jpg";

            // Setear la imagen
            // Realiza un resize a la imagen con intervention
            if($_FILES['propiedad']['tmp_name']['imagen']) {
                $imagen = Image::make($_FILES['propiedad']['tmp_name']['imagen'])->fit(800,600);
                $propiedad->setImagen($nombreImagen);
            }
        
            // Validar
            $errores = $propiedad->validar();

            // Revisar que el arreglo de errores este vacio

            if(empty($errores)) {
            
            // Crear la carpeta para subir imagenes
            if(!is_dir(CARPETA_IMAGENES)) {
                mkdir(CARPETA_IMAGENES);
            }

            // Guarda la imagen en el servidor
            $imagen->save(CARPETA_IMAGENES . $nombreImagen);

            // Guarda la base de datos
            $propiedad->guardar();
  
            }
        }

        $router->render('propiedades/crear', [
            'propiedad' => $propiedad,
            'vendedores' => $vendedores,
            'errores' => $errores
        ]);
    }

    public static function actualizar(Router $router) {

        $id = validarORedireccionar('/admin');
        $propiedad = Propiedad::find($id);

        $vendedores = Vendedor::all();

        $errores = Propiedad::getErrores();

        if($_SERVER["REQUEST_METHOD"] === 'POST') {

            // Asiganar los atributos
            $args = $_POST['propiedad']; 
            
            $propiedad->sincronizar($args);
    
            // Validacion
            $errores = $propiedad->validar();
    
            // Subida de archivos
            // Generar un nombre unico
            $nombreImagen = md5( uniqid( rand(), true) ) . ".jpg";
    
            if($_FILES['propiedad']['tmp_name']['imagen']) {
                $image = Image::make($_FILES['propiedad']['tmp_name']['imagen'])->fit(800,600);
                $propiedad->setImagen($nombreImagen);
            }
    
    
            if(empty($errores)) {
                if($_FILES['propiedad']['tmp_name']['imagen']) {
                    // Almacenar la imagen
                    $image->save(CARPETA_IMAGENES . $nombreImagen);
                }
    
               $propiedad->guardar();  
            }  
        }

        $router->render('/propiedades/actualizar', [
            'propiedad' => $propiedad,
            'errores' => $errores,
            'vendedores' => $vendedores
        ]);
    }

    public static function eliminar(Router $router) {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar ID
            $id = $_POST['id'];
            $id = filter_var($id, FILTER_VALIDATE_INT);
        
            if($id) {
        
                $tipo = $_POST['tipo'];
        
                if(validarTipoContenido($tipo)) {
                    $propiedad = Propiedad::find($id);
                    $propiedad->eliminar();
                }
            }
        }
    }
}