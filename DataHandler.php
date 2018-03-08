<?php
require( "DataObject.php" );
class DataHandler{
    
    private $dataServers = array();
    private $vatsim_data_lines = array();
    
    function __construct(){
        
        if( $this->shouldUpdateData() ){ $this->updateData(); }
        $this->vatsim_data_lines = explode( "\n", file_get_contents( "vatsim-data.txt" ) );
        
    }
    
    function shouldUpdateData(){
        
        if( !file_exists( "vatsim-data.txt" ) ){ return true; }
        $vatsim_data = file_get_contents( "vatsim-data.txt" );
        foreach( explode( "\n", $vatsim_data ) as $line ){
            
            if( preg_match( "/UPDATE = /", $line ) ){

                $date = explode( "UPDATE = ", $line )[ 1 ];
                $year = substr( $date, 0, 4 );
                $month = substr( $date, 4, 2 );
                $day = substr( $date, 6, 2 );
                $time = substr( $date, 8, 2 ) . ":" . substr( $date, 10, 2 ) . ":" . substr( $date, 12, 2 );
                
                return strtotime( $day . "-" . $month . "-" . $year . " " . $time ) + 120 < time();
                
            }
            
        }
        
        return true;
        
    }
    
    function updateData(){
        
        $status = file_get_contents( "https://status.vatsim.net/status.php" );
        foreach( explode( "\n", $status ) as $line ){
            if( preg_match( "/url0=/", $line ) ){
                
                array_push( $this->dataServers, trim( explode( "url0=", $line )[ 1 ] ) );
                
            }
            
        }
        $vatsim_data = file_get_contents( $this->dataServers[ array_rand( $this->dataServers ) ] );
        file_put_contents( "vatsim-data.txt", $vatsim_data );
        
    }
    
    function getClients(){
        
        $clients = array();
        $heading = array();
        $clientContent = false;
        
        foreach( $this->vatsim_data_lines as $rownum=>$line ){
            
            if( !$clientContent ){
                
                if( preg_match( "/; !CLIENTS section -/", $line ) ){
                    
                    $heading = explode( ":", explode( "; !CLIENTS section -         ", $line )[ 1 ] );
                    array_pop( $heading );
                    
                }
                
                $clientContent = preg_match( "/!CLIENTS:/", $line );
                
            } else {
                
                if( $line == ";\r" and preg_match('/;/', $this->vatsim_data_lines[ $rownum ] ) ){ break; }
                $curLine = explode( ":", $line );
                
                $data = array();
                foreach( $heading as $headingNum => $headingTitle ){
                    
                    $data[ $headingTitle ] = $curLine[ $headingNum ];
                    
                }
                
                array_push( $clients, new DataObject( $data ) );
                
            }
        }
        
        return $clients;
        
    }
    
    function getPrefiled(){
        
        $prefiled = array();
        $heading = array();
        $clientContent = false;
        
        foreach( $this->vatsim_data_lines as $rownum=>$line ){
            
            if( !$clientContent ){
                
                if( preg_match( "/; !PREFILE section -         /", $line ) ){
                    
                    $heading = explode( ":", explode( "; !PREFILE section -         ", $line )[ 1 ] );
                    array_pop( $heading );
                    
                }
                
                $clientContent = preg_match( "/!PREFILE:/", $line );;
                
            } else {
                
                if( $line == ";\r" and preg_match('/;/', $this->vatsim_data_lines[ $rownum ] ) ){ break; }
                $curLine = explode( ":", $line );
                
                $data = array();
                foreach( $heading as $headingNum => $headingTitle ){
                    
                    $data[ $headingTitle ] = $curLine[ $headingNum ];
                    
                }
                
                array_push( $prefiled, new DataObject( $data ) );
                
            }
        }
        
        return $prefiled;
        
    }
    
}
