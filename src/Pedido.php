<?php

namespace Kawschool;

class Pedido{

    private $config;
    private $cn = null;

    public function __construct(){

        $this->config = parse_ini_file(__DIR__.'/../config.ini') ;

        $this->cn = new \PDO( $this->config['dns'], $this->config['usuario'],$this->config['clave'],array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        ));
        
    }

    public function registrar($_params){
        $sql = "INSERT INTO `pedidos`(`cliente_id`, `total`, `fecha`) 
        VALUES (:cliente_id,:total,:fecha)";

        $resultado = $this->cn->prepare($sql);

        $_array = array(
            ":cliente_id" => $_params['cliente_id'],
            ":total" => $_params['total'],
            ":fecha" => $_params['fecha'],
            
        );

        if($resultado->execute($_array))
            return $this->cn->lastInsertId();

        return false;
    }

    public function registrarDetalle($_params){
        // Consulta SQL para insertar en la tabla detalle_pedidos
        $sql = "INSERT INTO `detalle_pedidos`(`pedido_id`, `pelicula_id`, `precio`, `cantidad`) 
        VALUES (:pedido_id, :pelicula_id, :precio, :cantidad)";
        $resultado = $this->cn->prepare($sql);
    
        // Consulta SQL para obtener la cantidad actual de la película en la tabla peliculas
        $sqlProductos= "SELECT cantidad FROM peliculas WHERE id = :pelicula_id";
        $resultadoProductos = $this->cn->prepare($sqlProductos);
        $resultadoProductos->bindParam(':pelicula_id', $_params['pelicula_id']);
        $resultadoProductos->execute();
        $cantidadProductos = $resultadoProductos->fetchColumn();
    
        // Actualizar la cantidad de la película en la tabla peliculas
        $cantidadNueva = $cantidadProductos - $_params['cantidad']; // Restar la cantidad vendida
        $sqlActualizar = "UPDATE peliculas SET cantidad = :cantidad WHERE id = :pelicula_id";
        $resultadoActualizar = $this->cn->prepare($sqlActualizar);
        $resultadoActualizar->bindParam(':cantidad', $cantidadNueva);
        $resultadoActualizar->bindParam(':pelicula_id', $_params['pelicula_id']);
        $resultadoActualizar->execute();
    
        // Array con los valores para la inserción en la tabla detalle_pedidos
        $_array = array(
            ":pedido_id" => $_params['pedido_id'],
            ":pelicula_id" => $_params['pelicula_id'],
            ":precio" => $_params['precio'],
            ":cantidad" => $_params['cantidad'],
        );
    
        // Ejecutar la inserción en la tabla detalle_pedidos
        if($resultado->execute($_array))
            return  true;
    
        return false;
    }    


    public function mostrar()
    {
        $sql = "SELECT p.id, nombre, apellidos, email, total, fecha FROM pedidos p 
        INNER JOIN clientes c ON p.cliente_id = c.id ORDER BY p.id DESC";

        $resultado = $this->cn->prepare($sql);

        if($resultado->execute())
            return  $resultado->fetchAll();

        return false;

    }
    public function mostrarUltimos()
    {
        $sql = "SELECT p.id, nombre, apellidos, email, total, fecha FROM pedidos p 
        INNER JOIN clientes c ON p.cliente_id = c.id ORDER BY p.id DESC LIMIT 10";

        $resultado = $this->cn->prepare($sql);

        if($resultado->execute())
            return  $resultado->fetchAll();

        return false;

    }

    public function mostrarPorId($id)
    {
        $sql = "SELECT p.id, nombre, apellidos, email, total, fecha FROM pedidos p 
        INNER JOIN clientes c ON p.cliente_id = c.id WHERE p.id = :id";

        $resultado = $this->cn->prepare($sql);

        $_array = array(
            ':id'=>$id
        );

        if($resultado->execute($_array ))
            return  $resultado->fetch();

        return false;
    }

    

    public function mostrarDetallePorIdPedido($id)
    {
        $sql = "SELECT 
                dp.id,
                pe.titulo,
                dp.precio,
                dp.cantidad,
                pe.foto
                FROM detalle_pedidos dp
                INNER JOIN peliculas pe ON pe.id= dp.pelicula_id
                WHERE dp.pedido_id = :id";

        $resultado = $this->cn->prepare($sql);

        $_array = array(
            ':id'=>$id
        );

        if($resultado->execute( $_array))
            return  $resultado->fetchAll();

        return false;

    }



}