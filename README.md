# VATSIM Data Handler
Easy to use, object-orientated data handler for the VATSIM status system.

## Requirements
* file_get_contents

## How to use
Bear in mind, this only lists callsigns. You might find it more beneficial to var_dump one of the clients, to see the different variables available to you.
```php
<?php
require( "DataHandler.php" );
$DH = new DataHandler();
foreach( $DH->getClients() as $client ){
    print( $client->callsign . "<br/>" );
}
```
