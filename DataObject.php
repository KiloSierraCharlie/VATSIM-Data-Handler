<?php

class DataObject{
    
    function __construct( $data ){
        
        foreach( $data as $variable=>$value ){
            
            $this->{ $variable } = $value;
            
        }
        
    }
    
}
