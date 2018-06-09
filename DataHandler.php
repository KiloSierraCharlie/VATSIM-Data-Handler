<?php
namespace VATSIM;
require( "DataObject.php" );

class DataHandler{
    
    private $dataServers = array();
    private $vatsim_data_lines = array();
    public $lastUpdate = 0;
    private $storageLocation = null;
    
    function __construct( $storageLocation = null ){
        
        $this->storageLocation = $storageLocation == null ? dirname(__FILE__) : $storageLocation;
        if( $this->shouldUpdateData() ){ $this->updateData(); }
        $this->vatsim_data_lines = explode( "\n", file_get_contents( $this->storageLocation . "/vatsim-data.txt" ) );

    }
    
    function shouldUpdateData(){
        
        if( !file_exists( "vatsim-data.txt" ) ){ return true; }
        $vatsim_data = file_get_contents( $this->storageLocation . "/vatsim-data.txt" );
        foreach( explode( "\n", $vatsim_data ) as $line ){
            
            if( preg_match( "/UPDATE = /", $line ) ){

                $date = explode( "UPDATE = ", $line )[ 1 ];
                $year = substr( $date, 0, 4 );
                $month = substr( $date, 4, 2 );
                $day = substr( $date, 6, 2 );
                $time = substr( $date, 8, 2 ) . ":" . substr( $date, 10, 2 ) . ":" . substr( $date, 12, 2 );
                
                $this->lastUpdate = strtotime( "$day-$month-$year $time" );
                
                return $this->lastUpdate + 120 < time();
                
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
        file_put_contents( dirname(__FILE__) . "/vatsim-data.txt", $vatsim_data );
        
    }
    
    function getSection( $heading_searchFor, $section_searchFor ){
        
        $return = array();
        $heading = array();
        $haveSection = false;
        
        foreach( $this->vatsim_data_lines as $rownum=>$line ){
            
            if( $line == "\n" || $line == "\r" ){ continue; }
            
            if( !$haveSection ){
                
                if( preg_match( "/$heading_searchFor/", $line ) ){
                    
                    $heading = explode( ":", explode( $heading_searchFor, $line )[ 1 ] );
                    array_pop( $heading );
                    
                }
                
                $haveSection = preg_match( "/$section_searchFor/", $line );
                
            } else {
                
                if( $line == ";\r" ){ break; }
                $curLine = explode( ":", $line );
                
                $data = array();
                foreach( $heading as $headingNum => $headingTitle ){
                    
                    $data[ $headingTitle ] = $curLine[ $headingNum ];
                    
                }
                
                array_push( $return, new DataObject( $data ) );
                
            }
        }
        
        return $return;
        
    }
    
    function searchFor( $functionName, $searchRegex, $searchField ){

        if( !method_exists( $this, $functionName ) ){ 
            throw new Exception( "Invalid Function Provided!" );
            return array();
        }
        
        return array_filter( $this->{$functionName}(), function( $dataObject ) use ( $searchRegex, $searchField ) {

            return isset( $dataObject->{$searchField} ) ? preg_match( $searchRegex, $dataObject->{$searchField} ) : false;
            
        } );
        
    }
    
    function getClients(){
        
        return $this->getSection( "; !CLIENTS section -         ", "!CLIENTS:" );
        
    }
    
    function getPrefiled(){
        
        return $this->getSection( "; !PREFILE section -         ", "!PREFILE:" );
        
    }
    
    function getVoiceServers(){
        
        return $this->getSection( "; !VOICE SERVERS section -   ", "!VOICE SERVERS:" );
        
    }
    
    function getAirTraffic(){
        
        return array_filter( $this->getClients(), function( $dataObject ){
            
            return $dataObject->clienttype == "ATC" && !preg_match( "/(VAT|ACC|PTD|ATD)[a-z]{2,4}[0-9]{1,2}|[a-z]{2,4}(_SUP|_OBS)|VATSIM[0-9]{1,2}/i", $dataObject->callsign )
            && $dataObject->rating != 1;
            
        } );
        
    }
    
    function getObservers(){
     
        return array_filter( $this->getClients(), function( $dataObject ){
            
            return $dataObject->clienttype == "ATC" && preg_match( "/(VAT|ACC|PTD|ATD)[a-z]{2,4}[0-9]{1,2}|[a-z]{2,4}(_SUP|_OBS)|VATSIM[0-9]{1,2}/i", $dataObject->callsign );
            
        } );
     
    }
    
    function getStaff(){
     
        return array_filter( $this->getClients(), function( $dataObject ){
            
            return $dataObject->clienttype == "ATC" && preg_match( "/(VAT|ACC|PTD|ATD)[a-z]{2,4}[0-9]{1,2}|[a-z]{2,4}_SUP|VATSIM[0-9]{1,2}/i", $dataObject->callsign );
            
        } );
     
    }
    
    function getSupervisors(){
        
        return $this->searchFor( "getClients", "/(11|12)/", "rating" );
        
    }
    
    function getPilots(){
        
        return $this->searchFor( "getClients", "/PILOT/", "clienttype" );
        
    }
    
}
