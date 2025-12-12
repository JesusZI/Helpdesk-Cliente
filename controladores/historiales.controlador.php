<?php

class ControladorHistoriales {

    /*=============================================
    MOSTRAR HISTORIALES
    =============================================*/
    static public function ctrMostrarHistoriales($fechaDesde = null, $fechaHasta = null, $usuarioId = null, $ticketId = null, $accion = null) {
        $tabla = "historiales";
        
        return ModeloHistoriales::mdlMostrarHistoriales($tabla, $fechaDesde, $fechaHasta, $usuarioId, $ticketId, $accion);
    }
    
    /*=============================================
    REGISTRAR HISTORIAL
    =============================================*/
    static public function ctrRegistrarHistorial($ticketId, $usuarioId, $accion, $detalles = null) {
        $tabla = "historiales";
        
        $datos = array(
            "ticket_id" => $ticketId,
            "usuario_id" => $usuarioId,
            "accion" => $accion,
            "detalles" => $detalles
        );
        
        return ModeloHistoriales::mdlRegistrarHistorial($tabla, $datos);
    }
}
