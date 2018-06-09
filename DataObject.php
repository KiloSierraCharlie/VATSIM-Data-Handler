<?php
namespace VATSIM;
class DataObject{
    
    function __construct( $data ){
        
        foreach( $data as $variable=>$value ){
            
            $this->{ $variable } = is_string( $value ) ? iconv( mb_detect_encoding( $value ), "UTF-8", $value ) : $value;
            
        }
        
    }
    
}
